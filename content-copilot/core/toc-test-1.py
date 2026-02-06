#!/usr/bin/env python3
"""
load_prompt_bundle.py

Carrega:
- configs/providers.yaml
- configs/model_presets.yaml
- core/prompts/prompts.yaml (índice)
- core/prompts/<path>/prompt.yaml (manifesto do prompt toc_generation)
- templates (system/user)
- schema (output.schema.json)

E monta um "bundle" final com:
- runtime resolvido (provider/model/mode/parameters/api_key)
- messages renderizados (com injeção opcional de output_schema_minified)
- schema carregado
- metadados/validações

Uso:
  python load_prompt_bundle.py
ou:
  python load_prompt_bundle.py --root pigmeu/content-copilot --prompt toc_generation

Dependências:
  pip install pyyaml jinja2
"""

from __future__ import annotations

import argparse
import json
import os
from pathlib import Path
from typing import Any, Dict, List, Optional

import yaml
from jinja2 import Environment, FileSystemLoader, StrictUndefined


def read_text(path: Path) -> str:
    return path.read_text(encoding="utf-8")


def read_yaml(path: Path) -> Dict[str, Any]:
    if not path.exists():
        raise FileNotFoundError(f"YAML não encontrado: {path}")
    data = yaml.safe_load(read_text(path))
    if not isinstance(data, dict):
        raise ValueError(f"YAML inválido (top-level precisa ser objeto): {path}")
    return data


def read_json(path: Path) -> Any:
    if not path.exists():
        raise FileNotFoundError(f"JSON não encontrado: {path}")
    return json.loads(read_text(path))


def minify_json(obj: Any) -> str:
    return json.dumps(obj, separators=(",", ":"), ensure_ascii=False)


def deep_merge(base: Dict[str, Any], override: Dict[str, Any]) -> Dict[str, Any]:
    """Merge simples: dict recursivo; override ganha."""
    out = dict(base)
    for k, v in (override or {}).items():
        if isinstance(v, dict) and isinstance(out.get(k), dict):
            out[k] = deep_merge(out[k], v)
        else:
            out[k] = v
    return out


def resolve_prompt_dir(root: Path, prompt_id: str) -> Path:
    idx_path = root / "core" / "prompts" / "prompts.yaml"
    idx = read_yaml(idx_path)

    base_dir = (idx_path.parent / idx.get("base_dir", ".")).resolve()
    prompts = idx.get("prompts", [])
    if not isinstance(prompts, list):
        raise ValueError("prompts.yaml inválido: 'prompts' precisa ser lista")

    entry = next((p for p in prompts if p.get("id") == prompt_id), None)
    if not entry:
        raise KeyError(f"Prompt id '{prompt_id}' não encontrado em {idx_path}")

    if entry.get("enabled", True) is False:
        raise ValueError(f"Prompt '{prompt_id}' está desabilitado em {idx_path}")

    path = entry.get("path")
    if not path:
        raise ValueError(f"Prompt '{prompt_id}' sem 'path' no índice")

    return (base_dir / path).resolve()


def resolve_api_key(provider_cfg: Dict[str, Any]) -> Dict[str, Optional[str]]:
    """
    Regras:
    - se provider_cfg.api_key existe: usa direto
    - senão, se provider_cfg.env_api_key existe: lê env var
    Retorna também a origem (api_key | env:<VAR> | None)
    """
    api_key = provider_cfg.get("api_key")
    if isinstance(api_key, str) and api_key.strip():
        return {"api_key": api_key.strip(), "api_key_source": "api_key"}

    env_name = provider_cfg.get("env_api_key")
    if isinstance(env_name, str) and env_name.strip():
        v = os.getenv(env_name.strip())
        return {
            "api_key": v if v else None,
            "api_key_source": f"env:{env_name.strip()}",
        }

    return {"api_key": None, "api_key_source": None}


def apply_model_preset(presets_cfg: Dict[str, Any], preset_name: Optional[str], params: Dict[str, Any]) -> Dict[str, Any]:
    presets = (presets_cfg or {}).get("presets", {})
    if not preset_name:
        return params or {}
    preset = presets.get(preset_name, {})
    if not isinstance(preset, dict):
        raise ValueError(f"Preset '{preset_name}' inválido em model_presets.yaml")
    merged = dict(preset)
    merged.update(params or {})
    return merged


