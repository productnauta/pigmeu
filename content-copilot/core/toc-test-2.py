#!/usr/bin/env python3
"""
toc-test-2.py (bundle + run + validate)

- Carrega configs/providers.yaml
- Carrega configs/model_presets.yaml
- Resolve prompt toc_generation via core/prompts/prompts.yaml
- Carrega prompt.yaml, templates e schema
- Declara context, tocs, article_title
- Renderiza messages
- Chama Mistral (chat.complete)
- Valida JSON + minified + schema
"""

from __future__ import annotations

import argparse
import json
import os
import re
from pathlib import Path
from typing import Any, Dict, List, Optional

import yaml
from jinja2 import Environment, FileSystemLoader, StrictUndefined
from jsonschema import Draft202012Validator
from mistralai import Mistral


# =========================================================
# Constantes de Configuração
# =========================================================

DEFAULT_ROOT = "pigmeu/content-copilot"
DEFAULT_PROMPT_ID = "toc_generation"
SUPPORTED_MISTRAL_PARAMS = ("temperature", "top_p", "max_tokens", "max_output_tokens")


# =========================================================
# Helpers
# =========================================================

def read_text(path: Path) -> str:
    """
    Lê arquivo de texto com encoding UTF-8.
    
    Args:
        path: Caminho do arquivo a ler.
    
    Returns:
        Conteúdo do arquivo como string.
    
    Raises:
        FileNotFoundError: Se o arquivo não existir.
    """
    return path.read_text(encoding="utf-8")


def read_yaml(path: Path) -> Dict[str, Any]:
    """
    Lê e parseia arquivo YAML.
    
    Args:
        path: Caminho do arquivo YAML.
    
    Returns:
        Dicionário contendo dados parseados do YAML.
    
    Raises:
        FileNotFoundError: Se o arquivo não existir.
        ValueError: Se YAML não for um objeto (dict) no nível raiz.
    """
    if not path.exists():
        raise FileNotFoundError(f"YAML não encontrado: {path}")
    data = yaml.safe_load(read_text(path))
    if not isinstance(data, dict):
        raise ValueError(f"YAML inválido (top-level precisa ser objeto): {path}")
    return data


def read_json(path: Path) -> Any:
    """
    Lê e parseia arquivo JSON.
    
    Args:
        path: Caminho do arquivo JSON.
    
    Returns:
        Objeto/estrutura parseada do JSON.
    
    Raises:
        FileNotFoundError: Se o arquivo não existir.
        json.JSONDecodeError: Se JSON for inválido.
    """
    if not path.exists():
        raise FileNotFoundError(f"JSON não encontrado: {path}")
    return json.loads(read_text(path))


def minify_json(obj: Any) -> str:
    """
    Converte objeto Python em string JSON minificada (sem espaços).
    
    Args:
        obj: Objeto Python a serializar.
    
    Returns:
        String JSON compacta (sem whitespace desnecessário).
    """
    return json.dumps(obj, separators=(",", ":"), ensure_ascii=False)


