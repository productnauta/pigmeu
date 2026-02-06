
# CONTENT COPILOT REQUIREMENTS

| file | description |
|---|---|
| [`database.yaml`](https://) | Configuration for system database connections. |
| `prompts.yaml` | Predefined prompts for content generation. |
| `wordpress.yaml` | Settings for WordPress integration. |
| `providers.yaml` | Configuration for AI service providers. |
| `content.yaml`| Settings for content generation parameters. |
| `seo.yaml` | SEO settings and parameters. |

---

## VISÃO GERAL

Copiloto baseado em IA e desenvolvido em Python, projetado para automatizar a geração de conteúdos para blogs. A pipeline é composta por etapas sequenciais que envolvem a ingestão de fontes, parametrização da geração, transacionamento de dados com MongoDB, interação APIs de IA (Gemini, Mistral, Groq, Ollama, etc.) e integração com WordPress para publicação.

---

## PIPELINE DE GERAÇÃO DE CONTEÚDO


### Inicialização do Fluxo

1. Usuário insere links das fontes e informações adicionais em texto livre, como público-alvo, estilo de escrita, etc.
2. Configura opções de geração, como quantidade de Ideias de Conteúdo a serem geradas, se permite ou não o uso de fontes na internet, etc.
3. Solicita a geração de ideias/conteúdo. 

## **PROCESSAMENTO INICIAL DAS FONTES**
   
Para cada link fornecido,o sistema deverá acessar o link e>

### OBTER HTML

- Obter o html completo da página
- Persistir o HTML completo na collection `original_sources` (campo `raw_html`)

---


### OBTER METADADOS

1. Analisar o conteúdo do html original  salvo no campo `raw_html` e extrair os seguintes metadados: 
    - Título h1
    - Meta descrição
    - URL canônica [se disponível]
    - Data de publicação [se disponível]
    - Categoria Principal [se disponível]
    - Outras categorias [se disponível]
    - Tags [se disponível]
2. Persistir os metadados na collection `original_sources` (campos correspondentes)

---

### OBTER ESTRUTURA DE TÍTULOS (TOC)

1. Analisar o conteúdo do html original  salvo no campo `raw_html` e :extrair a estrutura de títulos (TOC - Table of Contents), identificando os títulos h1, h2, h3, etc., e suas hierarquias.
2. Normalizar a estrutura de títulos em um formato hierárquico, para que possa ser facilmente identificado o nível de cada título.
3. Persistir essa estrutura na collection `original_sources` (campo `toc`).

---

### SANITIZAR HTML

Sanitizar HTML em Python removendo **scripts, CSS, tags perigosas e atributos indesejados**, mantendo:

* Texto “limpo”
* Tags de formatação: `<p> <a> <b>` (e outras que você escolher)
* Links (`href`) e texto do link
* Imagens com `src` e `alt`
* Legendas/descrições de imagens (ex.: `<figure><figcaption>...</figcaption></figure>`)

Para cada fonte,obter o HTML completo da página salvo no campo `raw_html` e realizar a sanitização conforme as etapas abaixo:

1. **Remover completamente:**

    * `<script>`, `<style>`, `<noscript>`, `<iframe>`, `<object>`, `<embed>`, `<svg>`, etc.
    * Comentários HTML
    * Meta/headers não úteis (opcional): `<head>`, `<meta>`, `<link>`

2. **Tags típicas para manter (exemplo):**

    * Texto/estrutura: `p, br, ul, ol, li, blockquote`
    * Formatação: `b, strong, i, em, u, code, pre`
    * Links: `a`
    * Imagens: `img, figure, figcaption`

3. **Atributos permitidos (exemplo mínimo e seguro):**
    * Para `<a>`: `href`, `title` (opcional)
    * Para `<img>`: `src`, `alt`, `title` (opcional)
    * Para `<figure>/<figcaption>`: geralmente nenhum atributo necessário

4. **Regras adicionais:**

    * Remover **todos** os atributos `on*` (ex.: `onclick`)
    * Remover `style` e `class` (ou manter `class` apenas se você precisar)
    * Bloquear protocolos perigosos em links: `javascript:`, `data:` (exceto se você decidir permitir `data:image/...`)

5. **Persistir o HTML**

    * Após a sanitização, persistir o HTML sanitizado na collection `original_sources` (campo `sanitized_html`).
---

        
### ANALISE INICIAL DOS CONTEÚDOS COM IA
   
Para cada fonte processada, o sistema deverá:

1. obter o HTML sanitizado salvo no campo `sanitized_html`.
2. Carregar o prompt `initial-content-analysis`
3. Montar a *user message* com o conteúdo do HTML sanitizado.
4. Enviar o prompt e o conteúdo da fonte para a IA
5. A IA analisa e responde com um resumo do conteúdo da fonte, no formato e com os campos especificados abaixo:
    * Título Principal
    * Resumo do Conteúdo (máx. 300 palavras)
    * Principais Assuntos (lista)
6. Exemplo de formato de resposta da IA:
    
    ```markdown
    # [Título extraído pela IA]

    ## Resumo:
    [Resumo extraído pela IA]

    ## Principais Assuntos:
    - Ponto 1
    - Ponto 2
    - Ponto 3
    ```
7. Receber a resposta da IA e normalizar o conteúdo, extraindo os campos conforme o formato especificado.
8. Persistir o conteúdo analisado no documento correspondente da collection `original_sources`, nos campos:
    
    * `ia_analysis.title`
    * `ia_analysis.summary`
    * `ia_analysis.key_points` (array)  

---

### CONSTRUIR DO CONTEXTO 

O sistema deverá gerar um contexto detalhado para ser utilizado como base de conhecimento do módulo de IA durante a geração de ideias/conteúdo. Para isso, deverá:

1. Obter os resumos de todas as fontes previamente criados pela IA (campo `ia_analysis.summary`).
    2.1 Concatenar todos os resumos em um único texto.
    2.2 Identificar o início e fim de cada resumo utilizando "## RESUMO 1: [TÍTULO DO ARTIGO]", "## RESUMO 2: [TÍTULO DO ARTIGO]", etc. 
    2;3 Armazenar esse conteúdo concatenado em variável para uso posterior.
2. Obter a estrutura de títulos (TOC) de todas as fontes (campo `toc`).
    3.1 Concatenar as estruturas de títulos em um único texto.
    3.2 Identificar o início e fim de cada TOC utilizando "## TOC 1: [TÍTULO DO ARTIGO]", "## TOC 2: [TÍTULO DO ARTIGO]", etc.
    3.3 Armazenar esse conteúdo concatenado em variável para uso posterior.
3. Carregar o prompt `build-source-context`.
4. Montar a *user message* com o conteúdo consolidado dos resumos e TOCs.
5. Enviar o prompt e o conteúdo para a IA.
6. A IA gera a resposta conforme o formato especificado abaixo:
    * Contexto Consolidado
    * Formato Markdown otimizado para leitura por modelos de IA
7. Receber a resposta da IA e normalizar o conteúdo.
8. Persistir o conteúdo final no documento correspondente da collection `content_generation_requests`, no campo `initial_context`.

---

### GERAR IDEIAS DE TÍTULOS DE CONTEÚDO

1. Utilizar o contexto construído na etapa anterior como base de conhecimento e contextualização da IA.
2. Carregar o prompt `content-ideas-generation`.
3. Montar a *user message* com as informações adicionais fornecidas pelo usuário (público-alvo, estilo de escrita, etc.) e o contexto inicial.,
4. Enviar o prompt e o conteúdo para a IA.
5. A IA gera as ideias de títulos de novos artigos conforme o formato especificado abaixo:
    * Lista de Títulos de Ideias de Conteúdo (máx. conforme configuração)
    * Formato JSON estruturado e otimizado para fácil parsing
    * Exemplo de formato de resposta da IA:
    
    ```json
    {
      "content_ideas": [
        "Título da Ideia 1",
        "Título da Ideia 2",
        "Título da Ideia 3"
      ]
    }
    ```
6. Receber a resposta da IA e normalizar o conteúdo, extraindo a lista de títulos conforme o formato especificado.
7. Persistir as ideias de conteúdo no documento correspondente da collection `content_generation_requests`, no campo `generated_title_ideas`.
8. Disponibilizar as ideias geradas na interface para o usuário revisar e selecionar o título desejado.
9. Persistir a seleção do título feita pelo usuário no documento correspondente da collection `content_generation_requests`, no campo `selected_title`.
10. Continuar para o próximo passo. 


---

### GERAR TOC DO CONTEÚDO

Utilizando o conteúdo do contexto do tema (campo `initial_context`) e o título selecionado pelo usuário (campo `selected_title`), o sistema deverá:


1. Obter todos os TOCs das fontes previamente processadas (campo `toc`).
2. Concatenar todas as TOCs em um único texto.
    2.1 Identificar o início e fim de cada TOC utilizando "## TOC 1: [TÍTULO DO ARTIGO]", "## TOC 2: [TÍTULO DO ARTIGO]", etc.
3. Carregar o prompt `content-toc-generation`.
4. Montar a *user message*  incluindo o contexto do tema, a ideia de título selecionada pelo usuário e o conteúdo consolidado dos TOCs.
5. Enviar o prompt e o conteúdo para a IA.
6. A IA responderá em formato JSON estruturado, com três diferentes propostas de estrutura de títulos.
    1. **Tipos de TOCS**
        - **Proposta 1**: Baseada na estrutura com os títulos e subtítulos mais recorrentes e frequentes otimizada para seo e geo. 
        - **Proposta 2**: Baseado nos títulos e subtítulos mais frequente, porém adicionando novos subtítulos relevantes identificados nas outras estruturas analisadas, otimizada para seo e geo. 
        - **Proposta 3**: Pesquisar conteúdos relacionados para ampliar a estrutura base com novos títulos e subtítulos otimizados para SEO e GEO.
    2. **Exemplo JSON**
    ```json
    
7. Receber a resposta da IA e normalizar o conteúdo, extraindo as três propostas de TOC conforme o formato especificado.
8. Persistir as propostas de TOC no documento correspondente da collection `content_generation_requests`, no campo `generated_toc_proposals`.
9. Disponibilizar as propostas geradas na interface para o usuário revisar e selecionar a estrutura desejada.
10. Persistir a seleção feita pelo usuário no documento correspondente da collection `content_generation_requests`, no campo `selected_toc`.
11. Continu




Crie um prompt com as seguintes INFO

## INFORMAÇÕES E REQUISITOS DO PROMPTS

Atuar como um analista de conteúdo, materias, artigos e notícias. Domina as técnicas de análise de conteúdo, incluindo a identificação de temas, padrões e tendências em grandes volumes de texto. Capaz de extrair insights valiosos e fornecer resumos concisos e precisos. Habilidade para avaliar a relevância e a qualidade das informações, garantindo que os dados sejam interpretados corretamente e utilizados de forma eficaz.

Analisando o conteúdo informado, responda com:

1. Resumo curto
    1. Resumo conciso e objetivo do conteúdo, incluindo pontos-chave, discussões, temas abordados e conclusões principais.
    2. Tamanho máximo: 153 palavras em 2 parágrafos 
2. Resumo analítico
    1. Análise detalhada do conteúdo, destacando os principais argumentos, evidências e implicações. Identificar a relevância e qualidade do conteúdo.
    3. Tamanho máximo: 522 palavras em 7 parágrafos
```


Atue como um redator de artigos e analista de conteúdo, especilista em SEO e na identificação de estruturas de títulos e subtítulos de artigos.


Voce receberá receberá o seguinte conteúdo para análise, composto por dois grupos de textos distintos:
  - Contexto do tema central dos artigos, incluindo informações relevantes, termos e palavras-chave importantes, temas e tópicos principais, etc. (tudo em texto simples).
  - Estrutura de títulos (h2) e subtítulos (h3) (toc) de todos os artigos (apenas títulos e subtítulos, sem o conteúdo dos artigos), onde cada estrutura está identificada por um título ## TOC 1, ## TOC 2, etc.) em texto sinokes,.
  - Título do artigo que será gerado.


A ia utilizar todo  contexto fornecido, e analisar todas as estruturas de títulos e subtítulos (toc) fornecidas para então gerar 3 propostas de estrutura de títulos e subtítulos (toc):
  1. Proposta 1: Baseada na estrutura com os títulos e subtítulos mais recorrentes e frequentes otimizada para seo e geo.
  2. Proposta 2: Utilizando a estrutura de títulos e subtítulos mais frequente, porém adicionando novos subtítulos relevantes identificados nas outras estruturas analisadas, otimizada para seo e geo.
  3. Proposta 3: Buscar na internet mais conteúdos e informações diretamente relacionadas aos temas abordados no contexto e nas estrutura de títulos, para identificar outros títulos e subtítulos e não presentes nas estruturas fornecidas, e considerando como base a proposta 1, gerar una estrutura que incorpore essas novas oportunidades de títulos e subtítulos, otimizados para seo e geo.

### OUTPUT EXPECTED:

Responda com as 3 propostas de estrutura de títulos e subtítulos (toc), cada uma contendo:
  - Mínimo 5 títulos h2
  - Maximo 9 títulos h2
  - Subtítulos h3 totais: mínimo 7, máximo 13
  - Hierarquização correta dos títulos e subtítulos (h2, h3, etc).
FORMATO E EXEMPLO DE OUTPUT:
  - Responda em formato JSON válido, sem markdown, sem texto fora do JSON.
  
```json
{
    "article_title": "string",
    "analysis_summary": {
        "main_themes": ["string"],
        "primary_keywords": ["string"],
        "analyzed_structures": "number",
        "recurring_titles": ["string"],
        "recurring_subtitles": ["string"]
    },
    "proposals": [
        {
        "id": "number",
        "name": "string",
        "description": "string",
        "structure": [
            {
            "h2": "string",
            "h3": ["string"]
            }
        ],
        "total_h2": "number",
        "total_h3": "number",
        "features": {
            "seo_optimization": "string",
            "geo_optimization": "string",
            "data_source": "string",
            "new_elements": "number"
        }
        }
    ],
    "metadata": {
        "generation_date": "string (ISO 8601)",
        "schema_version": "string",
        "total_titles_analyzed": "number",
        "total_subtitles_analyzed": "number"
    }
}
```


```markdown

# TOC GENERATION PROMPT
## SYSTEM PROMPT

You are an AI specialized in **SEO-oriented article outline generation** and **analysis of H2/H3 title structures**.

Analyze a provided **context**, multiple **TOC structures**, and a **target article title**. From this analysis, generate **three optimized TOC proposals** following SEO and GEO best practices.

Do not generate article content. Do not explain reasoning. Output **only valid, minified JSON**, with no markdown or extra text.

The output **must strictly follow this schema** (minified):

{"article_title":"string","analysis_summary":{"main_themes":["string"],"primary_keywords":["string"],"analyzed_structures":0,"recurring_titles":["string"],"recurring_subtitles":["string"]},"proposals":[{"id":0,"name":"string","description":"string","structure":[{"h2":"string","h3":["string"]}],"total_h2":0,"total_h3":0,"features":{"seo_optimization":"string","geo_optimization":"string","data_source":"string","new_elements":0}}],"metadata":{"generation_date":"ISO-8601","schema_version":"1.0","total_titles_analyzed":0,"total_subtitles_analyzed":0}}

Before responding, internally validate all constraints. If invalid, regenerate silently.

------------

## USER MESSAGE

You will receive:

1. **Context**: description of the topic, keywords, entities, and relevant themes.  
2. **TOC Structures**: multiple outlines containing only H2 and H3 titles, identified as `## TOC 1`, `## TOC 2`, etc.  
3. **Article Title**: the title for which the new TOCs will be generated.

### Task

Using all inputs, generate **three TOC proposals**:

- **Proposal 1**: Based on the most frequent and recurring H2/H3 patterns.  
- **Proposal 2**: Same base as Proposal 1, enriched with relevant subtitles found across other TOCs.  
- **Proposal 3**: Based on Proposal 1, expanded with new relevant H2/H3 inferred from context and domain knowledge.

All proposals must be optimized for **SEO and GEO**.

### Mandatory Constraints (per proposal)

- H2: minimum 5, maximum 9 
- Total H3: minimum 7, maximum 13  
- Correct hierarchy (H3 always under an H2)  
- Clear, concise, non-redundant titles  

### Output Rules

Return **only valid JSON**, strictly following the schema defined in the system prompt. No markdown. No explanations.
```