def build_messages(prompt_dir: Path, prompt_spec: Dict[str, Any], variables: Dict[str, Any]) -> List[Dict[str, str]]:
    # templates ficam em core/prompts/<prompt>/template/
    templates_dir = prompt_dir / "template"
    env = Environment(
        loader=FileSystemLoader(str(templates_dir)),
        undefined=StrictUndefined,
        autoescape=False,
    )

    messages_spec = prompt_spec.get("messages", [])
    if not isinstance(messages_spec, list) or not messages_spec:
        raise ValueError("prompt.yaml inválido: 'messages' precisa ser lista não vazia")

    out: List[Dict[str, str]] = []
    for m in messages_spec:
        role = m.get("role")
        template_path = m.get("template")
        if not role or not template_path:
            raise ValueError("Cada message precisa de 'role' e 'template' em prompt.yaml")

        # template no yaml pode ser "template/system-prompt.md" — vamos pegar só o filename
        template_name = Path(template_path).name
        tpl = env.get_template(template_name)
        content = tpl.render(**variables)
        out.append({"role": role, "content": content})

    return out


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--root", default="pigmeu/content-copilot", help="Raiz do content-copilot")
    ap.add_argument("--prompt", default="toc_generation", help="Prompt id no índice")
    ap.add_argument("--print", dest="print_mode", default="json", choices=["json", "pretty"], help="Formato de saída")
    args = ap.parse_args()

    root = Path(args.root).resolve()

    providers_cfg = read_yaml(root / "configs" / "providers.yaml")
    presets_cfg = read_yaml(root / "configs" / "model_presets.yaml")

    prompt_dir = resolve_prompt_dir(root, args.prompt)
    prompt_spec = read_yaml(prompt_dir / "prompt.yaml")

    # Provider + runtime
    provider_name = (prompt_spec.get("runtime") or {}).get("provider")
    if not provider_name:
        raise ValueError("prompt.yaml inválido: runtime.provider ausente")

    provider_cfg = (providers_cfg.get("providers") or {}).get(provider_name)
    if not isinstance(provider_cfg, dict):
        raise ValueError(f"Provider '{provider_name}' não encontrado em configs/providers.yaml")

    api_key_info = resolve_api_key(provider_cfg)

    runtime = prompt_spec.get("runtime", {})
    provider_default_mode = provider_cfg.get("default_mode")
    mode = runtime.get("mode") or provider_default_mode

    model = runtime.get("model")
    if not model:
        raise ValueError("prompt.yaml inválido: runtime.model ausente")

    preset_name = runtime.get("model_preset")
    params = apply_model_preset(presets_cfg, preset_name, runtime.get("parameters") or {})

    # Schema + variável para injetar no system prompt, se usado
    schema_ref = ((prompt_spec.get("io") or {}).get("output") or {}).get("schema_ref")
    schema_obj = read_json(prompt_dir / schema_ref) if schema_ref else None
    variables: Dict[str, Any] = {
                "context": """
This article discusses SEO best practices for long-form technical content,
including H2/H3 hierarchy, keyword clustering, and semantic coverage.
""".strip(),

                "tocs": """
## TOC 1
- Introduction
    - What is SEO
    - Why SEO matters

## TOC 2
- SEO Fundamentals
    - On-page SEO
    - Off-page SEO
""".strip(),

                "article_title": "SEO Best Practices for Long-Form Content",
        }
    if schema_obj is not None:
            variables["output_schema_minified"] = minify_json(schema_obj)

    messages = build_messages(prompt_dir, prompt_spec, variables)

    bundle = {
        "project_root": str(root),
        "prompt_id": prompt_spec.get("id"),
        "prompt_version": prompt_spec.get("version"),
        "prompt_dir": str(prompt_dir),
        "configs": {
            "providers_yaml": str(root / "configs" / "providers.yaml"),
            "model_presets_yaml": str(root / "configs" / "model_presets.yaml"),
        },
        "provider": {
            "name": provider_name,
            "default_mode": provider_default_mode,
            "api_key_source": api_key_info["api_key_source"],
            "api_key_present": bool(api_key_info["api_key"]),
        },
        "runtime_resolved": {
            "provider": provider_name,
            "mode": mode,
            "model": model,
            "parameters": params,
            # Inclui a key apenas se você quiser (cuidado com logs)
            "api_key": api_key_info["api_key"],
        },
        "prompt": {
            "manifest": prompt_spec,
            "messages_rendered": messages,
        },
        "schema": {
            "schema_ref": schema_ref,
            "output_schema": schema_obj,
            "output_schema_minified": minify_json(schema_obj) if schema_obj is not None else None,
        },
    }

    if args.print_mode == "pretty":
        print(json.dumps(bundle, indent=2, ensure_ascii=False))
    else:
        print(json.dumps(bundle, separators=(",", ":"), ensure_ascii=False))


if __name__ == "__main__":
    main()