def resolve_prompt_dir(root: Path, prompt_id: str) -> Path:
    """
    Resolve o diretório de um prompt a partir do índice de prompts.
    
    Lê core/prompts/prompts.yaml, busca entrada com 'id' especificado,
    valida se está ativado e retorna o caminho absoluto.
    
    Args:
        root: Raiz do projeto (content-copilot/).
        prompt_id: ID do prompt no índice (ex: 'toc_generation').
    
    Returns:
        Caminho absoluto do diretório do prompt.
    
    Raises:
        FileNotFoundError: Se prompts.yaml não existir.
        KeyError: Se prompt_id não encontrado no índice.
        ValueError: Se prompt estiver desabilitado ou sem 'path'.
    """
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
    Resolve API key do provider a partir de config ou variável de ambiente.
    
    Prioridade:
    1. provider_cfg['api_key'] (direto na config)
    2. provider_cfg['env_api_key'] (nome da var de ambiente)
    3. None (não disponível)
    
    Args:
        provider_cfg: Dicionário de configuração do provider.
    
    Returns:
        Dict com chaves:
        - 'api_key': String (chave), ou None se não encontrada.
        - 'api_key_source': 'api_key' | 'env:<NAME>' | None.
    """
    api_key = provider_cfg.get("api_key")
    if isinstance(api_key, str) and api_key.strip():
        return {"api_key": api_key.strip(), "api_key_source": "api_key"}

    env_name = provider_cfg.get("env_api_key")
    if isinstance(env_name, str) and env_name.strip():
        v = os.getenv(env_name.strip())
        return {"api_key": v if v else None, "api_key_source": f"env:{env_name.strip()}"}

    return {"api_key": None, "api_key_source": None}


def apply_model_preset(presets_cfg: Dict[str, Any], preset_name: Optional[str], params: Dict[str, Any]) -> Dict[str, Any]:
    """
    Aplica preset de modelo aos parâmetros, com override.
    
    Busca preset em presets_cfg['presets'][preset_name] e mescla
    (preset como base, params como override).
    
    Args:
        presets_cfg: Config com dicionário de presets.
        preset_name: Nome do preset (ou None para ignorar).
        params: Parâmetros adicionais (override).
    
    Returns:
        Dicionário mesclado de parâmetros.
    
    Raises:
        ValueError: Se preset_name existir mas não ser um dict.
    """
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
    """
    Renderiza messages (system, user, etc.) a partir de templates Jinja2.
    
    Lê templates do diretório prompt_dir/template/, renderiza com
    variáveis injetadas e retorna lista de messages estruturadas.
    
    Args:
        prompt_dir: Diretório raiz do prompt.
        prompt_spec: Especificação do prompt (contém 'messages').
        variables: Dict de variáveis para injetar nos templates.
    
    Returns:
        Lista de dicts {'role': str, 'content': str}.
    
    Raises:
        ValueError: Se 'messages' ausente, vazio ou malformado.
        jinja2.TemplateNotFound: Se template não existir.
        jinja2.UndefinedError: Se variável obrigatória ausente.
    """
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

        template_name = Path(template_path).name
        tpl = env.get_template(template_name)
        content = tpl.render(**variables)
        out.append({"role": role, "content": content})

    return out


def contains_markdown_fences_or_headings(s: str) -> bool:
    """
    Verifica se string contém indícios de markdown (fences ``` ou headings #).
    
    Heurística simples: verifica presença de ``` ou linha iniciando com #.
    
    Args:
        s: String a verificar.
    
    Returns:
        True se markdown detectado, False caso contrário.
    """
    return "```" in s or s.lstrip().startswith("#")


def is_minified_json_text(s: str) -> bool:
    """
    Verifica se string é JSON minificado (sem whitespace fora de strings).
    
    Método: remove quebras de linha e valida presença de espaços fora de strings.
    Aproximação heurística (não 100% precisa, mas boa o suficiente para output de LLM).
    
    Args:
        s: String JSON a verificar.
    
    Returns:
        True se minificado, False se contém whitespace indesejado.
    """
    if "\n" in s or "\r" in s:
        return False
    # Remove conteúdo de strings e verifica whitespace remanescente
    string_stripped = re.sub(r'"([^"\\]|\\.)*"', '""', s)
    return not bool(re.search(r"\s", string_stripped))


def validate_output_text(
    output_text: str,
    schema_obj: Optional[Dict[str, Any]],
    must_be_minified: bool,
    disallow_markdown: bool,
) -> Dict[str, Any]:
    """
    Valida saída do modelo (JSON, minificação, schema, markdown).
    
    Executa validações em ordem:
    1. Verifica markdown (se disallow_markdown=True).
    2. Parseia JSON.
    3. Verifica minificação (se must_be_minified=True).
    4. Valida contra JSON schema (se schema_obj fornecido).
    
    Args:
        output_text: Saída bruta do modelo.
        schema_obj: JSON schema para validar (ou None).
        must_be_minified: Se True, rejeita whitespace não essencial.
        disallow_markdown: Se True, rejeita ``` e headings.
    
    Returns:
        Objeto Python parseado (dict).
    
    Raises:
        ValueError: Se qualquer validação falhar.
    """
    if disallow_markdown and contains_markdown_fences_or_headings(output_text):
        raise ValueError("Saída contém indícios de Markdown (``` ou heading).")

    try:
        obj = json.loads(output_text)
    except Exception as e:
        raise ValueError(f"Saída não é JSON válido: {e}")

    if must_be_minified and not is_minified_json_text(output_text):
        raise ValueError("Saída não está minificada (whitespace detectado fora de strings).")

    if schema_obj is not None:
        validator = Draft202012Validator(schema_obj)
        errors = sorted(validator.iter_errors(obj), key=lambda e: list(e.path))
        if errors:
            e0 = errors[0]
            loc = ".".join(map(str, e0.path)) or "(root)"
            raise ValueError(f"Falha de schema em {loc}: {e0.message}")

    return obj


