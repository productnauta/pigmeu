
## CONFIG FILES (YAML)

| file | description |
|---|---|
| database.yaml | Configuration for system database connections. |
| prompts.yaml | Predefined prompts for content generation. |
| wordpress.yaml | Settings for WordPress integration. |
| providers.yaml | Configuration for AI service providers. |
| content.yaml| Settings for content generation parameters. |
| seo.yaml | SEO settings and parameters. |


---

## MODULES

| module    ⠀⠀⠀⠀⠀⠀⠀⠀⠀   | description |
|---------------|---|
| content ideas | Generates content ideas based on user input. |
| [**content-copilot**](content-copilot/docs/content-copilot-.md) | Provides complete articles generation workflow based on user-defined parameters. |
| eo copilot | SEO optimization for generated content. |

---

## SCHEMAS AND TEMPLATES

| schema/template    ⠀⠀⠀   | description |
|---------------|---|
| [database schema](content-copilot/core/schemas/article.json) | JSON schema for articles stored in the database. |


---

## FEATURES AND REQUIREMENTS

| feature |group | description |
|---|---|---|
| **title analysis**⠀| metadata | Analyzes titles for clarity, relevance, and search intent. |
| **title improvement** | metadata | Rewrites titles for SEO, CTR, and readability. |
| **title variations** | metadata | Generates multiple title options based on style and intent. |




## Recursos de geração de conteúdo (AIomatic)

### 1) Título e metadados

* **Analisar título**

  * Avaliar clareza, relevância e intenção de busca
* **Melhorar título**

  * Reescrita para SEO, CTR e legibilidade
* **Gerar variações de título**

  * Múltiplas opções por estilo/intenção
* **Analisar metadados (SEO)**

  * Title tag e meta description
* **Gerar / otimizar metadados**

  * Meta description persuasiva
  * Ajuste de comprimento e palavras-chave

---

### 2) Estrutura do artigo

* **Gerar estrutura do artigo**

  * Outline completo (H1–H6)
* **Analisar estrutura existente**

  * Coerência, hierarquia e cobertura temática
* **Otimizar estrutura de títulos**

  * Ajuste de headings para SEO e leitura
* **Expandir / simplificar estrutura**

  * Versão longa, curta ou orientada a snippets
* **Estrutura por intenção**

  * Informacional, transacional, comparativa, tutorial

---

### 3) Conteúdo textual

* **Gerar conteúdo completo**

  * Artigos longos ou curtos
* **Expandir conteúdo**

  * Aprofundamento por seção
* **Resumir conteúdo**

  * TL;DR, resumo executivo
* **Reescrever conteúdo**

  * Clareza, tom, estilo ou público-alvo
* **Enriquecer conteúdo**

  * Exemplos, dados, explicações adicionais
* **Parafrasear**

  * Manter sentido com nova redação
* **Correção gramatical e ortográfica**
* **Ajuste de tom e voz**

  * Formal, técnico, conversacional, marketing
* **Geração orientada por prompts/templates**

  * Templates reutilizáveis por tipo de conteúdo

---

### 4) SEO on-page

* **Inserção e otimização de palavras-chave**
* **Análise semântica**

  * Termos relacionados e entidades
* **Otimização de densidade**
* **Criação de FAQs (schema-ready)**
* **Otimização para featured snippets**
* **Links internos sugeridos**
* **Análise de legibilidade**

  * Frases, parágrafos, escaneabilidade

---

### 5) Conteúdo multimídia (texto assistido)

* **Sugestão de imagens**

  * Royalty-free ou geração por IA
* **Geração de prompts para imagens**
* **Geração de legendas**

  * Imagens e redes sociais
* **Texto alternativo (alt text)**

  * SEO e acessibilidade
* **Sugestão de vídeos**

  * YouTube/community ou embeds

---

### 6) Conteúdo derivado (repurpose)

* **Transformar artigo em**

  * Post de blog curto
  * Newsletter
  * Post para redes sociais
  * Thread
  * Descrição de vídeo
* **Extrair highlights**

  * Citações, bullets, insights
* **Call-to-action (CTA)**

  * Geração e otimização

---

### 7) Tradução e localização

* **Traduzir conteúdo**

  * Preservando HTML e estrutura
* **Localização**

  * Idioma, região, variação cultural
* **Reescrita multilíngue**

  * Tom e SEO por idioma

---

### 8) Spin / variações de conteúdo

* **Spin automático**

  * Variações semânticas
* **Geração de múltiplas versões**

  * Testes A/B
* **Controle de repetição**

  * Penalidades de frequência/presença

---

### 9) Conteúdo orientado a dados externos

* **Conteúdo a partir de URLs**

  * Scraping de páginas
* **Conteúdo a partir de RSS**
* **Conteúdo a partir de YouTube**

  * Busca e captions
* **Conteúdo com contexto por embeddings**

  * Respostas baseadas em base vetorial

---

### 10) Automação e escala

* **Geração em massa (bulk)**

  * Múltiplos posts/configurações
* **Agendamento automático**

  * Cron e status do WordPress
* **Geração por regras**

  * Condições de execução
* **Batch requests**

  * Processamento assíncrono

---

### 11) Conteúdo conversacional

* **Chat de geração assistida**

  * Iteração em tempo real
* **Assistants especializados**

  * Prompt, ferramentas e arquivos
* **Function calling**

  * Ações baseadas no conteúdo gerado

---

Se quiser, posso **mapear cada recurso ao módulo exato do plugin (arquivo/classe)** ou **organizar isso como documentação funcional para engenharia reversa**.
