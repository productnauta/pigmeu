#!/usr/bin/env python3
"""
Content Analyzer Script

Este script analisa conteúdo sanitizado e constrói contexto para os prompts de IA.

Uso:
    python core/scripts/content_analyzer.py --input sanitized_output.json --output context.json
"""

import argparse
import json
from typing import Dict, List


def analyze_content(content: Dict) -> Dict:
    """
    Analisa o conteúdo sanitizado e constrói contexto para prompts de IA.
    
    Args:
        content (Dict): Conteúdo sanitizado (ex.: saída do html_processor.py).
    
    Returns:
        Dict: Dicionário com o contexto construído.
    """
    titles = content.get('titles', [])
    paragraphs = content.get('paragraphs', [])
    main_content = content.get('main_content', '')
    
    # Extrair palavras-chave dos títulos
    keywords = []
    for title in titles:
        keywords.extend(title['text'].split())
    
    # Construir contexto
    context = {
        'titles': titles,
        'keywords': list(set(keywords)),  # Remover duplicatas
        'summary': main_content[:500] + '...' if len(main_content) > 500 else main_content,
        'paragraph_count': len(paragraphs)
    }
    
    return context


def load_input(input_path: str) -> Dict:
    """
    Carrega o conteúdo JSON de um arquivo.
    
    Args:
        input_path (str): Caminho para o arquivo JSON de entrada.
    
    Returns:
        Dict: Dicionário com o conteúdo carregado.
    """
    with open(input_path, 'r', encoding='utf-8') as f:
        return json.load(f)


def save_output(output: Dict, output_path: str) -> None:
    """
    Salva o contexto analisado em um arquivo JSON.
    
    Args:
        output (Dict): Dicionário com o contexto analisado.
        output_path (str): Caminho para o arquivo de saída.
    """
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(output, f, ensure_ascii=False, indent=2)


def main():
    """
    Função principal para analisar conteúdo.
    """
    parser = argparse.ArgumentParser(description='Analisa conteúdo e constrói contexto para prompts de IA.')
    parser.add_argument('--input', type=str, required=True, help='Caminho para o arquivo JSON de entrada.')
    parser.add_argument('--output', type=str, required=True, help='Caminho para o arquivo JSON de saída.')
    
    args = parser.parse_args()
    
    # Carregar e analisar conteúdo
    content = load_input(args.input)
    context = analyze_content(content)
    
    # Salvar saída
    save_output(context, args.output)
    
    print(f"Contexto analisado e salvo em: {args.output}")


if __name__ == '__main__':
    main()