def extract_json_substring(s: str) -> Optional[str]:
    """
    Extrai substring JSON válida (com braces balanceados).
    
    Remove code fences markdown (```) se presentes e busca primeira {.
    Encontra o } correspondente e retorna substring com JSON válido.
    
    Args:
        s: String potencialmente contendo JSON com markdown fences.
    
    Returns:
        Substring JSON extraída, ou None se não encontrada.
    """
    # Remove markdown code fences se existirem
    cleaned = re.sub(r'```\w*\n?', '', s)  # Remove ```json, ``` etc
    
    start = cleaned.find("{")
    if start == -1:
        return None
    depth = 0
    for i in range(start, len(cleaned)):
        ch = cleaned[i]
        if ch == "{":
            depth += 1
        elif ch == "}":
            depth -= 1
            if depth == 0:
                return cleaned[start : i + 1]
    return None


def _print_response_diagnostics(resp: Any) -> None:
    """Exibe diagnóstico detalhado da resposta do modelo."""
    try:
        choices = getattr(resp, "choices", None)
        print(f"DEBUG: has choices: {bool(choices)}")
        if choices:
            print(f"DEBUG: choices count: {len(choices)}")
            for i, c in enumerate(choices):
                print(f"DEBUG: choice[{i}] repr: {repr(c)}")
                msg = getattr(c, "message", None)
                if msg:
                    print(f"DEBUG: choice[{i}].message: {repr(msg)}")
                    print(f"DEBUG: choice[{i}].message.content: {getattr(msg, 'content', None)!r}")
        print(f"DEBUG: full resp: {repr(resp)}")
    except Exception as ex:
        print(f"DEBUG: falha ao imprimir diagnóstico: {ex}")


def extract_output_from_response(resp: Any) -> str:
    """
    Extrai texto do response Mistral com tratamento defensivo.
    
    Acessa resp.choices[0].message.content com validações.
    Exibe diagnóstico se falhar.
    
    Returns:
        String de conteúdo (pode estar vazia se falha irrecuperável).
    """
    try:
        if not getattr(resp, "choices", None):
            raise RuntimeError(f"Resposta do Mistral sem 'choices': {resp}")
        
        msg = resp.choices[0].message
        output_text = getattr(msg, "content", None)
        
        if not output_text:
            raise RuntimeError(f"Mensagem vazia no retorno: {resp}")
        
        return output_text.strip()
    
    except Exception as e:
        print(f"DEBUG: Erro ao extrair output: {e}")
        _print_response_diagnostics(resp)
        return ""


def validate_with_recovery(
    output_text: str,
    schema_obj: Optional[Dict[str, Any]],
    must_be_minified: bool,
    disallow_markdown: bool,
) -> Dict[str, Any]:
    """
    Valida output com recovery automático via extração de JSON embarcado.
    
    Tenta validação completa primeiro; se falhar, tenta extrair JSON
    balanceado e revalidar (ignorando markdown check).
    
    Args:
        output_text: Saída bruta do modelo.
        schema_obj: JSON schema para validar.
        must_be_minified: Se rejeita whitespace.
        disallow_markdown: Se rejeita markdown (desativado em recovery).
    
    Returns:
        Objeto Python validado.
    
    Raises:
        ValueError: Se validação e recovery ambos falharem.
    """
    try:
        # Tentativa 1: validação completa
        return validate_output_text(
            output_text,
            schema_obj,
            must_be_minified,
            disallow_markdown
        )
    except ValueError as e:
        print(f"\nDEBUG: Validação inicial falhou: {e}")
        
        # Tentativa 2: extração de JSON embarcado
        extracted = extract_json_substring(output_text)
        if not extracted:
            print("DEBUG: nenhum JSON embarcado encontrado — elevando exceção original")
            raise
        
        print("DEBUG: JSON embarcado encontrado — tentando recovery...")
        try:
            obj = json.loads(extracted)
            
            # Cleanup: remove $schema do root se existir
            if isinstance(obj, dict) and "$schema" in obj:
                print("DEBUG: removendo $schema do root...")
                del obj["$schema"]
            
            # Reserializa como JSON minificado
            cleaned = minify_json(obj)
            
            # Revalida sem markdown check (recovery mode)
            result = validate_output_text(
                cleaned,
                schema_obj,
                must_be_minified,
                disallow_markdown=False  # Recovery: ignora markdown
            )
            print("DEBUG: recovery OK — JSON extraído passou na validação")
            return result
        
        except Exception as e2:
            print(f"DEBUG: recovery falhou: {e2}")
            raise

