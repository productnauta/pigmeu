#!/usr/bin/env python3
"""
HTML Processor Script

Este script processa e sanitiza HTML de entrada, extraindo títulos, parágrafos e estrutura
para uso no pipeline de geração de conteúdo.

Uso:
    python core/scripts/html_processor.py --input input.html --output sanitized_output.json
"""

import argparse
import json
from bs4 import BeautifulSoup
from typing import Dict, List


def sanitize_html(html_content: str) -> Dict:
    """
    Sanitiza e extrai conteúdo relevante de um HTML.
    
    Args:
        html_content (str): Conteúdo HTML bruto.
    
    Returns:
        Dict: Dicionário com títulos, parágrafos e estrutura sanitizada.
    """
    soup = BeautifulSoup(html_content, 'html.parser')
    
    # Remove scripts, estilos e elementos indesejados
    for element in soup(['script', 'style', 'nav', 'footer', 'iframe']):
        element.decompose()
    
    # Extrair títulos (h1, h2, h3)
    titles = []
    for heading in soup.find_all(['h1', 'h2', 'h3']):
        titles.append({
            'level': heading.name,
            'text': heading.get_text(strip=True)
        })
    
    # Extrair parágrafos
    paragraphs = []
    for paragraph in soup.find_all('p'):
        text = paragraph.get_text(strip=True)
        if text:  # Ignorar parágrafos vazios
            paragraphs.append(text)
    
    # Extrair texto principal (corpo do artigo)
    main_content = soup.get_text(separator='\n', strip=True)
    
    return {
        'titles': titles,
        'paragraphs': paragraphs,
        'main_content': main_content
    }


def save_output(output: Dict, output_path: str) -> None:
    """
    Salva o conteúdo processado em um arquivo JSON.
    
    Args:
        output (Dict): Dicionário com o conteúdo processado.
        output_path (str): Caminho para o arquivo de saída.
    """
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(output, f, ensure_ascii=False, indent=2)


def load_input(input_path: str) -> str:
    """
    Carrega o conteúdo HTML de um arquivo.
    
    Args:
        input_path (str): Caminho para o arquivo de entrada.
    
    Returns:
        str: Conteúdo HTML bruto.
    """
    with open(input_path, 'r', encoding='utf-8') as f:
        return f.read()


def main():
    """
    Função principal para processar HTML.
    """
    parser = argparse.ArgumentParser(description='Processa e sanitiza HTML para o pipeline de conteúdo.')
    parser.add_argument('--input', type=str, required=True, help='Caminho para o arquivo HTML de entrada.')
    parser.add_argument('--output', type=str, required=True, help='Caminho para o arquivo JSON de saída.')
    
    args = parser.parse_args()
    
    # Carregar e processar HTML
    html_content = load_input(args.input)
    sanitized_content = sanitize_html(html_content)
    
    # Salvar saída
    save_output(sanitized_content, args.output)
    
    print(f"HTML processado e salvo em: {args.output}")


if __name__ == '__main__':
    main()