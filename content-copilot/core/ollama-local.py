#!/usr/bin/env python3
"""
Load Prompt Bundle Script

Este script carrega e valida bundles de prompts, incluindo templates e schemas.

Uso:
    python core/scripts/load_prompt_bundle.py --prompt-id toc_generation --output prompt_bundle.json
"""

import argparse
import json
import yaml
import os
from typing import Dict
from jsonschema import validate
from pathlib import Path


def load_prompt_config(prompt_id: str) -> Dict:
    """
    Carrega a configuração de um prompt a partir do arquivo prompt.yaml.
    
    Args:
        prompt_id (str): ID do prompt (ex.: toc_generation).
    
    Returns:
        Dict: Dicionário com a configuração do prompt.
    """
    # Carregar o registro de prompts
    with open('configs/prompts.yaml', 'r', encoding='utf-8') as f:
        prompts_registry = yaml.safe_load(f)
    
    # Encontrar o prompt pelo ID
    prompt_config = None
    for prompt in prompts_registry['prompts']:
        if prompt['id'] == prompt_id:
            prompt_config = prompt
            break
    
    if not prompt_config:
        raise ValueError(f"Prompt com ID '{prompt_id}' não encontrado.")
    
    # Carregar o arquivo prompt.yaml
    prompt_yaml_path = prompt_config['path']
    with open(prompt_yaml_path, 'r', encoding='utf-8') as f:
        return yaml.safe_load(f)


def load_template(template_path: str, prompt_config: Dict) -> str:
    """
    Carrega o conteúdo de um template de prompt.
    
    Args:
        template_path (str): Caminho para o arquivo de template.
        prompt_config (Dict): Configuração do prompt.
    
    Returns:
        str: Conteúdo do template.
    """
    # Resolver caminho relativo ao diretório do prompt.yaml
    prompt_dir = os.path.dirname(prompt_config['path'])
    full_path = os.path.join(prompt_dir, template_path)
    
    with open(full_path, 'r', encoding='utf-8') as f:
        return f.read()


def load_schema(schema_path: str, prompt_config: Dict) -> Dict:
    """
    Carrega um schema JSON para validação de saídas.
    
    Args:
        schema_path (str): Caminho para o arquivo de schema.
        prompt_config (Dict): Configuração do prompt.
    
    Returns:
        Dict: Dicionário com o schema JSON.
    """
    # Resolver caminho relativo ao diretório do prompt.yaml
    prompt_dir = os.path.dirname(prompt_config['path'])
    full_path = os.path.join(prompt_dir, schema_path)
    
    with open(full_path, 'r', encoding='utf-8') as f:
        return json.load(f)


def validate_prompt_config(prompt_config: Dict) -> None:
    """
    Valida a configuração de um prompt.
    
    Args:
        prompt_config (Dict): Configuração do prompt.
    
    Raises:
        ValueError: Se a configuração for inválida.
    """
    required_fields = ['id', 'runtime', 'messages', 'io']
    for field in required_fields:
        if field not in prompt_config:
            raise ValueError(f"Campo obrigatório '{field}' não encontrado no prompt.")


def create_prompt_bundle(prompt_id: str) -> Dict:
    """
    Cria um bundle completo de prompt, incluindo configuração, templates e schemas.
    
    Args:
        prompt_id (str): ID do prompt.
    
    Returns:
        Dict: Dicionário com o bundle completo.
    """
    # Carregar configuração do prompt
    prompt_config = load_prompt_config(prompt_id)
    validate_prompt_config(prompt_config)
    
    # Carregar templates
    templates = {}
    for message in prompt_config['messages']:
        template_path = message['template']
        templates[template_path] = load_template(template_path)
    
    # Carregar schema de saída
    schema_path = prompt_config['io']['output']['schema_ref']
    schema = load_schema(schema_path)
    
    return {
        'prompt_config': prompt_config,
        'templates': templates,
        'schema': schema
    }


def save_bundle(bundle: Dict, output_path: str) -> None:
    """
    Salva o bundle de prompt em um arquivo JSON.
    
    Args:
        bundle (Dict): Bundle de prompt.
        output_path (str): Caminho para o arquivo de saída.
    """
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(bundle, f, ensure_ascii=False, indent=2)


def main():
    """
    Função principal para carregar e validar bundles de prompts.
    """
    parser = argparse.ArgumentParser(description='Carrega e valida bundles de prompts.')
    parser.add_argument('--prompt-id', type=str, required=True, help='ID do prompt (ex.: toc_generation).')
    parser.add_argument('--output', type=str, required=True, help='Caminho para o arquivo JSON de saída.')
    
    args = parser.parse_args()
    
    # Criar bundle de prompt
    bundle = create_prompt_bundle(args.prompt_id)
    
    # Salvar bundle
    save_bundle(bundle, args.output)
    
    print(f"Bundle de prompt criado e salvo em: {args.output}")


if __name__ == '__main__':
    main()  