def main() -> None:
    """
    Função principal: orquestra carregamento de config, renderização
    de prompt, chamada à API Mistral e validação de saída.
    
    Fluxo:
    1. Parse argumentos (--root, --prompt, --run, --print).
    2. Carrega providers.yaml, model_presets.yaml.
    3. Resolve diretório do prompt via prompts.yaml.
    4. Carrega prompt.yaml, templates e schema.
    5. Resolve runtime (provider, model, parameters, api_key).
    6. Renderiza messages com variáveis de entrada.
    7. Exibe bundle (config + messages + schema).
    8. [Se --run] Chama Mistral API, valida e exibe resultado.
    """
    ap = argparse.ArgumentParser(
        description="Executa prompt de geração TOC contra Mistral API."
    )
    ap.add_argument(
        "--root",
        default=DEFAULT_ROOT,
        help="Raiz do projeto content-copilot (default: %(default)s)"
    )
    ap.add_argument(
        "--prompt",
        default=DEFAULT_PROMPT_ID,
        help="ID do prompt no índice (default: %(default)s)"
    )
    ap.add_argument(
        "--run",
        action="store_true",
        default=True,
        help="Executa chamada à API e valida saída (default: True)"
    )
    ap.add_argument(
        "--print",
        dest="print_mode",
        default="pretty",
        choices=["json", "pretty"],
        help="Formato de saída (default: %(default)s)"
    )
    args = ap.parse_args()

    # =====================================================
    # Carrega configs
    # =====================================================
    root = Path(args.root).resolve()

    providers_cfg = read_yaml(root / "configs" / "providers.yaml")
    presets_cfg = read_yaml(root / "configs" / "model_presets.yaml")

    # =====================================================
    # Resolve prompt e carrega especificação
    # =====================================================
    prompt_dir = resolve_prompt_dir(root, args.prompt)
    prompt_spec = read_yaml(prompt_dir / "prompt.yaml")

    # =====================================================
    # Resolve provider e runtime
    # =====================================================
    provider_name = (prompt_spec.get("runtime") or {}).get("provider")
    if not provider_name:
        raise ValueError("prompt.yaml inválido: runtime.provider ausente")

    provider_cfg = (providers_cfg.get("providers") or {}).get(provider_name)
    if not isinstance(provider_cfg, dict):
        raise ValueError(f"Provider '{provider_name}' não encontrado em configs/providers.yaml")

    api_key_info = resolve_api_key(provider_cfg)
    if not api_key_info["api_key"]:
        raise RuntimeError(
            f"API key não disponível para provider '{provider_name}' "
            f"(source={api_key_info['api_key_source']})"
        )

    runtime = prompt_spec.get("runtime", {})
    provider_default_mode = provider_cfg.get("default_mode")
    mode = runtime.get("mode") or provider_default_mode

    model = runtime.get("model")
    if not model:
        raise ValueError("prompt.yaml inválido: runtime.model ausente")

    preset_name = runtime.get("model_preset")
    params = apply_model_preset(presets_cfg, preset_name, runtime.get("parameters") or {})

    # =====================================================
    # Carrega schema de output
    # =====================================================
    output_cfg = ((prompt_spec.get("io") or {}).get("output") or {})
    schema_ref = output_cfg.get("schema_ref")
    must_be_minified = bool(output_cfg.get("must_be_minified", False))
    schema_obj = read_json(prompt_dir / schema_ref) if schema_ref else None

    # =====================================================
    # Carrega flags de validação
    # =====================================================
    validation_cfg = prompt_spec.get("validation", {})
    disallow_markdown = bool((validation_cfg.get("json") or {}).get("disallow_markdown", False))

    # =====================================================
    # Declara variáveis de entrada (contexto, TOCs, título)
    # =====================================================
    variables: Dict[str, Any] = {
        "context": (
            "O Manifesto Ágil, formalmente chamado Manifesto para o Desenvolvimento Ágil de Software, "
            "é um documento criado em 2001 por 17 profissionais experientes que buscavam uma forma mais "
            "adaptável, colaborativa e eficiente de desenvolver software comparado aos métodos tradicionais "
            "burocráticos. Ele estabelece quatro valores fundamentais — priorizar indivíduos e interações, "
            "software funcionando, colaboração com o cliente e responder a mudanças — que orientam equipes "
            "e organizações a focarem em valor entregue, flexibilidade e comunicação contínua. Além disso, "
            "o manifesto apresenta doze princípios que detalham práticas como entrega frequente de software "
            "de valor, acolhimento a mudanças nos requisitos mesmo tardiamente, colaboração contínua entre "
            "equipes e clientes, confiança em equipes auto-organizadas e foco em excelência técnica e simplicidade."
        ),
        "tocs": (
            "## TOC 1\n"
            "- Introdução ao Manifesto Ágil\n"
            "  - O que é o Manifesto para Desenvolvimento Ágil de Software\n"
            "  - Por que o Manifesto foi criado\n"
            "- Os Quatro Valores Fundamentais\n"
            "  - Indivíduos e interações mais que processos e ferramentas\n"
            "  - Software funcionando mais que documentação abrangente\n"
            "  - Colaboração com o cliente mais que negociação de contratos\n"
            "  - Responder a mudanças mais que seguir um plano\n"
            "- Os Doze Princípios Ágeis\n"
            "  - Satisfazer o cliente com entrega contínua\n"
            "  - Acolher mudanças nos requisitos\n"
            "  - Entrega frequente de software funcionando\n"
            "- Aplicações Práticas do Manifesto\n"
            "  - Métodos Ágeis mais utilizados\n"
            "  - Impacto no desenvolvimento moderno\n"
        ),
        "article_title": "Manifesto Ágil: Valores, Princípios e Aplicações Práticas no Desenvolvimento de Software",
    }
    
    # Injeta schema minificado para o modelo usar como referência
    if schema_obj is not None:
        variables["output_schema_minified"] = minify_json(schema_obj)

    # =====================================================
    # Renderiza messages
    # =====================================================
    messages = build_messages(prompt_dir, prompt_spec, variables)

    # =====================================================
    # Monta bundle (config + messages + schema)
    # =====================================================
    bundle = {
        "runtime_resolved": {
            "provider": provider_name,
            "model": model,
            "mode": mode,
            "parameters": params,
            "api_key_source": api_key_info["api_key_source"],
            "api_key_present": bool(api_key_info["api_key"]),
        },
        "prompt": {
            "id": prompt_spec.get("id"),
            "version": prompt_spec.get("version"),
            "messages_rendered": messages,
        },
        "schema": schema_obj,
    }

    # Exibe bundle (antes de chamar a API)
    if args.print_mode == "pretty":
        print(json.dumps(bundle, indent=2, ensure_ascii=False))
    else:
        print(json.dumps(bundle, separators=(",", ":"), ensure_ascii=False))

    # =====================================================
    # [SEÇÃO: Chamada à API + Validação]
    # =====================================================
    if not args.run:
        return

    if provider_name != "mistral":
        raise ValueError("Este runner --run está implementado apenas para provider=mistral.")

    # =====================================================
    # Inicializa cliente Mistral
    # =====================================================
    client = Mistral(api_key=api_key_info["api_key"])

    # Filtra apenas parâmetros suportados pela SDK Mistral
    mistral_kwargs = {}
    for k in SUPPORTED_MISTRAL_PARAMS:
        if k in params:
            mistral_kwargs[k] = params[k]

    # Normaliza: Mistral SDK prefere max_tokens (não max_output_tokens)
    if "max_output_tokens" in mistral_kwargs and "max_tokens" not in mistral_kwargs:
        mistral_kwargs["max_tokens"] = mistral_kwargs.pop("max_output_tokens")

    # =====================================================
    # Chama API Mistral
    # =====================================================
    resp = client.chat.complete(
        model=model,
        messages=messages,
        **mistral_kwargs,
    )

    # =====================================================
    # Extrai output_text com tratamento defensivo
    # =====================================================
    output_text = extract_output_from_response(resp)
    
    if not output_text:
        print("\nERRO: não foi possível extrair texto da resposta do modelo")
        raise RuntimeError("Output vazio após tentar extração")

    # =====================================================
    # Exibe output bruto
    # =====================================================
    print("\n--- MODEL OUTPUT (raw) ---\n")
    print(output_text)

    # =====================================================
    # Valida output com recovery automático
    # =====================================================
    validated = validate_with_recovery(
        output_text=output_text,
        schema_obj=schema_obj,
        must_be_minified=must_be_minified,
        disallow_markdown=disallow_markdown,
    )

    # =====================================================
    # Exibe resultado validado
    # =====================================================
    print("\n--- VALIDATION: OK ---\n")
    print(json.dumps(validated, indent=2, ensure_ascii=False))


if __name__ == "__main__":
    main()
