#!/usr/bin/env python3
"""
Store Results Script

Este script armazena resultados (ex.: TOCs geradas) no MongoDB.

Uso:
    python core/scripts/store_results.py --input toc_proposals.json --collection original_sources
"""

import argparse
import json
from typing import Dict
from pymongo import MongoClient
from pymongo.errors import ConnectionFailure
from dotenv import load_dotenv
import os


def load_input(input_path: str) -> Dict:
    """
    Carrega o conteúdo JSON de um arquivo.
    
    Args:
        input_path (str): Caminho para o arquivo JSON de entrada.
    
    Returns:
        Dict: Dicionário com os dados a serem armazenados.
    """
    with open(input_path, 'r', encoding='utf-8') as f:
        return json.load(f)


def connect_to_mongodb() -> MongoClient:
    """
    Conecta ao MongoDB usando as configurações do .env.
    
    Returns:
        MongoClient: Cliente MongoDB conectado.
    """
    load_dotenv()
    
    mongo_uri = os.getenv('MONGO_URI', 'mongodb://localhost:27017')
    mongo_db_name = os.getenv('MONGO_DB_NAME', 'cito_db')
    
    try:
        client = MongoClient(mongo_uri)
        client.admin.command('ping')  # Testar conexão
        print(f"Conectado ao MongoDB: {mongo_uri}")
        return client
    except ConnectionFailure as e:
        print(f"Erro ao conectar ao MongoDB: {e}")
        raise


def store_results(client: MongoClient, data: Dict, collection_name: str) -> None:
    """
    Armazena os resultados em uma coleção do MongoDB.
    
    Args:
        client (MongoClient): Cliente MongoDB conectado.
        data (Dict): Dados a serem armazenados.
        collection_name (str): Nome da coleção de destino.
    """
    db = client[os.getenv('MONGO_DB_NAME', 'cito_db')]
    collection = db[collection_name]
    
    result = collection.insert_one(data)
    print(f"Resultado armazenado com ID: {result.inserted_id}")


def main():
    """
    Função principal para armazenar resultados no MongoDB.
    """
    parser = argparse.ArgumentParser(description='Armazena resultados no MongoDB.')
    parser.add_argument('--input', type=str, required=True, help='Caminho para o arquivo JSON de entrada.')
    parser.add_argument('--collection', type=str, required=True, help='Nome da coleção de destino no MongoDB.')
    
    args = parser.parse_args()
    
    # Carregar dados de entrada
    data = load_input(args.input)
    
    # Conectar ao MongoDB e armazenar resultados
    client = connect_to_mongodb()
    store_results(client, data, args.collection)
    
    client.close()


if __name__ == '__main__':
    main()