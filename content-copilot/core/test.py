from __future__ import annotations

import json
from pathlib import Path
from typing import Any, Dict

import yaml
from mistralai import Mistral


PROVIDERS_YAML = Path("pigmeu/content-copilot/configs/providers.yaml")


def load_yaml(path: Path) -> Dict[str, Any]:
    if not path.exists():
        raise FileNotFoundError(f"Arquivo não encontrado: {path}")
    with path.open("r", encoding="utf-8") as f:
        data = yaml.safe_load(f)
    if not isinstance(data, dict):
        raise ValueError(f"YAML inválido (esperado objeto no topo): {path}")
    return data


def get_api_key_from_providers(cfg: Dict[str, Any], provider_name: str) -> str:
    providers = cfg.get("providers")
    if not isinstance(providers, dict):
        raise ValueError("providers.yaml inválido: chave 'providers' ausente ou inválida")

    provider_cfg = providers.get(provider_name)
    if not isinstance(provider_cfg, dict):
        raise ValueError(f"Provider '{provider_name}' não encontrado em providers.yaml")

    api_key = provider_cfg.get("api_key")
    if not isinstance(api_key, str) or not api_key.strip():
        raise ValueError(f"Provider '{provider_name}' sem campo 'api_key' válido")

    return api_key.strip()


def main() -> None:
    cfg = load_yaml(PROVIDERS_YAML)
    api_key = get_api_key_from_providers(cfg, "mistral")

    client = Mistral(api_key=api_key)

    response = client.chat.complete(
        model="mistral-small-latest",
        messages=[
            {
                "role": "user",
                "content": "O que veio antes, o ovo ou a galinha?"
            }
        ],
        temperature=0.7,
        max_tokens=200
    )

    print(response.choices[0].message.content)


if __name__ == "__main__":
    main()
