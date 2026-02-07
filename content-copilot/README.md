# CITO (Content Copilot) MVP

O **CITO (Content Copilot)** é um sistema de automação de geração de conteúdo para blogs, utilizando IA para criar TOCs (Table of Contents), resumos e rascunhos de artigos.

---

## **Visão Geral**
O CITO MVP é projetado para:
- Processar e sanitizar HTML de entrada.
- Gerar propostas de TOC usando prompts de IA.
- Armazenar resultados em um banco de dados MongoDB.
- Facilitar a integração com plataformas como WordPress.

---

## **Estrutura do Projeto**
```text
content-copilot/
├── configs/
│   ├── providers.yaml          # Configurações dos provedores de IA
│   ├── model_presets.yaml      # Presets de modelos para IA
│   └── prompts.yaml            # Registro de prompts disponíveis
├── core/
│   ├── prompts/
│   │   └── toc_generation/     # Prompt para geração de TOC
│   │       ├── prompt.yaml      # Configuração do prompt
│   │       ├── template/        # Templates de prompts
│   │       └── schemas/         # Schemas de validação
│   ├── scripts/
│   │   ├── html_processor.py    # Processa e sanitiza HTML
│   │   ├── content_analyzer.py  # Analisa conteúdo e constrói contexto
│   │   ├── load_prompt_bundle.py # Carrega bundles de prompts
│   │   ├── toc-test-2.py        # Gera TOCs usando IA
│   │   └── store_results.py     # Armazena resultados no MongoDB
├── docs/
│   └── mvp/                    # Documentação do MVP
├── .env                        # Variáveis de ambiente
├── requirements.txt            # Dependências Python
└── README.md                   # Este arquivo
```

---

## **Setup e Instalação**
### **Pré-requisitos**
- Python 3.9+
- MongoDB (local ou remoto)
- Chaves de API para provedores de IA (Mistral, Groq, Gemini)

### **Instalação**
1. Clone o repositório:
   ```bash
   git clone https://github.com/your-organization/content-copilot.git
   cd content-copilot
   ```

2. Crie e ative um ambiente virtual:
   ```bash
   python3 -m venv venv
   source venv/bin/activate  # Linux/macOS
   # ou
   venv\Scripts\activate     # Windows
   ```

3. Instale as dependências:
   ```bash
   pip install -r requirements.txt
   ```

4. Configure as variáveis de ambiente:
   ```bash
   cp .env.example .env
   # Edite o arquivo .env com suas configurações
   ```

---

## **Uso**
### **Processar HTML**
```bash
python core/scripts/html_processor.py --input input.html --output sanitized_output.json
```

### **Analisar Conteúdo**
```bash
python core/scripts/content_analyzer.py --input sanitized_output.json --output context.json
```

### **Carregar Bundle de Prompt**
```bash
python core/scripts/load_prompt_bundle.py --prompt-id toc_generation --output prompt_bundle.json
```

### **Gerar TOCs**
```bash
python core/scripts/toc-test-2.py --input prompt_bundle.json --output toc_proposals.json
```

### **Armazenar Resultados no MongoDB**
```bash
python core/scripts/store_results.py --input toc_proposals.json --collection original_sources
```

---

## **Documentação**
A documentação completa do MVP está disponível em [`docs/mvp/`](docs/mvp/):
- [Requirements](docs/mvp/requirements.md)
- [Architecture](docs/mvp/architecture.md)
- [Data Model](docs/mvp/data_model.md)
- [Pipeline](docs/mvp/pipeline.md)
- [Prompts](docs/mvp/prompts.md)
- [Scripts](docs/mvp/scripts.md)
- [Setup](docs/mvp/setup.md)

---

## **Contribuição**
Contribuições são bem-vindas! Siga estas etapas:
1. Abra uma issue para discutir a mudança.
2. Crie um fork do repositório.
3. Faça suas alterações e envie um pull request.

---

## **Licença**
Este projeto está licenciado sob a [MIT License](LICENSE).