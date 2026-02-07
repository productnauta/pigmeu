
User: Analise atendamente o arquivo [content-copilot.md](pigmeu/content-copilot/docs/content-copilot.md)  e faça o entendimento sobre o escopo do projeto (docuemntado até agora.

Em seguida, inicie um planejamento para o desenvolvimento de um MVP.

GitHub Copilot: Status: Running a research agent to gather project context (prompts, configs, schemas, scripts). Next: I'll synthesize findings into a concise MVP plan for your review.

Research the repo and docs to draft MVP plan

Searched for files matching `**/content-copilot.md`, 1 match

Searched for files matching `**/core/**`, 11 matches

Searched for text `load_prompt_bundle`, 8 results

Searched for files matching `**/core/load_prompt_bundle.py`, no matches

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/docs/content-copilot.md#1-1), lines 1 to 400

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/docs/content-copilot.md#400-400), lines 400 to 770

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/toc-test-2.py#1-1), lines 1 to 400

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/toc-test-2.py#400-400), lines 400 to 724

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/prompts/prompts.yaml)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/prompts/toc_generation/prompt.yaml)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/prompts/toc_generation/template/system-prompt.md)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/prompts/toc_generation/template/user-message.md)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/prompts/toc_generation/schemas/output.schema.json)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/configs/providers.yaml)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/configs/model_presets.yaml)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/test.py)

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/utils/prompts.yaml#1-1), lines 1 to 400

Searched for regex `retry_on_fail|retry_on_fail|Retry|max_retries|retry`, 6 results

Searched for regex `tests/|test_prompts|pytest|unittest|CI|github actions`, 20 results

Searched for files matching `**/.github/**`, 4 matches

Searched for files matching `**/tests/**`, no matches

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/toc-test-1.py#1-1), lines 1 to 200

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot/core/toc-test-1.py#200-200), lines 200 to 267

Searched for regex `original_sources|content_generation_requests`, 14 results

Searched for regex `sanitize|sanitiza|raw_html|fetch|requests|http`, 20 results

## Plan: Content Copilot MVP ✅

TL;DR — Construir um MVP que percorra o fluxo: obter HTML → sanitizar + extrair TOC/metadata → gerar contexto → executar prompt `toc_generation` (via loader + provider adapter) → validar e persistir as propostas de TOC. Objetivo: provar o fluxo end-to-end com testes e CI, usando Mistral como provider inicial e armazenamento por ficheiros (opção para Mongo depois).

### Steps (5 passos)
1. **Corrigir configs & segurança** — Validar e limpar `configs/providers.yaml` e `configs/model_presets.yaml`; exigir `env_api_key` e adicionar validação (schema). (Entrega: validação de config + CI check) ✅  
2. **Implementar fetch + sanitização + TOC extractor** — Script `scripts/fetch_and_sanitize.py` que produz JSON com `raw_html`, `sanitized_html`, `toc`, `metadata` em `data/original_sources/`. (Entrega: testes unitários para sanitização e extração) 🧼  
3. **Criar loader reutilizável de prompt** — `core/load_prompt_bundle.py` (ou refactor de `core/toc-test-1.py`) que resolve runtime, injeta `output_schema_minified` e retorna `messages_rendered` e bundle. (Entrega: contrato unit-testado) 🔧  
4. **Adapter de provider + abstração** — `core/providers/adapter.py` (interface) e `core/providers/mistral_adapter.py` que normaliza chamadas e respostas (permite mocking em testes). (Entrega: mockable adapter com testes) ⚙️  
5. **Orquestrador end-to-end + persistência** — `core/runner.py` que: lê `data/original_sources/`, constrói `context`, chama loader+adapter, aplica validação/retry conforme `prompt.yaml`, persiste `content_generation_requests/` com `generated_toc_proposals`. Incluir testes de retry e validação. (Entrega: fluxo E2E funcionando em modo CLI) 🚀

---

### Further Considerations 💡
1. Armazenamento: preferes **filesystem JSON** (rápido para MVP) ou **MongoDB** (alinha com docs)?  
2. Provider: MVP com **apenas Mistral** ou adicionamos **um segundo provider** para validar abstração?  
3. Retry policy: usar **regenerate** por padrão ou **auto-minify then revalidate** como primeiro recovery?

> Observação importante: o repositório já contém manifestos, templates (`core/prompts/toc_generation/template/`) e schema (`output.schema.json`) — vamos reaproveitar esses ativos para reduzir trabalho.

---

Se concorda com esse rascunho, eu resumo em tarefas de implementação (issues + prioridades) e proponho estimativas mais detalhadas. Qual opção prefere para armazenamento e provider inicial?

User: Mapeie as tarefas necessárias para o mvp, sugira as funcionalidades do mvp.

GitHub Copilot: ## Plan: Mapeamento de Tarefas e Funcionalidades do MVP

TL;DR — MVP com **5 funcionalidades principais**: (1) Fetch & sanitize fontes, (2) Extrair TOC/metadata, (3) Análise inicial com IA, (4) Gerar propostas de TOC, (5) Persistir tudo. Divididas em **15 tarefas** com dependências claras, ordenadas por prioridade (config/segurança → loader → adapters → orquestrador → testes).

---

### Funcionalidades Core do MVP 🎯

| # | Funcionalidade | Escopo | Entrada | Saída | Status |
|---|---|---|---|---|---|
| **F1** | Fetch & Sanitize HTML | Obter links, parsear HTML, remover scripts/CSS maliciosos | Lista de URLs | `original_sources/` com `raw_html`, `sanitized_html`, `toc`, `metadata` | Core |
| **F2** | Extrair TOC & Metadata | Identificar H2/H3, datas, títulos, meta desc, URLs canônicas | `raw_html` | Estrutura hierárquica JSON (`toc`, `title`, `pub_date`, `canonical_url`) | Core |
| **F3** | Análise Inicial com IA | Resumir cada fonte via prompt `initial-content-analysis` | `sanitized_html` + prompt | `ia_analysis` (título, resumo, key_points) persistido | Core |
| **F4** | Construir Contexto | Consolidar resumos e TOCs de múltiplas fontes | Múltiplos `ia_analysis` + `toc` | `initial_context` (Markdown concatenado) pronto para prompt | Core |
| **F5** | Gerar Propostas de TOC | Chamar prompt `toc_generation` com contexto + título | `initial_context` + título selecionado | 3 propostas de TOC em JSON, validadas vs schema | Core |
| **F6** | Persistência | Salvar documentos `original_sources` e `content_generation_requests` | Bundle de dados por fase | Ficheiros JSON ou MongoDB (configurable) | Core |

---

### Tarefas Mapeadas (15 tasks) 📋

#### **Bloco 1: Config & Security (Priority 1)** ⚠️

| Task | Subtarefas | Aceitação | Esforço | Deps |
|---|---|---|---|---|
| **T1.1** Config Validation & Schema | 1.1.1 Criar schema para `providers.yaml` (provider, api_key, default_mode) | Arquivo schema criado; validador implementado; CI testa durante startup | **S** | — |
| | 1.1.2 Criar schema para `model_presets.yaml` (presets com temperature, max_tokens, etc.) | Schema validado em CI | | |
| | 1.1.3 Implementar `ConfigValidator` que testa ambos schemas em `conftest.py` | Testes unitários executam sem hardcoded secrets | | |
| **T1.2** Secrets Hygiene | 1.2.1 Remover hardcoded API keys de `providers.yaml`; mover para exemplos com comentários | `providers.yaml` não contém secrets; `providers.example.yaml` criado com placeholders | **S** | T1.1 |
| | 1.2.2 Implementar `load_api_key_from_env()` util; requires `env_api_key` field | Função testa resolução de env var; fallback opcional | | |
| | 1.2.3 Validar em CI que tracked files não contêm patterns `api_key:` (linter) | CI job rejeita PRs com secrets | | |

---

#### **Bloco 2: Fetch & Sanitize (Priority 2)** 🧼

| Task | Subtarefas | Aceitação | Esforço | Deps |
|---|---|---|---|---|
| **T2.1** HTML Fetcher | 2.1.1 Implement `core/utils/html_fetcher.py` com `fetch_html(url)` using requests + timeout | Função testa GET com 5s timeout; lida com redirects; lança exceptions em 4xx/5xx | **S** | T1.2 |
| | 2.1.2 Add retry logic + user-agent header (avoid 403s) | Testa 1 retry em timeout | | |
| **T2.2** HTML Sanitizer | 2.2.1 Implement `core/utils/html_sanitizer.py` usando `bleach` library | Remove `<script>`, `<style>`, `<iframe>`, `on*` attrs; mantém `<a>`, `<img>`, `<p>`, `<b>`, etc. | **S** | — |
| | 2.2.2 Whitelist apenas atributos seguros (`href`, `src`, `alt`, `title`) | Testes passam com safe tags; perigosos são stripados | | |
| | 2.2.3 Preserve text + basic formatting, remove CSS/JS | Saída minificada e sem <style> | | |
| **T2.3** Metadata Extractor | 2.3.1 Implement `core/utils/metadata_extractor.py`: extract H1, meta desc, canonical, publish date | Função retorna dict: `{title, meta_desc, canonical_url, pub_date}` | **S** | T2.2 |
| | 2.3.2 Use BeautifulSoup para parsing; fallbacks para campos faltantes | Testes com HTML samples; dates em ISO format ou `null` | | |
| **T2.4** TOC Extractor | 2.4.1 Implement `core/utils/toc_extractor.py`: scan H2/H3, build hierarchical JSON | Retorna array com `{level, text, id}` (hierarquia) | **M** | T2.2 |
| | 2.4.2 Handle nested H3 under H2; skip orphaned H3s | Testes com fixtures; passa em HTML com múltiplos níveis | | |
| | 2.4.3 Minify for compact storage | JSON minificado sem whitespace | | |
| **T2.5** Fetch + Process Pipeline | 2.5.1 Implement `scripts/fetch_and_sanitize.py`: CLI que aceita list de URLs | Script lê URLs, chama T2.1-T2.4, salva JSON em `data/original_sources/{id}.json` | **M** | T2.1-T2.4 |
| | 2.5.2 Gera UUID para cada fonte; lida com duplicatas | Sem overwrite sem flag; logs deduplication | | |
| | 2.5.3 Persiste: `{raw_html, sanitized_html, toc, metadata, id, fetched_at}` | Ficheiro JSON validável contra schema | | |

---

#### **Bloco 3: Prompt Loader (Priority 3)** 🔧

| Task | Subtarefas | Aceitação | Esforço | Deps |
|---|---|---|---|---|
| **T3.1** Canonical Loader Module | 3.1.1 Refactor `core/toc-test-1.py` → `core/loaders/prompt_loader.py` | Módulo `PromptLoader` com método `load_bundle(prompt_id, overrides=None)` | **S** | T1.1 |
| | 3.1.2 Resolve: `prompt.yaml` → runtime → provider → model → preset + overrides | Bundle retorna: `{runtime_resolved, messages, schema, schema_minified}` | | |
| | 3.1.3 Template rendering (Jinja2): context + tocs + article_title | Messages renderizadas prontas para LLM | | |
| | 3.1.4 Secrets não aparecem no bundle (log only `{api_key_present, api_key_source}`) | Testes verificam não-logging de secrets | | |
| **T3.2** Bundle Schema & Validation | 3.2.1 Define output type `PromptBundle(dataclass)` com typed fields | Estrutura: `runtime`, `messages`, `schema`, `schema_minified`, `config` | **S** | T3.1 |
| | 3.2.2 Unit tests: carregar `toc_generation` prompt e validar bundle | Tests mock não chamam API; verificam estrutura do bundle | | |

---

#### **Bloco 4: Provider Abstraction (Priority 4)** ⚙️

| Task | Subtarefas | Aceitação | Esforço | Deps |
|---|---|---|---|---|
| **T4.1** Abstract Provider Interface | 4.1.1 Implement `core/providers/base.py`: `ProviderAdapter` interface abstrata | Define métodos: `send_messages(messages, **params) → ProviderResponse` | **M** | T1.2 |
| | 4.1.2 Normalize response: `{status, content, finish_reason, usage}` | Todos adapters retornam este shape | | |
| | 4.1.3 Factory: `get_provider(provider_name) → ProviderAdapter` | Dadas configs, retorna adapter instânciado | | |
| **T4.2** Mistral Adapter | 4.2.1 Implement `core/providers/mistral_adapter.py` | Wraps `mistralai.Mistral` SDK | **S** | T4.1, T1.2 |
| | 4.2.2 Maps our params to Mistral SDK: `max_tokens` (ours) → `max_tokens` (SDK), etc. | Adapter testa mapping com mocked SDK | | |
| | 4.2.3 Extracts response content + metadata | Response normalizado vs output interface | | |
| **T4.3** Provider Tests | 4.3.1 Unit tests com mocked SDK; nenhuma chamada real à API | Tests de sucesso, falha, timeout | **S** | T4.1, T4.2 |
| | 4.3.2 Integration test (opcional): real Mistral call se env var presente | Skip se não autenticado | | |

---

#### **Bloco 5: Orchestrator & Validation (Priority 5)** 🚀

| Task | Subtarefas | Aceitação | Esforço | Deps |
|---|---|---|---|---|
| **T5.1** Output Validator | 5.1.1 Implement `core/validators/output_validator.py` (refactor de T2.5 logic) | Função `validate_output(response, schema, rules)` | **M** | T3.1 |
| | 5.1.2 Regras: JSON parse, minified, schema validation, disallow_markdown | Retorna `{valid, errors, cleaned_output}` ou raise exception | | |
| | 5.1.3 Recovery: `validate_with_recovery()` tenta auto-minify/fix antes de fail | Tenta recuperação 1x antes de retornar erro | | |
| **T5.2** Retry Orchestrator | 5.2.1 Implement `core/orchestrators/retry_handler.py` baseado em `prompt.yaml` rules | Config: `max_retries`, `retry_on` (json_error, schema_error, validation_error) | **M** | T5.1, T4.3 |
| | 5.2.2 Strategy: regenerate messages (re-render templates, new API call) | Tries até max_retries; logs cada tentativa | | |
| | 5.2.3 Fallback: se falhar, retorna erro estruturado | Não loopa infinito | | |
| **T5.3** End-to-End Runner | 5.3.1 Implement `core/runner.py`: orquestra F1-F5 completas | CLI: `python core/runner.py --urls file.txt --article-title "..." --output data/` | **L** | T2.5, T3.1, T4.3, T5.1, T5.2 |
| | 5.3.2 Fluxo: load sources → build context → create request doc → call prompt → validate+retry → persist | E2E tests com mock provider | | |
| | 5.3.3 Persistence: salva `content_generation_requests/{id}.json` com `{generated_toc_proposals, selected_title, initial_context, ...}` | Ficheiro JSON válido | | |
| | 5.3.4 Logging: cada fase logada; erros são estruturados | Logs em `logs/runner.log`; debug opcional | | |

---

#### **Bloco 6: Persistence Layer (Priority 6)** 💾

| Task | Subtarefas | Aceitação | Esforço | Deps |
|---|---|---|---|---|
| **T6.1** Storage Abstraction | 6.1.1 Implement `core/storage/storage_base.py`: interface abstrata (save, load, list, delete) | Define contrato para backends | **M** | — |
| | 6.1.2 Filesystem backend: `core/storage/fs_backend.py` (padrão para MVP) | Salva JSON em `data/original_sources/`, `data/content_generation_requests/` | | |
| **T6.2** Optional: Mongo Backend | 6.2.1 Implement `core/storage/mongo_backend.py` (future, não MVP critico) | Opcional; integra PyMongo | **L** | T6.1 |

---

#### **Bloco 7: Testing & CI (Priority 7)** ✅

| Task | Subtarefas | Aceitação | Esforço | Deps |
|---|---|---|---|---|
| **T7.1** Unit Tests | 7.1.1 Tests para T2.1-T2.5 (fetch, sanitize, extract) | 80%+ coverage; fixtures com HTML samples | **M** | T2.1-T2.5 |
| | 7.1.2 Tests para T3.1-T3.2 (loader, bundle) | Mock configs; verifica bundle shape | | |
| | 7.1.3 Tests para T4.1-T4.3 (provider adapters) | Mocked SDK; nenhuma API real | | |
| | 7.1.4 Tests para T5.1-T5.3 (validator, retry, runner) | Integration tests com mock provider | | |
| **T7.2** CI Pipeline | 7.2.1 GitHub Action: `tests.yml` que roda `pytest` + coverage | Rejeita PRs com coverage < 70% | **S** | T7.1 |
| | 7.2.2 Config validation check (T1.1) + secrets linter | Testa schemas + verifica não-secrets | | |
| | 7.2.3 Code lint (flake8/pylint) + format (black) | Rejeita code style violations | | |

---

### Mapa de Dependências 🔗

```
T1.1 (Config Schema)
├─ T1.2 (Secrets Hygiene)
├─ T2.1 (HTML Fetcher)
├─ T3.1 (Loader)
└─ T4.1 (Provider Interface)

T2.1-T2.4 (Fetch/Sanitize/Extract)
└─ T2.5 (Fetch + Process Pipeline)
    └─ T5.3 (Runner E2E)

T3.1 (Loader)
├─ T3.2 (Bundle Schema)
└─ T5.3 (Runner E2E)

T4.1 (Provider Interface)
├─ T4.2 (Mistral Adapter)
├─ T4.3 (Provider Tests)
└─ T5.3 (Runner E2E)

T5.1 (Output Validator)
├─ T5.2 (Retry Orchestrator)
└─ T5.3 (Runner E2E)

T5.3 (Runner E2E)
└─ T6.1 (Storage Abstraction)
    └─ T7.1 (Unit Tests)
        └─ T7.2 (CI Pipeline)
```

---

### Estimativas por Bloco 📊

| Bloco | Tasks | Esforço Total | Prazo (est.) |
|---|---|---|---|
| 1. Config & Security | T1.1, T1.2 | **S** | ~0.5 dia |
| 2. Fetch & Sanitize | T2.1-T2.5 | **M** | ~2 dias |
| 3. Prompt Loader | T3.1, T3.2 | **S** | ~0.5 dia |
| 4. Provider Adapters | T4.1-T4.3 | **M** | ~1 dia |
| 5. Orchestrator & Validation | T5.1-T5.3 | **L** | ~2 dias |
| 6. Persistence | T6.1, (T6.2) | **M** | ~1 dia (+ 0.5 se Mongo) |
| 7. Testing & CI | T7.1, T7.2 | **M** | ~1.5 dias |
| **TOTAL MVP** | **15** | **~6M+** | **~8 dias** |

---

### Funcionalidades Não-MVP (Future) 🚧

- [ ] MongoDB backend + migrations
- [ ] Segundo provider (OpenAI, Groq, Ollama)
- [ ] HTTP API / Web UI
- [ ] Análise inicial com IA (F3) — opcional para MVP v1, requer prompt adicional
- [ ] Rate-limiting & advanced retry policies
- [ ] Observability & metrics
- [ ] Backup & disaster recovery

---

### Open Decisions 🤔

1. **Armazenamento MVP**: Filesystem JSON ✅ ou aguardar MongoDB (T6.2)?  
2. **Análise IA (F3)**: Incluir no MVP ou deixar para v1.1?  
3. **CLI vs API**: MVP apenas CLI (`python core/runner.py`)? Ou adicionar FastAPI?  
4. **Validação Minified**: Aceitar auto-minify com revalidação (pragmático) ou fail-fast (strict)?

> Pronto para confirmar esse mapa? Recomendo validar decisões acima e depois partir para implementação por bloco (1 → 2 → 3 → 4 → 5 → 6 → 7).

User: Sugira um modelo de documento json para mongo dbs

GitHub Copilot: ## MongoDB Schema para Content Copilot MVP 🗄️

Dois modelos principais de documento baseados no MVP mapeado:

---

### 1. Collection: `original_sources`

```json
{
  "_id": "uuid-v4-string",
  "metadata": {
    "source_url": "https://example.com/article",
    "canonical_url": "https://example.com/article-canonical",
    "title": "H1 da página",
    "meta_description": "Meta description extraída",
    "language": "pt-BR",
    "pub_date": "2026-02-06T10:30:00Z",
    "categories": ["tech", "ai"],
    "tags": ["prompt", "llm"]
  },
  "content": {
    "raw_html": "<html>...</html>",
    "sanitized_html": "<p>...</p>",
    "text_extracted": "Texto limpo sem tags"
  },
  "toc": [
    {
      "level": 2,
      "text": "Seção Principal",
      "id": "secao-principal"
    },
    {
      "level": 3,
      "text": "Subsection",
      "id": "subsection"
    }
  ],
  "ia_analysis": {
    "title": "Título extraído pela IA",
    "summary": "Resumo estruturado (max 300 palavras)",
    "key_points": [
      "Ponto 1",
      "Ponto 2",
      "Ponto 3"
    ],
    "sentiment": "positive",
    "processed_at": "2026-02-06T11:00:00Z",
    "model_used": "mistral-large-latest",
    "tokens_used": {
      "input": 850,
      "output": 250
    }
  },
  "processing": {
    "fetched_at": "2026-02-06T10:30:00Z",
    "sanitized_at": "2026-02-06T10:31:00Z",
    "analyzed_at": "2026-02-06T11:00:00Z",
    "status": "completed",
    "errors": []
  },
  "metadata_audit": {
    "created_at": "2026-02-06T10:30:00Z",
    "updated_at": "2026-02-06T11:00:00Z",
    "created_by": "runner-v1",
    "version": 1
  }
}
```

**Índices recomendados:**
```javascript
db.original_sources.createIndex({ "metadata.source_url": 1 }, { unique: true });
db.original_sources.createIndex({ "processing.status": 1 });
db.original_sources.createIndex({ "metadata.pub_date": -1 });
db.original_sources.createIndex({ "ia_analysis.key_points": 1 });
```

---

### 2. Collection: `content_generation_requests`

```json
{
  "_id": "uuid-v4-string",
  "request_metadata": {
    "created_at": "2026-02-06T11:05:00Z",
    "updated_at": "2026-02-06T11:15:00Z",
    "status": "completed",
    "user_id": "user-123",
    "request_name": "MVP Test Run - Content Ideas"
  },
  "sources": {
    "source_ids": ["source-id-1", "source-id-2", "source-id-3"],
    "count": 3,
    "fetched_at": "2026-02-06T10:30:00Z"
  },
  "initial_context": {
    "consolidation_type": "automated",
    "context_text": "## RESUMO 1: Artigo sobre...\n## RESUMO 2: ...\n## TOC 1: ...\n## TOC 2: ...",
    "summaries_count": 3,
    "tocs_count": 3,
    "generated_at": "2026-02-06T11:05:00Z"
  },
  "content_ideas": {
    "ideas": [
      "Ideia de título 1",
      "Ideia de título 2",
      "Ideia de título 3"
    ],
    "count": 3,
    "generated_at": "2026-02-06T11:06:00Z",
    "model_used": "mistral-large-latest",
    "tokens_used": {
      "input": 2500,
      "output": 150
    }
  },
  "selected_title": "Ideia de título 2",
  "selected_title_at": "2026-02-06T11:06:30Z",
  "toc_proposals": {
    "proposals": [
      {
        "proposal_id": 1,
        "proposal_type": "frequency_optimized",
        "description": "Baseada em estruturas mais recorrentes",
        "toc": [
          {
            "level": 1,
            "text": "Introdução",
            "id": "intro"
          },
          {
            "level": 2,
            "text": "Conceitos Fundamentais",
            "id": "conceitos"
          },
          {
            "level": 3,
            "text": "Definições",
            "id": "definicoes"
          }
        ],
        "total_h2": 4,
        "total_h3": 12,
        "seo_score": 8.5
      },
      {
        "proposal_id": 2,
        "proposal_type": "enhanced_relevance",
        "description": "Frequentes + novos subtítulos relevantes",
        "toc": [],
        "total_h2": 5,
        "total_h3": 15,
        "seo_score": 8.8
      },
      {
        "proposal_id": 3,
        "proposal_type": "research_expanded",
        "description": "Ampliada com pesquisa e novos títulos",
        "toc": [],
        "total_h2": 6,
        "total_h3": 18,
        "seo_score": 9.0
      }
    ],
    "count": 3,
    "generated_at": "2026-02-06T11:10:00Z",
    "model_used": "mistral-large-latest",
    "tokens_used": {
      "input": 3200,
      "output": 1800
    }
  },
  "selected_toc": {
    "proposal_id": 2,
    "selected_at": "2026-02-06T11:12:00Z",
    "toc": []
  },
  "configuration": {
    "target_audience": "Desenvolvedores",
    "writing_style": "Technical",
    "allow_internet_sources": true,
    "max_idea_count": 5,
    "language": "pt-BR"
  },
  "execution_log": {
    "phases": [
      {
        "phase": "fetch_sources",
        "status": "completed",
        "started_at": "2026-02-06T10:30:00Z",
        "ended_at": "2026-02-06T10:45:00Z",
        "errors": []
      },
      {
        "phase": "analyze_sources",
        "status": "completed",
        "started_at": "2026-02-06T10:45:00Z",
        "ended_at": "2026-02-06T11:05:00Z",
        "errors": []
      },
      {
        "phase": "build_context",
        "status": "completed",
        "started_at": "2026-02-06T11:05:00Z",
        "ended_at": "2026-02-06T11:06:00Z",
        "errors": []
      },
      {
        "phase": "generate_ideas",
        "status": "completed",
        "started_at": "2026-02-06T11:06:00Z",
        "ended_at": "2026-02-06T11:06:30Z",
        "errors": []
      },
      {
        "phase": "generate_toc",
        "status": "completed",
        "started_at": "2026-02-06T11:06:30Z",
        "ended_at": "2026-02-06T11:10:00Z",
        "errors": []
      }
    ]
  },
  "metadata_audit": {
    "created_by": "runner-v1",
    "version": 1,
    "environment": "development"
  }
}
```

**Índices recomendados:**
```javascript
db.content_generation_requests.createIndex({ "request_metadata.status": 1 });
db.content_generation_requests.createIndex({ "request_metadata.created_at": -1 });
db.content_generation_requests.createIndex({ "request_metadata.user_id": 1 });
db.content_generation_requests.createIndex({ "sources.source_ids": 1 });
db.content_generation_requests.createIndex({ "selected_toc.proposal_id": 1 });
```

---

### 3. JSON Schema Validation (MongoDB)

```json
{
  "$jsonSchema": {
    "bsonType": "object",
    "required": ["_id", "metadata", "processing"],
    "properties": {
      "_id": { "bsonType": "string" },
      "metadata": {
        "bsonType": "object",
        "required": ["source_url"],
        "properties": {
          "source_url": { "bsonType": "string" },
          "canonical_url": { "bsonType": "string" },
          "title": { "bsonType": "string" },
          "pub_date": { "bsonType": "date" },
          "categories": { "bsonType": "array", "items": { "bsonType": "string" } }
        }
      },
      "content": {
        "bsonType": "object",
        "properties": {
          "raw_html": { "bsonType": "string" },
          "sanitized_html": { "bsonType": "string" }
        }
      },
      "toc": {
        "bsonType": "array",
        "items": {
          "bsonType": "object",
          "required": ["level", "text"],
          "properties": {
            "level": { "bsonType": "int" },
            "text": { "bsonType": "string" },
            "id": { "bsonType": "string" }
          }
        }
      },
      "ia_analysis": {
        "bsonType": "object",
        "properties": {
          "title": { "bsonType": "string" },
          "summary": { "bsonType": "string" },
          "key_points": { "bsonType": "array", "items": { "bsonType": "string" } },
          "processed_at": { "bsonType": "date" }
        }
      },
      "processing": {
        "bsonType": "object",
        "required": ["status"],
        "properties": {
          "status": { "enum": ["pending", "processing", "completed", "failed"] },
          "errors": { "bsonType": "array" }
        }
      }
    }
  }
}
```

---

### Design Decisions 🎯

| Aspecto | Decisão | Motivo |
|---|---|---|
| **Denormalization** | Armazenar `ia_analysis` e `toc` inline | Reduz joins; queries mais rápidas |
| **Arrays** | `toc_proposals` como array de objetos | Mantém histórico; fácil buscar proposta por ID |
| **Timestamps** | ISO 8601 formato (`Z` UTC) | Standard; funciona em múltiplos timezones |
| **Unique** | `source_url` unique em `original_sources` | Evita duplicatas de sources |
| **Status enum** | Valores fixos (pending, processing, completed, failed) | Facilita queries; validação no schema |
| **Nested logs** | `execution_log.phases[]` estruturado | Auditoria completa por fase |

---

### Queries Comuns 📍

```javascript
// Buscar todas as fontes processadas
db.original_sources.find({ "processing.status": "completed" })

// Buscar requisição + todas as propostas de TOC
db.content_generation_requests.findOne({ _id: "..." })

// Buscar requisições de um usuário em ordem recente
db.content_generation_requests.find({ "request_metadata.user_id": "user-123" })
  .sort({ "request_metadata.created_at": -1 })

// Buscar propostas com score SEO > 8.5
db.content_generation_requests.find({ "toc_proposals.proposals.seo_score": { $gt: 8.5 } })

// Atualizar TOC selecionado
db.content_generation_requests.updateOne(
  { _id: "..." },
  { $set: { "selected_toc": { proposal_id: 2, selected_at: new Date() } } }
)
```

---

### Pronto para integração com T6.2 (Mongo Backend)? ✅

Este schema é **backwards-compatible** com o modelo filesystem JSON (T6.1) — cada documento pode ser salvo como JSON sem mudanças estruturais.

User: Analise a imagem do protótipo da interface inicial do fluxo de geraçãode conteúdos. Responda com uma avaliação e sugestões 

GitHub Copilot: Vou analisar o protótipo da interface e oferecer uma avaliação com sugestões práticas e priorizadas. Passos: (1) avaliação geral do layout e fluxo, (2) problemas/ambigüidades imediatas, (3) recomendações UX/funcionais para MVP e evolução.

1) Avaliação geral (rápida)
- Layout claro e padrão: painel principal para inputs (`Links`, `Additional Information`) e painel lateral para `Options` + ação (`GIVE ME IDEAS`). Isso favorece foco e escaneabilidade.
- Bons elementos: campos de link múltiplos com botão de adicionar/remover, grande área para contexto adicional e seleção de provider — cobre necessidades essenciais do fluxo.

2) Problemas / ambiguidade visíveis
- Checkbox duplicado “Allow internet access” (painel) aparenta erro/duplicação e gera confusão sobre comportamento exato.
- Falta indicação de estado por fonte (fetched / sanitized / analyzed) — usuário não sabe progresso por link.
- Sem validação visível de URL ou limite de links (pode aceitar links inválidos ou maliciosos).
- Área de “Additional Information” sem orientação sobre extensão/uso (quando e como influencia a geração).
- Ação “GIVE ME IDEAS” sem indicação de tempo estimado, custos (tokens), ou confirmação de escopo (vai buscar internet? usar fontes?).
- Sem controle de prioridade/peso das fontes (todas tratadas igual por padrão).
- Falta de feedback de erros e opções de retry ou edição quando a IA falhar/retornar inválido.

3) Sugestões práticas (priorizadas) — MVP primeiro, depois melhorias

Prioridade Alta (MVP)
- Corrigir a ambiguidade do checkbox: usar rótulos distintos e explicativos — ex.: `Allow fetching links (Internet access)` e `Allow searching web for additional context` OR combinar em um único toggle com explicação curta. Adicionar tooltip explicando implicações (privacidade, custo).
- Validação de links em-line: checar formato URL, mostrar favicon/host, bloquear scripts/data: URLs, e indicar domínio/host trust score (ex.: "medium", "low").
- Indicadores de status por fonte: badge/states — queued / fetching / sanitized / analyzed / error. Permitir re-run por fonte ou excluir.
- Placeholder e microcopy na `Additional Information` explicando uso (ex.: “Contexto adicional: público, tom, pontos a priorizar — opcional, até 800 caracteres”).
- Feedback de execução: spinner + barra de progresso por etapa (fetch → analyze → generate) e uma estimativa de duração / aviso de custo. Mostrar modelo/provider escolhido e opção de cancelar.
- Resultado direto após geração: painel de propostas (lista de títulos + 3 TOC proposals) com ações: “Select title”, “Show JSON”, “Save to draft”, “Run TOC generation” (para título selecionado).

Prioridade Média
- Fonte cards expandidos: preview do `sanitized_html` e `toc` extraído por fonte; permitir editar título/slug antes de serem usados no contexto.
- Permitir controlar peso/ordem das fontes (arrastar para reordenar) para influenciar context consolidation.
- Mostrar logs/validation errors do prompt (ex.: schema validation fails) e botão “Retry with relaxed validation / regenerate”.
- Presets e advanced options: dropdown `Model Preset` (deterministic / balanced) e controles de `temperature`, `max_output_tokens` ocultos em “Advanced”.

Prioridade Baixa / Futuro
- Estimativa de custo (tokens / estimated $) antes de executar.
- Integração com WordPress: botão “Publish draft” com mapeamento de campos H2/H3 → editor.
- Histórico de runs (`content_generation_requests`), re-executar, comparar propostas e auditar tokens usados.
- Export JSON / Download of `content_generation_requests` doc.
- Accessibility: labels claros, keyboard navigation, ARIA attributes, contrast and font-scaling.

4) Micro-UX / detalhes de usabilidade
- Botões primários: usar verbos claros — ex.: `Generate ideas` em vez de `GIVE ME IDEAS` (mais profissional e i18n-friendly).
- Tooltips e help icons ao lado de `Links`, `Allow internet access`, `IA Provider` e `Additional Information`.
- Limitar número de links por default (ex.: 10) com opção “Advanced: allow more”.
- Mostrar host domain near each URL (ex.: medium.com) e um botão “inspect” que abre preview.
- Auto-detect language of sources and offer `language` option in `Options`.
- Confirm CTA with summary modal (e.g., “This run will fetch 3 URLs and use Mistral — estimated time ~20s. Continue?”) para evitar execuções acidentais.

5) Segurança / Privacidade / Legal
- Aviso e consentimento para “Allow internet access” explicando que conteúdos serão fetched and processed, plus copyright considerations.
- Não mostrar API keys in UI logs; flag `api_key_present` only.
- Provide opt-out to not store raw HTML permanently (configurable TTL).

6) Accessibility & Internationalization
- Ensure form fields have labels and error states readable by screen readers.
- Use locale-friendly date/time formatting for any timestamps.
- Support Portuguese/English i18n strings for buttons/tooltips.

7) Proposta mínima de fluxo de tela (MVP)
- Step 1: User adds links + optional extra context + chooses provider + toggle internet.
- Step 2: Click `Generate ideas` → modal confirmation → spinner.
- Step 3: Results panel shows list of title ideas + brief metadata (confidence/score) and 3 TOC proposals per title; user can select title and click `Generate TOC`.
- Step 4: Persist selection and offer “Export JSON” / “Save Draft” / “Publish”.

8) Pequenas sugestões visuais
- Use cards com subtle shadows for each Link (aiding scanning).
- Color-coded badges for source status (grey queued, blue processing, green done, red error).
- Right panel `Options` collapsible to keep focus on main area.

Resumo final (1 frase)
- O protótipo está bem estruturado; com pequenas correções semânticas (checkboxes) e a inclusão de validação/estados por fonte + feedback de execução e tooltips, torna-se um MVP robusto, seguro e usável.

Se quiser, eu descrevo as telas detalhadas do fluxo MVP (mockups textuais) ou forneço uma lista de componentes React/HTML + props para construir a interface rapidamente. Qual opção prefere?

User: AS DUAS

GitHub Copilot: Próximo passo: vou (1) descrever as telas do fluxo MVP com mockups textuais e estados, e (2) fornecer uma lista de componentes React (TypeScript) com propriedades/handlers e formas de integração. Depois disso, proponho os próximos passos de implementação.

## 1) Mockups textuais — telas e estados (MVP)

Tela A — Content Copilot (entrada)
- Header: breadcrumb + título “Content Copilot”.
- Left column: lista de links (component `LinksList`).
  - Cada link: favicon, domain, URL editable, status badge (queued / fetching / sanitized / analyzed / error), botão X (remover), ícone “preview”.
  - Botão “+ Add link” adiciona nova linha.
  - UX: validação inline de URL (error message), limite padrão 10 links.
- Centro: `Additional Information`
  - Textarea (placeholder: “Público, tom, pontos a priorizar — opcional, até 800 chars”).
  - Small hint: “Será usado como contexto adicional para o modelo.”
- Right sidebar: `Options`
  - Toggle: “Allow internet access” (single toggle; tooltip: explicação de fetch & privacy)
  - Dropdown: “IA Provider” (ex.: Mistral)
  - Preset dropdown: “Model preset” (deterministic / balanced)
  - Advanced (collapsible): temperature, max_tokens
  - CTA: primary button `Generate ideas` (disabled until >=1 valid link or text in Additional Info)
- Footer area: small summary line: “1 URL — provider Mistral — estimated ~30s”

Tela B — Confirmação modal (light)
- Summary: count links, provider, allow internet? button `Confirm & Run` / `Cancel`.
- Checkbox: “Don’t show again”.

Tela C — Execução / feedback
- Global progress: linear progress + stage chips: Fetching → Sanitizing → Analyzing → Generating.
- Per-link live status (updates badges).
- Cancel button available.
- If error per link: show error card with “Retry” / “Remove”.

Tela D — Resultados: Title Ideas
- Left: list of generated title ideas (card per title) with score/confidence and short rationale.
- Actions per title: `Select` (primary), `Regenerate ideas`, `Show JSON`.
- Right: preview of selected title’s `3 TOC proposals` (each a `TocProposalCard`).
  - Each proposal shows hierarchical list, metrics: total_h2, total_h3, seo_score.
  - Actions: `Select proposal`, `Edit proposal` (inline edit H2/H3), `Save draft`.
- Bottom: `Save request` / `Export JSON` / `Publish draft` (if integrated).

Tela E — Source preview & edit
- Modal or slide-over per source: shows `sanitized_html` truncated, extracted `toc` tree, metadata (title, meta desc, canonical, pub_date). Buttons: `Edit title`, `Ignore source`, `Boost weight` (influence ranking).

Estados de falha e retry
- Validation error after generation: show detailed message (schema error / JSON parse), buttons: `Retry (regenerate)`, `Retry (relaxed validation)`, `Open raw output`.
- Long-run warning: spinner + cancel; estimate tokens & cost warning if > threshold.

A11y & i18n notes
- All controls labelled, keyboard navigable, ARIA roles on lists, large hit targets; UI strings externalized for i18n (pt/en).

## 2) Componentes React (TypeScript) — estrutura e props

Arquitetura geral: componente `ContentCopilotApp` (state management via React + Context or Zustand). Comunica com APIs locais: `/api/fetch-sources`, `/api/generate-ideas`, `/api/status/:id`.

Component list (interface summaries):

- LinksList
  - Props: links: LinkItemData[], onAdd():void, onRemove(id):void, onChange(id, partial):void, onPreview(id):void
  - LinkItemData: { id: string; url: string; domain?:string; status: 'queued'|'fetching'|'sanitized'|'analyzed'|'error'; error?:string; weight?:number }

- UrlInput (single)
  - Props: value:string, onChange(str):void, onValidate(url):Promise<boolean>

- AdditionalInfo
  - Props: value:string, onChange(str):void, maxLength?:number

- OptionsPanel
  - Props: allowInternet:boolean, onToggleInternet(bool):void, provider:string, onProviderChange(name):void, preset:string, onPresetChange(name):void, advanced:AdvancedOptions
  - AdvancedOptions: { temperature:number, max_tokens:number, showAdvanced:boolean }

- GenerateButton
  - Props: disabled:boolean, onClick():Promise<void>, label?:string
  - Emits event that triggers confirmation modal then run.

- ConfirmationModal
  - Props: open:boolean, summary:RunSummary, onConfirm():void, onCancel():void

- ProgressBar
  - Props: stages: Stage[], currentStageIndex:number
  - Stage: { key:string, label:string, status:'pending'|'running'|'done'|'error' }

- SourceCard
  - Props: source: OriginalSource, onEdit(), onRemove(), onRetry(), onBoost()

- ResultList (Title Ideas)
  - Props: ideas: TitleIdea[], onSelect(id):void, onRegenerate():void
  - TitleIdea: { id:string, title:string, score?:number, rationale?:string }

- TocProposalCard
  - Props: proposal: TocProposal, selected:boolean, onSelect(), onEdit(), onExport()
  - TocProposal: { id:number, type:string, description?:string, toc: TocNode[], seo_score?:number, total_h2?:number, total_h3?:number }

- TocEditor (inline editing)
  - Props: toc: TocNode[], onChange(newToc):void

- ApiClient (service module, not a UI comp)
  - Methods:
    - fetchSources(urls:string[]): Promise<{sources:OriginalSource[], jobId:string}>
    - getJobStatus(jobId:string): Promise<JobStatus>
    - generateIdeas(jobId:string, options): Promise<GenerateResult>
    - persistRequest(payload): Promise<{id:string}>

Data shapes (TypeScript snippets):
- OriginalSource
  - { id:string; source_url:string; canonical_url?:string; title?:string; meta_description?:string; raw_html?:string; sanitized_html?:string; toc?:TocNode[]; ia_analysis?:IAAnalysis; processing:{ status:string; fetchedAt?:string; errors?:string[] } }

- TocNode
  - { id:string; level:number; text:string; children?:TocNode[] }

- GenerateResult
  - { requestId:string; titleIdeas:TitleIdea[]; tocProposals?: TocProposal[]; meta:{model:string, tokens:{input:number,output:number}} }

Eventos & fluxo (callbacks)
- User clicks Generate → GenerateButton calls ApiClient.fetchSources(links) → receives jobId → poll getJobStatus(jobId) updating per-link statuses → when status = ready, call ApiClient.generateIdeas(jobId, {provider,preset,additionalInfo}) → receive GenerateResult and show in ResultList.

Erro handling
- All API calls return structured error { code, message, details }.
- UI shows non-blocking toast + actionable button.

Styling & UX
- Use design tokens; components responsive.
- Badges color mapping: queued(gray), fetching(blue), sanitized(green), error(red).

## 3) Endpoints sugeridos da API (minimal)
- POST /api/fetch-sources { urls: string[] } → 202 { jobId }
- GET /api/status/:jobId → 200 { jobId, sources: [{id,status,error?}], stage, progress }
- POST /api/generate-ideas { jobId, article_title?, additional_info, options } → 200 { requestId, titleIdeas, tocProposals }
- GET /api/request/:requestId → 200 { full request document } (persisted)

## 4) Prioridades para implementação front-end (curto prazo)
1. Implementar `LinksList`, `AdditionalInfo`, `OptionsPanel`, `GenerateButton` + confirmation modal. Wire to mock ApiClient.
2. Implement polling UI for job status and per-link badges.
3. Implement `ResultList` + `TocProposalCard` + selection flow.
4. Add `SourceCard` preview/edit and inline TOC editor (optional MVP stretch).
5. Add persistence/export and integration with backend.

## 5) Próximos passos recomendados (rápidos)
- Confirme: linguagem do UI (pt-BR ok) e se prefere CLI-only backend ou API para integração com frontend.
- Posso gerar: (A) especificação JSON para componentes (props + example states) ou (B) esqueleto React + sample mock server (Express) para rodar localmente. Quer que eu gere o código do esqueleto agora?

Escolha se quer que eu gere o esqueleto React + servidor mock agora (faço arquivos necessários, basic runner e instruções).

User: Idioma pt-BR
Crie um documento de especificação de requisitos com essas definições.

GitHub Copilot: Vou criar um documento de especificação de requisitos completo em pt-BR consolidando todas as definições. Preparando a estrutura e salvando o arquivo no repositório. 

Searched for files matching `**/content-copilot/docs`, no matches

Localizando diretório de documentação para colocar a especificação. 

Read [](file:///home/chico/pigmeu-productnauta/pigmeu/content-copilot)

Criando documento de especificação de requisitos no diretório `docs`. Este será um documento de referência completo para o MVP.

```markdown
# Especificação de Requisitos — Content Copilot MVP

**Versão:** 1.0  
**Data:** 2026-02-06  
**Status:** Rascunho para aprovação  
**Idioma:** pt-BR  

---

## 1. Visão Geral

O **Content Copilot** é uma aplicação web que automatiza a geração de ideias de conteúdo e estruturas de título (TOC — Table of Contents) para blogs, baseada em análise de múltiplas fontes com inteligência artificial.

### Objetivo do MVP

Provar o fluxo end-to-end: **entrada de URLs → fetch & sanitize → análise com IA → consolidação de contexto → geração de 3 propostas de TOC → persistência**.

### Escopo MVP (in)

- Fetch de múltiplas URLs com sanitização de HTML.
- Extração automática de metadata (título, meta description, canonical, data de publicação) e TOC (H2/H3).
- Análise inicial de cada fonte com IA (sumarização).
- Consolidação de contexto a partir de múltiplas fontes.
- Geração de ideias de títulos de artigos.
- Geração de 3 propostas de estrutura de TOC baseadas em contexto.
- Interface web para entrada de dados, visualização de progresso e seleção de resultados.
- Persistência em armazenamento local (filesystem JSON; MongoDB opcional futuro).
- Testes unitários e CI/CD básico.

### Escopo MVP (out)

- Interface de publicação direta no WordPress.
- Múltiplos providers de IA (apenas Mistral para MVP).
- Edição avançada inline de TOCs.
- Análise inicial com IA (F3 — pode ser implementado em paralelo ou v1.1).
- Rate-limiting e cotas de uso.
- Dashboard de métricas/histórico.
- Backup e recuperação de desastres.

---

## 2. Requisitos Funcionais

### F1 — Fetch & Sanitize de Fontes

**Descrição:**  
Dado uma lista de URLs, o sistema deve obter o HTML completo, sanitizar (remover scripts/CSS maliciosos) e extrair metadados e estrutura.

**Inputs:**
- Lista de URLs (até 10 por padrão; máximo 50 com flag avançada).
- Configuração: permitir acesso à internet (sim/não).

**Outputs:**
- Para cada URL: documento `original_source` com:
  - `raw_html`: HTML original completo.
  - `sanitized_html`: HTML seguro (sem scripts, estilos, atributos on*).
  - `metadata`: título H1, meta description, canonical URL, data de publicação (se encontrada).
  - `toc`: estrutura hierárquica H2/H3.
  - `status`: queued → fetching → sanitized → analyzed → completed ou error.
  - `fetched_at`, `sanitized_at`: timestamps.
  - `errors`: lista de erros durante o processamento.

**Critérios de aceitação:**
- [ ] Função `fetch_html(url)` aceita URL válida, trata timeouts (5s), redirects e erros HTTP.
- [ ] Função `sanitize_html(html)` remove `<script>`, `<style>`, `<iframe>`, `on*` attrs; mantém `<p>`, `<a>`, `<img>`, `<b>`, `<i>`, `<figure>`, `<figcaption>`.
- [ ] Função `extract_metadata(html)` retorna dict com `title`, `meta_description`, `canonical_url`, `pub_date` (ISO 8601 ou null).
- [ ] Função `extract_toc(html)` scaneia H2/H3, retorna array: `[{level:2, text:"...", id:"..."}, ...]`.
- [ ] CLI `scripts/fetch_and_sanitize.py` aceita arquivo com URLs e salva JSON em `data/original_sources/`.
- [ ] Duplicatas por `source_url` detectadas e alertadas.
- [ ] Testes unitários cobrem casos normais, malformados e erro.

**Esforço estimado:** S  
**Dependências:** T1.2 (secrets)  

---

### F2 — Análise Inicial com IA (Opcional MVP, v1.1)

**Descrição:**  
Para cada fonte sanitizada, chamar prompt `initial-content-analysis` para sumarizar.

**Inputs:**
- `sanitized_html` de cada fonte.
- Prompt bundle (runtime, templates, schema).

**Outputs:**
- `ia_analysis`: { title, summary (max 300 palavras), key_points[], processed_at, model_used, tokens }

**Critérios de aceitação:**
- [ ] Função `analyze_source(sanitized_html)` chama IA e valida contra schema.
- [ ] Testes com mock provider.

**Esforço estimado:** M  
**Dependências:** T3.1 (loader), T4.3 (provider)  
**Nota:** Pode ser implementado em paralelo com outras tarefas; não bloqueia geração de TOC.

---

### F3 — Consolidação de Contexto

**Descrição:**  
Dado resumos e TOCs de múltiplas fontes, consolidar em um único contexto estruturado para passar ao prompt `toc_generation`.

**Inputs:**
- Array de `ia_analysis.summary` (cada fonte).
- Array de `toc` (cada fonte).
- `article_title` (selecionado pelo usuário).
- `additional_info` (contexto adicional do usuário, texto livre).

**Outputs:**
- `initial_context`: markdown consolidado com seções `## RESUMO 1: [TÍTULO]`, `## TOC 1: [TÍTULO]`, etc.
- Salvo em documento `content_generation_requests`.

**Critérios de aceitação:**
- [ ] Função `build_context(summaries, tocs, article_title)` concatena com marcadores claros.
- [ ] Output é Markdown legível e minificável.

**Esforço estimado:** S  
**Dependências:** F2 (análise)  

---

### F4 — Geração de Propostas de TOC

**Descrição:**  
Chamar prompt `toc_generation` com contexto consolidado e artigo título. IA retorna 3 propostas de estrutura.

**Inputs:**
- `initial_context` (Markdown).
- `article_title` (selecionado).
- Configurações: provider, model, preset, parâmetros.

**Outputs:**
- `toc_proposals[3]`:
  - proposal_id: 1, 2, 3.
  - proposal_type: "frequency_optimized", "enhanced_relevance", "research_expanded".
  - description: explicação da proposta.
  - toc: array hierárquico `[{level, text, id, children?}, ...]`.
  - metrics: total_h2, total_h3, seo_score.
- Salvo em `content_generation_requests.generated_toc_proposals`.

**Critérios de aceitação:**
- [ ] Loader carrega prompt `toc_generation`, renderiza templates com contexto.
- [ ] Provider adapter chama Mistral.
- [ ] Output validado contra schema JSON (`output.schema.json`).
- [ ] Retry automático (até `max_retries`) em erro de validação.
- [ ] Testes com mock provider e validação de estrutura.

**Esforço estimado:** M  
**Dependências:** T3.1, T4.3, T5.1, T5.2  

---

### F5 — Persistência de Resultados

**Descrição:**  
Salvar documentos `original_sources` e `content_generation_requests` em backend (filesystem JSON ou MongoDB).

**Outputs:**
- `data/original_sources/{id}.json`: documentos de fonte.
- `data/content_generation_requests/{id}.json`: documentos de requisição (com ideias, TOCs, seleções).

**Critérios de aceitação:**
- [ ] Filesystem backend salva JSON válido, estruturado conforme schema MongoDB.
- [ ] Carregamento e listagem de documentos.
- [ ] Testes de persistência e leitura.

**Esforço estimado:** S  
**Dependências:** T6.1  

---

### F6 — Interface Web (MVP)

**Descrição:**  
Interface para usuário entrada de URLs, visualização de progresso, seleção de resultados.

**Telas principais:**
1. **Entrada (Links + Options):**
   - Lista de URLs (adicionar/remover).
   - Checkbox "Permitir acesso à internet".
   - Dropdown: Provider, Preset.
   - Textarea: Informações adicionais.
   - Botão: "Gerar ideias" (confirmação modal).

2. **Progresso (live):**
   - Barra de progresso com stages.
   - Status por link (badges).
   - Cancel button.

3. **Resultados (Ideias + TOC):**
   - Lista de ideias de título com scores.
   - Cards de 3 propostas de TOC (hierarquia, métricas).
   - Ações: selecionar, editar (opcional), exportar, salvar.

**Critérios de aceitação:**
- [ ] Componentes React (TypeScript) com props tipadas.
- [ ] Integração com API backend (endpoints descritos abaixo).
- [ ] Formulário de entrada validado (URLs, limites).
- [ ] Progresso em tempo real (polling ou WebSocket).
- [ ] Tratamento de erros (notificações, retry).
- [ ] Acessibilidade: labels, ARIA, contraste, teclado.
- [ ] i18n pt-BR.

**Esforço estimado:** M  
**Dependências:** Backend completo (T1–T5)  

---

## 3. Requisitos Não-Funcionais

### Performance

- **Fetch**: máximo 5s por URL (timeout).
- **Sanitização**: < 500ms por URL.
- **IA generation**: < 60s (depende do modelo; aviso se > 30s).
- **Interface**: carregamento < 2s; progresso live atualiza a cada 1s.

### Segurança

- [ ] Sem hardcoded API keys em tracked files; use `env_api_key`.
- [ ] Validação de URL: rejeitar `javascript:`, `data:`, URLs malformadas.
- [ ] HTML sanitização robusta (whitelist de tags e attrs).
- [ ] Logs: nunca logar `api_key` completa; usar `api_key_present` e `api_key_source`.
- [ ] CORS configurado para origin permitida.
- [ ] Rate-limiting básico (5 requisições/min por IP, opcional MVP).

### Escalabilidade (future)

- Arquitetura pronta para async queues (Celery/Bull).
- Persistência agnóstica (filesystem → MongoDB sem refactor).
- API stateless (suportando múltiplas instâncias).

### Observabilidade

- [ ] Logging estruturado por fase (fetch, analyze, generate).
- [ ] Arquivo log: `logs/runner.log`.
- [ ] Debug mode opcional (verbose logging).

### Acessibilidade (WCAG 2.1 AA)

- [ ] Formulários com labels explícitas.
- [ ] Navegação por teclado.
- [ ] Contraste mínimo 4.5:1.
- [ ] Ícones com `aria-label`.
- [ ] Mensagens de erro claras.

---

## 4. Requisitos Técnicos

### Stack recomendado

**Backend:**
- Python 3.10+
- FastAPI ou Flask (para API REST).
- Mistral SDK (`mistralai`).
- BeautifulSoup4 (parsing HTML).
- Bleach (sanitização).
- Jinja2 (template rendering).
- Pydantic (validação).
- pytest (testes).
- MongoDB (opcional) ou filesystem JSON.

**Frontend:**
- React 18+ (TypeScript).
- Vite ou Create React App.
- TailwindCSS ou CSS Modules (styling).
- React Query ou SWR (data fetching).
- Zustand ou Context API (state).
- i18next (i18n).
- Vitest ou Jest (testes).

**DevOps:**
- GitHub Actions (CI/CD).
- Docker (containerização, optional MVP).
- Poetry ou pip (Python deps).
- npm/yarn (JS deps).

### Estrutura de diretórios esperada

```
pigmeu/content-copilot/
├── backend/
│   ├── core/
│   │   ├── __init__.py
│   │   ├── loaders/
│   │   │   └── prompt_loader.py
│   │   ├── providers/
│   │   │   ├── base.py
│   │   │   └── mistral_adapter.py
│   │   ├── validators/
│   │   │   └── output_validator.py
│   │   ├── orchestrators/
│   │   │   └── retry_handler.py
│   │   ├── utils/
│   │   │   ├── html_fetcher.py
│   │   │   ├── html_sanitizer.py
│   │   │   ├── metadata_extractor.py
│   │   │   └── toc_extractor.py
│   │   ├── storage/
│   │   │   ├── base.py
│   │   │   └── fs_backend.py
│   │   ├── runner.py
│   │   └── api.py (FastAPI app)
│   ├── configs/
│   │   ├── providers.yaml
│   │   ├── model_presets.yaml
│   │   └── schema/
│   │       ├── providers_schema.json
│   │       └── presets_schema.json
│   ├── prompts/
│   │   ├── prompts.yaml
│   │   └── toc_generation/
│   │       ├── prompt.yaml
│   │       ├── template/
│   │       │   ├── system-prompt.md
│   │       │   └── user-message.md
│   │       └── schemas/
│   │           └── output.schema.json
│   ├── tests/
│   │   ├── test_fetcher.py
│   │   ├── test_sanitizer.py
│   │   ├── test_extractor.py
│   │   ├── test_loader.py
│   │   ├── test_provider.py
│   │   ├── test_validator.py
│   │   ├── test_runner.py
│   │   └── conftest.py
│   ├── requirements.txt
│   ├── pyproject.toml
│   └── README.md
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── LinksList.tsx
│   │   │   ├── AdditionalInfo.tsx
│   │   │   ├── OptionsPanel.tsx
│   │   │   ├── GenerateButton.tsx
│   │   │   ├── ConfirmationModal.tsx
│   │   │   ├── ProgressBar.tsx
│   │   │   ├── SourceCard.tsx
│   │   │   ├── ResultList.tsx
│   │   │   ├── TocProposalCard.tsx
│   │   │   └── TocEditor.tsx
│   │   ├── services/
│   │   │   └── apiClient.ts
│   │   ├── types/
│   │   │   └── index.ts
│   │   ├── i18n/
│   │   │   ├── pt-BR.json
│   │   │   └── en-US.json (future)
│   │   ├── App.tsx
│   │   └── main.tsx
│   ├── tests/
│   │   └── (vitest/jest)
│   ├── package.json
│   ├── tsconfig.json
│   ├── vite.config.ts
│   └── README.md
├── docs/
│   ├── especificacao-requisitos.md (este arquivo)
│   ├── mongodb-schema.md
│   └── api-endpoints.md
├── scripts/
│   ├── fetch_and_sanitize.py
│   └── setup_db.py
└── .github/
    └── workflows/
        └── tests.yml
```

---

## 5. Endpoints da API (Backend)

Versão: `v1` (prefix: `/api/v1`)

### POST `/api/v1/sources/fetch`

**Descrição:** Inicia fetching de múltiplas URLs.

**Request:**
```json
{
  "urls": ["https://...", "https://..."],
  "allow_internet": true
}
```

**Response (202 Accepted):**
```json
{
  "job_id": "uuid-v4",
  "status": "queued",
  "sources_count": 2
}
```

**Error (400, 422):**
```json
{
  "code": "invalid_urls",
  "message": "Uma ou mais URLs inválidas.",
  "details": ["URL 1: formato inválido", "..."]
}
```

---

### GET `/api/v1/jobs/:job_id/status`

**Descrição:** Obtém status de um job de fetch.

**Response (200 OK):**
```json
{
  "job_id": "uuid-v4",
  "status": "processing",
  "stage": "analyzing",
  "progress_percent": 65,
  "sources": [
    {
      "id": "src-1",
      "url": "https://...",
      "status": "sanitized",
      "fetched_at": "2026-02-06T11:00:00Z",
      "error": null
    },
    {
      "id": "src-2",
      "url": "https://...",
      "status": "fetching",
      "error": null
    }
  ],
  "completed_at": null
}
```

---

### POST `/api/v1/ideas/generate`

**Descrição:** Gera ideias de títulos e 3 propostas de TOC.

**Request:**
```json
{
  "source_ids": ["src-1", "src-2"],
  "article_title": "Artigo sobre...",
  "additional_info": "Público: devs; tom: técnico",
  "options": {
    "provider": "mistral",
    "model_preset": "deterministic_json",
    "allow_internet": true,
    "language": "pt-BR"
  }
}
```

**Response (200 OK):**
```json
{
  "request_id": "req-uuid",
  "title_ideas": [
    {
      "id": "idea-1",
      "title": "Ideia de título 1",
      "score": 8.5,
      "rationale": "Baseada em frequência de tópicos"
    }
  ],
  "toc_proposals": [
    {
      "proposal_id": 1,
      "type": "frequency_optimized",
      "description": "...",
      "toc": [...],
      "metrics": {
        "total_h2": 5,
        "total_h3": 15,
        "seo_score": 8.5
      }
    }
  ],
  "metadata": {
    "model_used": "mistral-large-latest",
    "tokens": { "input": 2500, "output": 1200 },
    "generated_at": "2026-02-06T11:05:00Z"
  }
}
```

---

### POST `/api/v1/requests/:request_id/select`

**Descrição:** Persiste seleção de título e proposta de TOC.

**Request:**
```json
{
  "title_id": "idea-1",
  "toc_proposal_id": 2
}
```

**Response (200 OK):**
```json
{
  "request_id": "req-uuid",
  "selected_title": "Ideia de título 1",
  "selected_toc": { "proposal_id": 2, "toc": [...] },
  "status": "completed"
}
```

---

### GET `/api/v1/requests/:request_id`

**Descrição:** Obtém documento completo de uma requisição.

**Response (200 OK):**
```json
{
  "_id": "req-uuid",
  "request_metadata": {
    "created_at": "2026-02-06T11:05:00Z",
    "status": "completed"
  },
  "sources": { "source_ids": [...], "count": 2 },
  "initial_context": "...",
  "title_ideas": [...],
  "toc_proposals": [...],
  "selected_title": "...",
  "selected_toc": {...}
}
```

---

### GET `/api/v1/requests?user_id=...&limit=10`

**Descrição:** Lista requisições (com paginação).

**Response (200 OK):**
```json
{
  "requests": [...],
  "total": 42,
  "limit": 10,
  "offset": 0
}
```

---

## 6. Componentes React (TypeScript)

### LinksList.tsx

```typescript
interface LinkItemData {
  id: string;
  url: string;
  domain?: string;
  status: 'queued' | 'fetching' | 'sanitized' | 'analyzed' | 'error';
  error?: string;
  weight?: number;
}

interface LinksListProps {
  links: LinkItemData[];
  onAdd(): void;
  onRemove(id: string): void;
  onChange(id: string, partial: Partial<LinkItemData>): void;
  onPreview(id: string): void;
}

export const LinksList: React.FC<LinksListProps> = ({ ... }) => { ... }
```

### AdditionalInfo.tsx

```typescript
interface AdditionalInfoProps {
  value: string;
  onChange(text: string): void;
  maxLength?: number; // default 800
  placeholder?: string;
}
```

### OptionsPanel.tsx

```typescript
interface AdvancedOptions {
  temperature: number;
  max_tokens: number;
  showAdvanced: boolean;
}

interface OptionsPanelProps {
  allowInternet: boolean;
  onToggleInternet(flag: boolean): void;
  provider: string;
  onProviderChange(name: string): void;
  preset: string;
  onPresetChange(name: string): void;
  advanced: AdvancedOptions;
  onAdvancedChange(partial: Partial<AdvancedOptions>): void;
}
```

### GenerateButton.tsx

```typescript
interface GenerateButtonProps {
  disabled: boolean;
  onClick(): Promise<void>;
  label?: string; // default: "Gerar ideias"
  loading?: boolean;
}
```

### ConfirmationModal.tsx

```typescript
interface RunSummary {
  urlCount: number;
  provider: string;
  estimatedTime?: string;
  allowInternet: boolean;
}

interface ConfirmationModalProps {
  open: boolean;
  summary: RunSummary;
  onConfirm(): void;
  onCancel(): void;
}
```

### ProgressBar.tsx

```typescript
interface Stage {
  key: string;
  label: string;
  status: 'pending' | 'running' | 'done' | 'error';
}

interface ProgressBarProps {
  stages: Stage[];
  currentStageIndex: number;
  percentComplete?: number;
}
```

### ResultList.tsx (Title Ideas)

```typescript
interface TitleIdea {
  id: string;
  title: string;
  score?: number;
  rationale?: string;
}

interface ResultListProps {
  ideas: TitleIdea[];
  onSelect(id: string): void;
  onRegenerate(): Promise<void>;
  selectedId?: string;
}
```

### TocProposalCard.tsx

```typescript
interface TocNode {
  id: string;
  level: number;
  text: string;
  children?: TocNode[];
}

interface TocProposal {
  proposal_id: number;
  type: 'frequency_optimized' | 'enhanced_relevance' | 'research_expanded';
  description?: string;
  toc: TocNode[];
  seo_score?: number;
  total_h2?: number;
  total_h3?: number;
}

interface TocProposalCardProps {
  proposal: TocProposal;
  selected: boolean;
  onSelect(): void;
  onEdit(): void;
  onExport(): void;
}
```

### ApiClient.ts (serviço)

```typescript
export class ApiClient {
  async fetchSources(
    urls: string[],
    allowInternet: boolean
  ): Promise<{ sources?: OriginalSource[]; jobId: string }> { ... }

  async getJobStatus(jobId: string): Promise<JobStatus> { ... }

  async generateIdeas(
    sourceIds: string[],
    articleTitle: string,
    additionalInfo: string,
    options: GenerateOptions
  ): Promise<GenerateResult> { ... }

  async persistSelection(
    requestId: string,
    titleId: string,
    tocProposalId: number
  ): Promise<void> { ... }

  async getRequest(requestId: string): Promise<ContentGenerationRequest> { ... }

  async listRequests(userId?: string, limit?: number): Promise<{ requests: ContentGenerationRequest[]; total: number }> { ... }
}
```

---

## 7. Dados — Tipos TypeScript & Schema MongoDB

### OriginalSource

```typescript
interface OriginalSource {
  _id: string; // uuid
  metadata: {
    source_url: string;
    canonical_url?: string;
    title?: string;
    meta_description?: string;
    language?: string;
    pub_date?: string; // ISO 8601
    categories?: string[];
    tags?: string[];
  };
  content: {
    raw_html: string;
    sanitized_html: string;
    text_extracted?: string;
  };
  toc: TocNode[];
  ia_analysis?: {
    title: string;
    summary: string;
    key_points: string[];
    sentiment?: string;
    processed_at: string;
    model_used: string;
    tokens_used: { input: number; output: number };
  };
  processing: {
    fetched_at: string;
    sanitized_at?: string;
    analyzed_at?: string;
    status: 'pending' | 'processing' | 'completed' | 'error';
    errors: string[];
  };
  metadata_audit: {
    created_at: string;
    updated_at: string;
    created_by: string;
    version: number;
  };
}
```

### ContentGenerationRequest

```typescript
interface ContentGenerationRequest {
  _id: string; // uuid
  request_metadata: {
    created_at: string;
    updated_at: string;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    user_id?: string;
    request_name?: string;
  };
  sources: {
    source_ids: string[];
    count: number;
    fetched_at?: string;
  };
  initial_context: {
    consolidation_type: string;
    context_text: string;
    summaries_count: number;
    tocs_count: number;
    generated_at?: string;
  };
  content_ideas?: {
    ideas: string[];
    count: number;
    generated_at?: string;
    model_used: string;
    tokens_used: { input: number; output: number };
  };
  selected_title?: string;
  selected_title_at?: string;
  toc_proposals: {
    proposals: TocProposal[];
    count: number;
    generated_at?: string;
    model_used: string;
    tokens_used: { input: number; output: number };
  };
  selected_toc?: {
    proposal_id: number;
    selected_at: string;
    toc: TocNode[];
  };
  configuration: {
    target_audience?: string;
    writing_style?: string;
    allow_internet_sources: boolean;
    max_idea_count?: number;
    language: string;
  };
  execution_log: {
    phases: Array<{
      phase: string;
      status: string;
      started_at: string;
      ended_at: string;
      errors: string[];
    }>;
  };
  metadata_audit: {
    created_by: string;
    version: number;
    environment: string;
  };
}
```

---

## 8. Telas do MVP (Flow)

### Tela 1: Entrada (Content Copilot)

```
┌────────────────────────────────────────────────────┐
│ Content Copilot                                    │
├───────────────────────────┬────────────────────────┤
│ Links         [+]         │      Options           │
├───────────────────────────┤                        │
│ [domain.com]    queued [x]│  ☑ Permitir acesso    │
│ [domain2.com]   queued [x]│    à internet         │
│ [new field...]            │                        │
│                           │  Provider: Mistral ▼  │
│ Informações Adicionais    │  Preset: Determin. ▼  │
├───────────────────────────┤                        │
│ (textarea, 800 chars max) │  [Advanced ▼]         │
│                           │                        │
│ Contexto para geração...  │  [Gerar ideias]       │
│                           │                        │
└───────────────────────────┴────────────────────────┘
```

### Tela 2: Modal de Confirmação

```
┌──────────────────────────────────┐
│ Confirmar Geração                │
├──────────────────────────────────┤
│ Resumo:                          │
│ • 2 URLs a processar             │
│ • Provider: Mistral              │
│ • Acesso à internet: Sim         │
│ • Tempo estimado: ~30s           │
│                                  │
│ ☑ Não mostrar novamente         │
│                                  │
│ [Cancelar]  [Confirmar & Rodar]  │
└──────────────────────────────────┘
```

### Tela 3: Progresso

```
┌────────────────────────────────────────────┐
│ Processando...                   [x Cancel] │
├────────────────────────────────────────────┤
│ Etapas: [Fetching] → [Sanitizing] →...     │
│ Progresso: ████████░░░ 65%                 │
├────────────────────────────────────────────┤
│ Status por URL:                            │
│ [domain.com]     ✓ sanitized               │
│ [domain2.com]    ⟳ analyzing               │
└────────────────────────────────────────────┘
```

### Tela 4: Resultados (Ideias + TOCs)

```
┌─────────────────┬──────────────────────────┐
│  Ideias         │  Propostas de TOC        │
├─────────────────┼──────────────────────────┤
│ ☐ Ideia 1 (8.5) │ Proposta 1 (8.5):        │
│   "Título..."   │ • Intro                  │
│   [Select]      │   - Conceitos            │
│                 │     * Definições         │
│ ☐ Ideia 2 (8.8) │ Proposta 2 (8.8):        │
│   "Título..."   │ • Intro                  │
│   [Select]      │   - Conceitos            │
│                 │                          │
│ ☐ Ideia 3 (9.0) │ [Select] [Edit] [Export]│
│   "Título..."   │                          │
│   [Select]      │                          │
└─────────────────┴──────────────────────────┘
```

---

## 9. Critérios de Aceitação Globais

### Funcionalidades (Feature Completeness)

- [ ] F1: Fetch & sanitize funcionando com 10+ URLs teste.
- [ ] F3: Consolidação de contexto gerando Markdown válido.
- [ ] F4: Geração de TOC retornando 3 propostas estruturadas.
- [ ] F5: Persistência em filesystem JSON (Mongo opcional).
- [ ] F6: Interface web com todos os componentes integrados.

### Qualidade

- [ ] Testes unitários: > 70% code coverage.
- [ ] CI/CD: testes rodam em cada PR; linter + formato code.
- [ ] Documentação: README, docstrings, exemplos.
- [ ] Sem erros ou warnings em linter (flake8, eslint).

### UX

- [ ] Validação de entrada (URLs, limites).
- [ ] Feedback de erro (toasts, modals).
- [ ] Acessibilidade mínima (WCAG 2.1 A).
- [ ] i18n pt-BR completo (sem string hardcoded).

### Performance

- [ ] Fetch < 5s/URL.
- [ ] UI carregamento < 2s.
- [ ] Progresso atualiza live (< 1s delay).

---

## 10. Roadmap Pós-MVP (Future)

- **v1.1:** Análise inicial com IA (F2).
- **v1.2:** Suporte a segundo provider (OpenAI, Groq).
- **v2.0:** MongoDB + UI para seleção de backend.
- **v2.1:** HTTP API + CI/CD avançado (rate-limiting, metricas).
- **v3.0:** Integração WordPress, editor inline de TOC, dashboard.

---

## 11. Glossário

| Termo | Definição |
|---|---|
| **TOC** | Table of Contents; estrutura hierárquica de títulos (H2, H3, etc.). |
| **IA Analysis** | Sumarização de cada fonte realizada pelo prompt `initial-content-analysis`. |
| **Context** | Consolidação de resumos e TOCs para passar ao prompt `toc_generation`. |
| **Proposal** | Uma das 3 estruturas de TOC geradas pela IA para um artigo. |
| **Provider** | Serviço de IA (ex.: Mistral, OpenAI). |
| **Preset** | Conjunto reutilizável de parâmetros (temperature, max_tokens). |
| **Minified JSON** | JSON sem whitespace extra; compacto. |
| **Sanitization** | Remoção de tags/scripts perigosos de HTML. |

---

## 12. Referências

- MongoDB Schema (documento separado)
- API Endpoints (documento separado)
- Content Copilot — Arquitetura (documentação original)

---

**Documento versão 1.0 | Pronto para implementação.**
```

Especificação de requisitos completa criada! O documento consolida:

✅ **6 funcionalidades** (F1–F6) com critérios de aceitação  
✅ **Requisitos não-funcionais** (perf, segurança, a11y, i18n)  
✅ **Stack técnico** recomendado + estrutura de diretórios  
✅ **5 endpoints de API** (fetch, status, generate, select, get)  
✅ **Componentes React** com props tipadas (TypeScript)  
✅ **Tipos de dados** (OriginalSource, ContentGenerationRequest)  
✅ **4 telas do MVP** (entrada, confirmação, progresso, resultados)  
✅ **Glossário** e roadmap pós-MVP  

Arquivo salvo em: `/home/chico/pigmeu-productnauta/pigmeu/content-copilot/docs/especificacao-requisitos.md`

Próximos passos recomendados:
1. Revisar e validar este documento com stakeholders.
2. Começar implementação pelo **Bloco 1** (Config & Security) — S, ~0.5 dia.
3. Depois **Bloco 2** (Fetch & Sanitize) — M, ~2 dias.
4. Paralelo: **Frontend** (mockups → componentes React).

Quer que eu gere também os documentos auxiliares (`mongodb-schema.md` e `api-endpoints.md` em detalhes) ou começa implementação?Especificação de requisitos completa criada! O documento consolida:

✅ **6 funcionalidades** (F1–F6) com critérios de aceitação  
✅ **Requisitos não-funcionais** (perf, segurança, a11y, i18n)  
✅ **Stack técnico** recomendado + estrutura de diretórios  
✅ **5 endpoints de API** (fetch, status, generate, select, get)  
✅ **Componentes React** com props tipadas (TypeScript)  
✅ **Tipos de dados** (OriginalSource, ContentGenerationRequest)  
✅ **4 telas do MVP** (entrada, confirmação, progresso, resultados)  
✅ **Glossário** e roadmap pós-MVP  

Arquivo salvo em: `/home/chico/pigmeu-productnauta/pigmeu/content-copilot/docs/especificacao-requisitos.md`

Próximos passos recomendados:
1. Revisar e validar este documento com stakeholders.
2. Começar implementação pelo **Bloco 1** (Config & Security) — S, ~0.5 dia.
3. Depois **Bloco 2** (Fetch & Sanitize) — M, ~2 dias.
4. Paralelo: **Frontend** (mockups → componentes React).

Quer que eu gere também os documentos auxiliares (`mongodb-schema.md` e `api-endpoints.md` em detalhes) ou começa implementação?

User: aNALISE OS WIREFRAMES DAS IMAGENS, e identifique os requisitos, itens do fluxo, funcionalidaes,



GitHub Copilot: Analisando os wireframes das imagens para extrair requisitos, fluxo e funcionalidades.

---

## Análise dos Wireframes — Content Copilot

### Wireframe 1: Tela de Progresso & Resultados (Generation Progress)

#### Componentes Visíveis

1. **Progress Timeline (Barra de Etapas)**
   - Etapas sequenciais: Get Sources HTML → Extract Metadata → Summarize Sources Context → Suggest Title Ideas → SEO Content Analysis → Suggest TOC Ideas → Enrich Content → Finalize Publish
   - Status visual: ● (completed/in-progress/pending), linhas conectando etapas
   - Atual: "Suggest Title Ideas" (etapa ativa)

2. **Page Titles of Analyzed Sources**
   - Lista de títulos extraídos das fontes analisadas (3 exemplos visíveis)
   - Cada título em "card" ou badge

3. **Suggested Titles Ideas**
   - Subtítulo: "Select one of title suggestions below, or generate new ideas"
   - Lista de ideias de títulos (5+ sugestões)
   - Uma ideia destacada em verde (selecionada)
   - Botões de ação: [REGENERATE] [ACCEPT]

4. **Step Panel (Direita)**
   - Exibe parâmetros da execução atual:
     - content_sources: 3
     - prompt: content-title-ideas
     - model: llama 3.1 70b
     - temperature: 0.4

#### Requisitos Extraídos (WF1)

| # | Requisito | Tipo | Prioridade | Status |
|---|---|---|---|---|
| **R1.1** | Timeline de etapas com status visual | UI/UX | Alta | Novo |
| **R1.2** | Listagem de títulos das fontes analisadas | Funcional | Alta | Novo |
| **R1.3** | Sugestões de ideias de títulos (multi-select) | Funcional | Alta | Novo |
| **R1.4** | Botão REGENERATE (gera novos títulos) | Funcional | Alta | Novo |
| **R1.5** | Botão ACCEPT (confirma seleção) | Funcional | Alta | Novo |
| **R1.6** | Painel lateral com parâmetros de execução | UI/Info | Média | Novo |
| **R1.7** | Indicação visual de seleção (highlight verde) | UI/UX | Média | Novo |

---

### Wireframe 2: Tela de Entrada (Initial Input)

#### Componentes Visíveis

1. **Links Section**
   - Título: "Links"
   - Botão [+] para adicionar novo link
   - Lista de campos de URL (2 exemplos):
     - `https://medium.com/@insorg.narqud/get-access-to-all-top-open-sourc...`
     - `https://astudio.google.com/u/5/api-keys?project=gen-lang-client-a329...`
   - Ícone [x] para remover cada link

2. **Additional Information**
   - Textarea para contexto adicional (texto livre)
   - Placeholder/hint: "Contexto para geração..."

3. **Options Panel (Direita)**
   - Checkboxes: "Allow internet access" (duplicado — possível bug)
   - Dropdown: "IA Provider" (ex.: Mistral)
   - Botão: [GIVE ME IDEAS] (primário, azul)
   - Annotations (sticky notes): explicações sobre campos

#### Requisitos Extraídos (WF2)

| # | Requisito | Tipo | Prioridade | Status |
|---|---|---|---|---|
| **R2.1** | Input de múltiplas URLs com add/remove | Funcional | Alta | Novo |
| **R2.2** | Validação de URLs (formato, caracteres inválidos) | Funcional | Alta | Novo |
| **R2.3** | Textarea para contexto adicional (livre) | Funcional | Média | Novo |
| **R2.4** | Toggle "Allow internet access" (único, não duplicado) | Funcional | Alta | Bug |
| **R2.5** | Dropdown para seleção de IA Provider | Funcional | Alta | Novo |
| **R2.6** | Botão "Gerar ideias" (ação primária) | Funcional | Alta | Novo |
| **R2.7** | Tooltips/hints explicativos (sticky notes) | UI/UX | Média | Novo |
| **R2.8** | Limite de URLs (10 padrão, 50 max) | Funcional | Média | Novo |

---

## Mapeamento do Fluxo de Usuário (User Journey)

```
┌─────────────────┐
│  Tela 1: Entrada│
│  (WF2)          │
└────────┬────────┘
         │ 1. Usuário insere URLs
         │ 2. Adiciona contexto adicional
         │ 3. Seleciona Provider
         │ 4. Clica "Gerar ideias"
         ▼
┌──────────────────┐
│ Modal Confirmação│
│ (não mostrado)   │
└────────┬─────────┘
         │ 5. Confirma execução
         ▼
┌──────────────────────┐
│ Tela 2: Progresso    │
│ (WF1)                │
│ Timeline + Resultados│
└────────┬─────────────┘
         │ 6. Etapas executam (fetch, analyze, generate)
         │ 7. Timeline atualiza
         │ 8. Títulos analisados aparecem
         │ 9. Sugestões de ideias aparecem
         ▼
┌─────────────────────┐
│ Usuário seleciona   │
│ título (click)      │
└────────┬────────────┘
         │ 10. Destacar em verde
         ▼
┌─────────────────────┐
│ Clica [ACCEPT] ou   │
│ [REGENERATE]        │
└────────┬────────────┘
         │ 11. Se ACCEPT → Ir para TOC
         │ 12. Se REGENERATE → Regera ideias
         ▼
┌──────────────────┐
│ Tela 3: TOC      │
│ (não mostrado)   │
│ 3 propostas      │
└──────────────────┘
```

---

## Funcionalidades Identificadas (MVP)

### **Grupo 1: Preparação & Entrada de Dados**

| ID | Funcionalidade | Descrição | Entrada | Saída | Componente | Prioridade |
|---|---|---|---|---|---|---|
| **F1** | Gerenciar Links | Adicionar/remover URLs | URL string | Array de URLs | LinksList | Alta |
| **F2** | Validar URLs | Verificar formato, accessibility | URL string | Valid/Invalid + error msg | UrlValidator | Alta |
| **F3** | Contexto Adicional | Textarea para informações extras | Free text (≤800) | String contexto | AdditionalInfo | Média |
| **F4** | Seleção Provider | Dropdown com providers disponíveis | Provider name | Selected provider | OptionsPanel | Alta |
| **F5** | Confirmação Modal | Confirmar antes de executar | User click | jobId se confirmed | ConfirmationModal | Alta |

---

### **Grupo 2: Execução & Progresso**

| ID | Funcionalidade | Descrição | Entrada | Saída | Componente | Prioridade |
|---|---|---|---|---|---|---|
| **F6** | Timeline de Etapas | Mostrar progresso do fluxo | Job status | Timeline com badges | ProgressTimeline | Alta |
| **F7** | Status por Etapa | Visual de completed/pending/error | Stage data | Colored badges | StageIndicator | Alta |
| **F8** | Fetch & Sanitize | Obter HTML das URLs | URLs | raw_html, sanitized_html | [Backend Task] | Alta |
| **F9** | Extract Metadata | Extrair título, meta desc, canonical | raw_html | metadata object | [Backend Task] | Alta |
| **F10** | Summarize Sources | Análise/resumo de cada fonte | sanitized_html | ia_analysis | [Backend Task] | Média |
| **F11** | Build Context | Consolidar resumos em contexto | [Array de resumos] | initial_context (Markdown) | [Backend Task] | Alta |

---

### **Grupo 3: Sugestão & Seleção de Títulos**

| ID | Funcionalidade | Descrição | Entrada | Saída | Componente | Prioridade |
|---|---|---|---|---|---|---|
| **F12** | Listar Títulos Analisados | Mostrar títulos das fontes | ia_analysis[].title | List display | PageTitlesSection | Média |
| **F13** | Gerar Ideias de Títulos | Chamar IA para sugerir novos títulos | initial_context + article_title | Array de ideias | [Backend Task] | Alta |
| **F14** | Exibir Sugestões | Mostrar ideias geradas | ideas[] | List com seleção | TitleIdeasList | Alta |
| **F15** | Selecionar Ideia | Click em título para selecionar | idea object | selected_idea + highlight | TitleIdeasList | Alta |
| **F16** | Regenerar Ideias | Gerar novas sugestões (retry) | Same context | New ideas[] | Button REGENERATE | Alta |

---

### **Grupo 4: Visualização & Controle**

| ID | Funcionalidade | Descrição | Entrada | Saída | Componente | Prioridade |
|---|---|---|---|---|---|---|
| **F17** | Painel de Parâmetros | Mostrar config de execução | Job metadata | Info display | StepPanel | Média |
| **F18** | Info sobre Fontes | Mostrar count de sources, URLs | Job data | Text/badges | InfoPanel | Média |
| **F19** | Prompt & Model Info | Exibir prompt usado, modelo, temperature | Runtime config | Text display | StepPanel | Média |

---

### **Grupo 5: Persistência & Próximos Passos**

| ID | Funcionalidade | Descrição | Entrada | Saída | Componente | Prioridade |
|---|---|---|---|---|---|---|
| **F20** | Salvar Seleção | Persistir título escolhido | selected_idea | request doc updated | [Backend Task] | Alta |
| **F21** | Gerar TOC | Avançar para geração de TOC (F4 original) | selected_title + context | 3 toc_proposals | [Backend Task] | Alta |

---

## Mapa de Requisitos → Componentes React

```
├─ Página Principal (ContentCopilotApp)
│  ├─ LinksList (F1, F2, R2.1, R2.2)
│  ├─ AdditionalInfo (F3, R2.3)
│  ├─ OptionsPanel (F4, F5, R2.4, R2.5, R2.6)
│  ├─ ConfirmationModal (F5, R1.5)
│  │
│  └─ ProgressView (exibida após GIVE ME IDEAS)
│     ├─ ProgressTimeline (F6, F7, R1.1, R1.2)
│     ├─ PageTitlesSection (F12, R1.2)
│     ├─ TitleIdeasList (F13, F14, F15, F16, R1.3, R1.4, R1.5, R1.7)
│     ├─ StepPanel (F17, F18, F19, R1.6)
│     └─ Buttons: [REGENERATE], [ACCEPT] (F16, F21)
```

---

## Fluxo Detalhado (step-by-step)

### **Fase 1: Entrada (WF2)**
- [ ] **Pré-condição:** Usuário acessa `/content-copilot`
- [ ] **S1.1:** Sistema exibe tela vazia com Links input + Additional Info + Options
- [ ] **S1.2:** Usuário clica [+] para adicionar link
- [ ] **S1.3:** Sistema renderiza novo campo de URL
- [ ] **S1.4:** Usuário digita URL (ex.: `https://medium.com/...`)
- [ ] **S1.5:** Sistema valida URL (formato, comprimento)
  - Se inválido: mostra erro inline
  - Se válido: aceita, mostra domain favicon
- [ ] **S1.6:** Usuário adiciona mais links (repeater S1.2-S1.5)
- [ ] **S1.7:** Usuário preenche "Additional Information" (opcional, max 800 chars)
- [ ] **S1.8:** Usuário seleciona Provider no dropdown
- [ ] **S1.9:** Usuário clica [GIVE ME IDEAS]
- [ ] **S1.10:** Sistema valida entrada:
  - Min 1 URL válida OU texto em Additional Info
  - Se inválido: toast error
  - Se válido: → Fase 2

### **Fase 2: Confirmação (Modal)**
- [ ] **S2.1:** Sistema exibe modal com resumo:
  - Número de URLs
  - Provider selecionado
  - Tempo estimado
  - Aviso de privacidade (se internet access ativo)
- [ ] **S2.2:** Usuário clica [CONFIRM & RUN] ou [CANCEL]
  - Se CANCEL: voltar para Fase 1
  - Se CONFIRM: → Fase 3

### **Fase 3: Execução (WF1 — Progress)**
- [ ] **S3.1:** Sistema inicia job, retorna `jobId`
- [ ] **S3.2:** Sistema renderiza tela de Progresso com Timeline vazia (todos pending)
- [ ] **S3.3:** Sistema começa etapas sequenciais:
  1. **Get Sources HTML** (fetch_html)
     - Status: ⟳ processing
     - Ao completar: ✓ completed, verde
  2. **Extract Metadata** (metadata_extractor)
     - Deps: Get Sources HTML completo
     - Status: pending → processing → completed
  3. **Summarize Sources Context** (analyze_source — F2, v1.1 opcional)
     - Deps: Extract Metadata completo
  4. **Suggest Title Ideas** (generate_ideas)
     - Deps: Summarize Sources Context completo (ou skip se F2 não implementado)
     - Ao completar: TitleIdeasList é renderizada
  5. Etapas subsequentes: SEO Content Analysis, Suggest TOC Ideas, Enrich Content, Finalize Publish (future scope)

- [ ] **S3.4:** Poll job status a cada ~1s
  - Atualizar timeline
  - Atualizar badges per stage
  - Se erro: mostrar error badge + retry option

- [ ] **S3.5:** Ao completar etapa **Suggest Title Ideas**:
  - Renderizar "Page Titles of Analyzed Sources" (títulos extraídos das fontes)
  - Renderizar "Suggested Titles Ideas" (ideias geradas pela IA)
  - Exibir StepPanel com parâmetros

### **Fase 4: Seleção de Título (WF1 — Resultados)**
- [ ] **S4.1:** Usuário visualiza lista de títulos sugeridos
- [ ] **S4.2:** Usuário clica em um título
  - Sistema destaca em verde
  - Sistema atualiza preview (se houver)
- [ ] **S4.3:** Usuário clica [REGENERATE]
  - Sistema volta etapa **Suggest Title Ideas** (re-roda)
  - Novas ideias são geradas
  - Timeline não retrocede; apenas relança etapa
- [ ] **S4.4:** Usuário clica [ACCEPT] (com título selecionado)
  - Sistema salva `selected_title` no request doc
  - Sistema transiciona para Fase 5 (TOC Generation)

### **Fase 5: Geração de TOC (Futuro, após ACCEPT)**
- [ ] **S5.1:** Sistema avança timeline para "Suggest TOC Ideas"
- [ ] **S5.2:** Sistema chama prompt `toc_generation` com `initial_context` + `selected_title`
- [ ] **S5.3:** Sistema recebe 3 propostas de TOC
- [ ] **S5.4:** Tela nova (não mostrada em wireframe) exibe 3 cards de propostas
- [ ] **S5.5:** Usuário seleciona uma proposta → [ACCEPT TOC]
- [ ] **S5.6:** Sistema salva `selected_toc` e completa fluxo → "Finalize Publish" (future)

---

## Tabela de Mapeamento: Requisitos Especificação → Wireframes

| Spec. Requisito | Wireframe | Status | Observação |
|---|---|---|---|
| F1 (Fetch & Sanitize) | WF2, S1.2-S1.5 | ✓ Mapeado | Entrada de URLs |
| F3 (Consolidação Contexto) | WF2, S1.7 | ✓ Mapeado | "Additional Information" |
| F4 (Geração TOC) | WF1, S5 (future) | ✓ Mapeado | "Suggest TOC Ideas" etapa |
| F6 (Interface Web) | WF1, WF2 | ✓ Mapeado | Ambos wireframes |
| Seleção Provider | WF2, S1.8 | ✓ Mapeado | Dropdown |
| Validação entrada | S1.5, S1.10 | ✓ Implícito | Não visível em wireframe |
| Modal confirmação | S2.1-S2.2 | ⚠️ Faltando | Não mostrado em wireframe |
| Progress real-time | WF1, S3.4 | ✓ Mapeado | Timeline com badges |
| Retry/Regenerate | WF1, S4.3 | ✓ Mapeado | Button [REGENERATE] |
| Source preview | WF1 | ⚠️ Parcial | "Page Titles..." apenas |

---

## Issues & Recomendações

| # | Issue | Tipo | Solução Proposta | Prioridade |
|---|---|---|---|---|
| **I1** | Checkbox "Allow internet access" duplicado (WF2) | Bug UX | Manter apenas 1; adicionar tooltip | Alta |
| **I2** | Modal confirmação não mostrado em wireframe | Faltante | Criar WF3 (Modal) | Média |
| **I3** | Fase de análise inicial (F2) não aparece em WF1 | Escopo | Confirmar se "Summarize Sources" = análise IA | Média |
| **I4** | Timeline mostra 8 etapas; spec MVP foca em 5 | Escopo | Alinhar: quais etapas no MVP vs. v1.1? | Alta |
| **I5** | Sem indicação de carregamento durante Fetch | UX | Adicionar spinner/progress bar per link | Média |
| **I6** | TOC generation não visível em WF1 (começa após ACCEPT) | Fluxo | Confirmar transição: WF1 → WF3 (TOC proposals) | Alta |
| **I7** | Sem tratamento de erro visível em WF1 | UX | Adicionar error states (ícone ⚠️, mensagem) | Média |

---

## Resumo: Requisitos Novos Identificados nos Wireframes

### **Adições à Especificação Original**

1. **Timeline de Etapas** (WF1)
   - Visual tipo Gantt com badges de status
   - Etapas sequenciais: 8 no total (MVP: 5 críticas)
   - Status: pending (∘), processing (⟳), completed (✓), error (✗)

2. **Page Titles of Analyzed Sources** (WF1)
   - Listagem de títulos H1 extraídos das fontes
   - Auxilia usuário a contextualizar análise

3. **Suggested Titles Ideas com Regenerate** (WF1)
   - Multi-select (pode selecionar + de um título)
   - Botão [REGENERATE] para retry
   - Botão [ACCEPT] para confirmação

4. **Step Panel com Parâmetros** (WF1)
   - Exibe: # sources, prompt usado, model, temperatura
   - Auxilia debugging/auditoría

5. **Validação de URL com Favicon** (WF2)
   - Mostrar domain + favicon ao validar
   - Indicação visual de sucesso

6. **Limite de URLs com Aviso** (WF2)
   - Máximo 10 padrão (WF não mostra, mas spec menciona)
   - Aviso ao atingir limite

7. **Contexto Adicional com Hint** (WF2)
   - Textarea com placeholder explicativo
   - Max length 800 caracteres

---

**Próximos passos:**
1. Confirmar alinhamento das 8 etapas (WF1) com 5-6 etapas do MVP vs. future.
2. Desenhar WF3 (Modal confirmação) que falta.
3. Desenhar WF4 (TOC Proposals) — após ACCEPT do título.
4. Validar fluxo de erro e retry em cada etapa.

Quer que eu crie os wireframes faltantes (Modal + TOC Proposals) ou detalhe as especificações das 8 etapas?

User: Sim, crie os wireframes.

GitHub Copilot: Vou criar os wireframes faltantes (WF3 - Modal Confirmação e WF4 - TOC Proposals) com descrições detalhadas, layouts e anotações.

---

## Wireframe 3: Modal de Confirmação (Confirm & Run)

### Layout ASCII

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│   ┌───────────────────────────────────────────────────┐    │
│   │                                                   │    │
│   │  🔔 Confirmar Geração de Ideias                  │ [X] │
│   │                                                   │    │
│   ├───────────────────────────────────────────────────┤    │
│   │                                                   │    │
│   │  Resumo da Execução:                             │    │
│   │  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │    │
│   │                                                   │    │
│   │  📌 Fontes a processar:              2 URLs      │    │
│   │     • https://medium.com/@insorg...              │    │
│   │     • https://astudio.google.com...              │    │
│   │                                                   │    │
│   │  🤖 Provedor de IA:                 Mistral      │    │
│   │                                                   │    │
│   │  ⏱️  Tempo estimado:                 ~30-45s      │    │
│   │                                                   │    │
│   │  🌐 Acesso à internet:              ✓ Ativo      │    │
│   │                                                   │    │
│   │  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │    │
│   │                                                   │    │
│   │  ⚠️  Aviso de Privacidade:                        │    │
│   │  "Os conteúdos das URLs serão fetched e         │    │
│   │   processados pela IA. Nenhum conteúdo será     │    │
│   │   mantido permanentemente."                      │    │
│   │                                                   │    │
│   │  ☑ Não mostrar este aviso novamente             │    │
│   │                                                   │    │
│   │  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │    │
│   │                                                   │    │
│   │           [Cancelar]    [Confirmar & Rodar]      │    │
│   │                                                   │    │
│   └───────────────────────────────────────────────────┘    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Componentes (WF3)

| # | Componente | Tipo | Descrição | Props |
|---|---|---|---|---|
| **C3.1** | Header com Ícone | UI | Ícone 🔔 + título "Confirmar Geração de Ideias" | title, icon |
| **C3.2** | Close Button | Ação | [X] para fechar modal | onClick |
| **C3.3** | Summary Section | Info | Exibe resumo da execução com ícones | sources, provider, time, internet |
| **C3.4** | Sources List | Info | Bullet list das URLs a processar | urls[] |
| **C3.5** | Provider Display | Info | Mostra provider selecionado | provider_name |
| **C3.6** | Estimated Time | Info | Tempo estimado (cálculo baseado em # URLs) | estimate_seconds |
| **C3.7** | Internet Access Badge | Info | Mostra se acesso internet está ativo | allow_internet |
| **C3.8** | Privacy Warning | Info | Aviso sobre dados (LGPD, privacidade) | warning_text |
| **C3.9** | Don't Show Again | Checkbox | "☑ Não mostrar este aviso novamente" | onToggleDontShow |
| **C3.10** | Cancel Button | Ação | Voltar para tela anterior | onClick |
| **C3.11** | Confirm Button | Ação | Confirmar e iniciar execução | onClick |

### Requisitos (WF3)

| # | Requisito | Descrição | Prioridade |
|---|---|---|---|
| **R3.1** | Summary visual | Exibir resumo clara de URLs, provider, tempo estimado | Alta |
| **R3.2** | Privacy warning | Aviso sobre LGPD e uso de dados (obrigatório BR) | Alta |
| **R3.3** | Don't show again | Checkbox para suprimir modal em próximas execuções | Média |
| **R3.4** | Cancel option | Opção de cancelamento sem perder dados | Alta |
| **R3.5** | Time estimate | Cálculo dinâmico: ~15s por URL + overhead | Média |
| **R3.6** | Acessibilidade | Modal focável, keyboard navigable (ESC = close) | Média |

### Fluxo de Interação (WF3)

```
Usuário clica [GIVE ME IDEAS] (WF2)
         ↓
Sistema valida entrada
         ↓
Sistema exibe WF3 Modal
         ↓
┌─────────────────────────────────┐
│ Usuário escolhe:                │
├─────────────────────────────────┤
│ A) [Cancelar]                   │
│    → Volta para WF2             │
│    → Mantém dados preenchidos   │
│                                 │
│ B) Clica [☑ Don't show again]   │
│    → Salva preferência em localStorage
│                                 │
│ C) [Confirmar & Rodar]          │
│    → Fecha modal                │
│    → Inicia job de fetch        │
│    → Renderiza WF1 Progress     │
│    → Chama POST /api/v1/sources/fetch
└─────────────────────────────────┘
```

### Dados de Entrada para Modal

```typescript
interface ConfirmationModalData {
  sources_count: number;
  urls: string[];
  provider: string;
  allow_internet: boolean;
  estimated_time_seconds: number;
  additional_info?: string;
}

// Exemplo de cálculo de tempo:
// time = (sources_count * 15) + (sanitize_overhead * 5) + (api_call_overhead)
// = (2 * 15) + 10 = 40 segundos
```

---

## Wireframe 4: TOC Proposals (Após ACCEPT do Título)

### Layout ASCII

```
┌─────────────────────────────────────────────────────────────────┐
│ Content Copilot  ← [Back]                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Generation Progress (Timeline com todas etapas)                │
│  ● [Get HTML] → ● [Extract] → ● [Summarize] → ● [Ideas]       │
│  → ● [SEO Analysis] → ⟳ [Suggest TOC] → ∘ [Enrich] → ∘ [Pub]  │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  📋 Título Selecionado:                                          │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ "Writing User Stories That Actually Work in Agile      │   │
│  │  Projects"                                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  Estruturas de TOC Propostas (Escolha uma):                    │
│                                                                 │
│  ┌─ Proposta 1: Frequência Otimizada ─────────────────────┐   │
│  │ 📊 Score SEO: 8.5/10                                    │   │
│  │ Total de H2: 5 | Total de H3: 12                       │   │
│  │                                                         │   │
│  │ 📌 Descrição:                                           │   │
│  │ "Baseada em estruturas mais recorrentes dos artigos    │   │
│  │  analisados. Otimizada para SEO e palavras-chave."     │   │
│  │                                                         │   │
│  │ 📑 Estrutura:                                           │   │
│  │ • Introdução                                            │   │
│  │   └─ O que são User Stories?                           │   │
│  │   └─ Por que são importantes?                          │   │
│  │ • Conceitos Fundamentais                                │   │
│  │   └─ Definições Básicas                                │   │
│  │     └─ Papel do usuário                                │   │
│  │     └─ Funcionalidades esperadas                       │   │
│  │   └─ Estrutura canônica                                │   │
│  │ • [... (truncado, clique para expandir)]               │   │
│  │                                                         │   │
│  │ [Preview ↗]  [Editar]  [Selecionar esta]               │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─ Proposta 2: Relevância Aprimorada ───────────────────┐    │
│  │ 📊 Score SEO: 8.8/10                                    │    │
│  │ Total de H2: 6 | Total de H3: 15                       │    │
│  │                                                         │    │
│  │ 📌 Descrição:                                           │    │
│  │ "Baseada em frequência + novos subtítulos relevantes    │    │
│  │  identificados nas análises. Melhor cobertura."        │    │
│  │                                                         │    │
│  │ [Preview ↗]  [Editar]  [Selecionar esta]               │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                 │
│  ┌─ Proposta 3: Pesquisa Ampliada ──────────────────────┐     │
│  │ 📊 Score SEO: 9.0/10  ⭐ Recomendado                   │     │
│  │ Total de H2: 7 | Total de H3: 18                       │     │
│  │                                                         │     │
│  │ 📌 Descrição:                                           │     │
│  │ "Ampliada com pesquisa externa e novos títulos         │     │
│  │  otimizados para SEO. Cobertura completa."             │     │
│  │                                                         │     │
│  │ [Preview ↗]  [Editar]  [Selecionar esta]               │     │
│  └─────────────────────────────────────────────────────────┘     │
│                                                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━   │
│                                                                 │
│  Right Panel: Step Info                                        │
│  ┌─────────────────────────┐                                   │
│  │ 📝 Etapa Atual:         │                                   │
│  │ Suggest TOC Ideas       │                                   │
│  │                         │                                   │
│  │ 🤖 Modelo:              │                                   │
│  │ Mistral Large           │                                   │
│  │                         │                                   │
│  │ 🔥 Temperatura:         │                                   │
│  │ 0.4                     │                                   │
│  │                         │                                   │
│  │ 📊 Tokens usados:       │                                   │
│  │ In: 3200 | Out: 1800    │                                   │
│  │                         │                                   │
│  │ ⏱️  Gerado em:          │                                   │
│  │ 2026-02-06 11:10:00     │                                   │
│  └─────────────────────────┘                                   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Componentes (WF4)

| # | Componente | Tipo | Descrição | Props |
|---|---|---|---|---|
| **C4.1** | Header + Back Button | Nav | Breadcrumb + título + [← Voltar] | onBack |
| **C4.2** | Progress Timeline (expandida) | UI | 8 etapas, atual highlighted | stages[], currentIndex |
| **C4.3** | Selected Title Display | Info | Card com título escolhido pelo usuário | selected_title |
| **C4.4** | Proposal Cards (x3) | UI | 3 cards com propostas de TOC | proposals[] |
| **C4.5** | Score Badge | Info | "📊 Score SEO: 8.5/10" + opcional ⭐ | score |
| **C4.6** | Metrics Display | Info | "Total de H2: 5 | Total de H3: 12" | total_h2, total_h3 |
| **C4.7** | Description Text | Info | Explicação da proposta (1-2 linhas) | description |
| **C4.8** | TOC Tree (expandível) | UI | Hierarquia H2/H3, truncado com "…" | toc[], maxHeight |
| **C4.9** | Action Buttons | Ação | [Preview ↗] [Editar] [Selecionar esta] | onPreview, onEdit, onSelect |
| **C4.10** | Step Panel (direita) | Info | Etapa atual, modelo, temperatura, tokens | metadata |
| **C4.11** | Preview Modal | Modal | Exibe TOC completa em modal (se clicado Preview) | toc[], title |
| **C4.12** | TOC Editor Modal | Modal | Inline editor para editar H2/H3 (se clicado Editar) | toc[], onChange |

### Requisitos (WF4)

| # | Requisito | Descrição | Prioridade |
|---|---|---|---|
| **R4.1** | Exibição de 3 propostas | Card layout para cada proposta | Alta |
| **R4.2** | Score SEO | Métrica visual (number + star opcional) | Alta |
| **R4.3** | Métrica de H2/H3 | Contagem visual total | Alta |
| **R4.4** | Descrição legível | Explicação breve de cada proposta | Alta |
| **R4.5** | TOC hierárquica | Visualização em árvore (H1 < H2 < H3) | Alta |
| **R4.6** | Truncation dinâmico | "…" com "clique para expandir" se > 5 níveis | Média |
| **R4.7** | Preview em modal | Expandir TOC completa sem sair | Média |
| **R4.8** | Edição inline | Permitir usuário editar títulos (opcional MVP) | Média |
| **R4.9** | Seleção clara | Uma proposta pode ser selecionada/destacada | Alta |
| **R4.10** | Recomendação visual | Proposta 3 marcada com ⭐ (maior score) | Baixa |
| **R4.11** | Timeline completa | Mostrar todas 8 etapas com status | Alta |
| **R4.12** | Back navigation | Botão [← Voltar] para retornar a seleção de título | Média |

### Fluxo de Interação (WF4)

```
Usuário clica [ACCEPT] em WF1 (seleção de título)
         ↓
Sistema salva selected_title = "Writing User Stories..."
         ↓
Sistema renderiza WF4 (TOC Proposals)
         ↓
Timeline avança: "Suggest Title Ideas" → "SEO Content Analysis" → "Suggest TOC Ideas" (⟳)
         ↓
Sistema exibe 3 propostas de TOC (cards)
         ↓
┌─────────────────────────────────────────────────────┐
│ Usuário interage:                                   │
├─────────────────────────────────────────────────────┤
│ A) Clica [Preview ↗] em proposta                    │
│    → Abre modal com TOC completa                    │
│    → Usuário visualiza estrutura expandida          │
│    → [Fechar] modal                                 │
│                                                     │
│ B) Clica [Editar] em proposta (MVP stretch)        │
│    → Abre editor inline/modal                       │
│    → Usuário edita H2/H3                            │
│    → [Salvar edições] → Atualiza card              │
│                                                     │
│ C) Clica [Selecionar esta] em uma proposta         │
│    → Proposta é highlighted/selecionada             │
│    → Aparece botão [CONFIRMAR SELEÇÃO] ou          │
│    → Auto-seleciona + transição para próxima etapa  │
│                                                     │
│ D) Clica [← Voltar]                                 │
│    → Volta para WF1 (seleção de título)             │
│    → Mantém dados de TOC_proposals em cache         │
└─────────────────────────────────────────────────────┘
         ↓
[Se C] Sistema persiste selected_toc = {proposal_id: 2, toc: [...]}
         ↓
Timeline avança para próximas etapas: "Enrich Content" → "Finalize Publish"
         ↓
Sistema salva content_generation_request com todos os dados
         ↓
Tela final: "✓ Fluxo Completado" + botões [Export JSON] [Save Draft] [Publish]
```

### Dados de Saída (WF4)

```typescript
interface TocProposal {
  proposal_id: number;  // 1, 2, 3
  type: 'frequency_optimized' | 'enhanced_relevance' | 'research_expanded';
  description: string;
  toc: TocNode[];
  metrics: {
    total_h2: number;
    total_h3: number;
    seo_score: number;
  };
  model_used: string;
  tokens_used: {
    input: number;
    output: number;
  };
  generated_at: string;  // ISO 8601
}

interface TocNode {
  id: string;  // slug único
  level: number;  // 1, 2, 3
  text: string;  // "Introdução", "Conceitos Fundamentais", etc.
  children?: TocNode[];
}

// Exemplo de Proposta 1:
{
  proposal_id: 1,
  type: 'frequency_optimized',
  description: 'Baseada em estruturas mais recorrentes dos artigos analisados...',
  toc: [
    {
      id: 'intro',
      level: 1,
      text: 'Introdução',
      children: [
        { id: 'what-are-us', level: 2, text: 'O que são User Stories?' },
        { id: 'why-important', level: 2, text: 'Por que são importantes?' }
      ]
    },
    {
      id: 'fundamentals',
      level: 1,
      text: 'Conceitos Fundamentais',
      children: [
        {
          id: 'definitions',
          level: 2,
          text: 'Definições Básicas',
          children: [
            { id: 'user-role', level: 3, text: 'Papel do usuário' },
            { id: 'expected-features', level: 3, text: 'Funcionalidades esperadas' }
          ]
        }
      ]
    }
  ],
  metrics: { total_h2: 5, total_h3: 12, seo_score: 8.5 },
  // ...
}
```

---

## Componente React: TocProposalCard.tsx (exemplo completo)

```typescript
interface TocProposalCardProps {
  proposal: TocProposal;
  isSelected: boolean;
  isRecommended?: boolean;  // Score > 8.8 → mostra ⭐
  onPreview(): void;
  onEdit(): void;
  onSelect(): void;
}

const TocProposalCard: React.FC<TocProposalCardProps> = ({
  proposal,
  isSelected,
  isRecommended,
  onPreview,
  onEdit,
  onSelect
}) => {
  const [isExpanded, setIsExpanded] = React.useState(false);
  const maxDisplayItems = 5;
  
  const truncatedToc = proposal.toc.slice(0, maxDisplayItems);
  const hasMoreItems = proposal.toc.length > maxDisplayItems;

  return (
    <div className={`toc-proposal-card ${isSelected ? 'selected' : ''}`}>
      {/* Header */}
      <div className="card-header">
        <h3>
          Proposta {proposal.proposal_id}: {proposal.type}
          {isRecommended && <span className="star">⭐ Recomendado</span>}
        </h3>
        <button className="close-btn" onClick={() => null}>×</button>
      </div>

      {/* Score & Metrics */}
      <div className="metrics-row">
        <span className="score">📊 Score SEO: {proposal.metrics.seo_score}/10</span>
        <span className="separator">|</span>
        <span className="h-count">Total de H2: {proposal.metrics.total_h2}</span>
        <span className="separator">|</span>
        <span className="h-count">Total de H3: {proposal.metrics.total_h3}</span>
      </div>

      {/* Description */}
      <div className="description">
        <p>📌 Descrição:</p>
        <p className="text">{proposal.description}</p>
      </div>

      {/* TOC Preview */}
      <div className="toc-preview">
        <p>📑 Estrutura:</p>
        <ul className="toc-tree">
          {truncatedToc.map((node) => (
            <li key={node.id} className={`level-${node.level}`}>
              • {node.text}
              {node.children && (
                <ul>
                  {node.children.slice(0, 2).map((child) => (
                    <li key={child.id} className={`level-${child.level}`}>
                      └─ {child.text}
                    </li>
                  ))}
                  {node.children.length > 2 && (
                    <li className="truncated">└─ […]</li>
                  )}
                </ul>
              )}
            </li>
          ))}
          {hasMoreItems && (
            <li className="more-items" onClick={() => setIsExpanded(true)}>
              • […] Clique para expandir
            </li>
          )}
        </ul>
      </div>

      {/* Action Buttons */}
      <div className="actions">
        <button className="btn-secondary" onClick={onPreview}>
          Preview ↗
        </button>
        <button className="btn-secondary" onClick={onEdit}>
          Editar
        </button>
        <button 
          className={`btn-primary ${isSelected ? 'selected' : ''}`}
          onClick={onSelect}
        >
          {isSelected ? '✓ Selecionada' : 'Selecionar esta'}
        </button>
      </div>
    </div>
  );
};
```

---

## Tela Final: Resumo & Exportação (WF5 — Futuro)

```
┌──────────────────────────────────────────────────────────┐
│ Content Copilot — Fluxo Completado ✓                     │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  ✓ Título Selecionado:                                   │
│  "Writing User Stories That Actually Work in Agile      │
│   Projects"                                              │
│                                                          │
│  ✓ Estrutura de TOC Selecionada:                         │
│  Proposta 2 (Relevância Aprimorada)                      │
│  • Introdução                                            │
│  • Conceitos Fundamentais                                │
│  • [... (resumida)]                                      │
│                                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                          │
│  📊 Resumo de Execução:                                  │
│  • Fontes processadas: 2                                 │
│  • Tempo total: 52 segundos                              │
│  • Modelo: Mistral Large                                 │
│  • Total de tokens: 5000 (in) + 3000 (out)              │
│  • Request ID: req-abc123def456                          │
│                                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                          │
│  Próximas ações:                                         │
│                                                          │
│  [Export JSON] [Save as Draft] [Publish to WordPress]   │
│  [Start New Request] [Go Back]                           │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

---

## Mapeamento: 8 Etapas da Timeline (Especificação Completa)

| # | Etapa | Sigla | Descrição | Dependências | Status MVP |
|---|---|---|---|---|---|
| 1 | Get Sources HTML | FETCH | Fetch HTML bruto de todas as URLs | — | ✓ Crítica |
| 2 | Extract Metadata | META | Extrair title, meta desc, canonical, pub_date | FETCH | ✓ Crítica |
| 3 | Summarize Sources Context | ANALYZE | Análise IA inicial de cada fonte (F2) | META | ⚠️ Opcional v1.1 |
| 4 | Suggest Title Ideas | IDEAS | Gerar ideias de títulos para artigo | ANALYZE (ou META) | ✓ Crítica |
| 5 | SEO Content Analysis | SEO | Análise SEO das ideias + keyword scoring | IDEAS | ⚠️ Optional future |
| 6 | Suggest TOC Ideas | TOC | Gerar 3 propostas de estrutura | SEO (ou IDEAS) | ✓ Crítica |
| 7 | Enrich Content | ENRICH | Adicionar insights extra, referências | TOC | ⚠️ Optional v2.0 |
| 8 | Finalize Publish | PUBLISH | Preparar para publicação / export | ENRICH | ⚠️ Optional v2.0 |

### MVP (Mínimo Viável)

Etapas críticas: **1, 2, 4, 6**  
Etapas opcionais: **3, 5, 7, 8** (podem ser implementadas após MVP ou v1.1)

### Timeline simplificada para MVP (5 etapas essenciais)

```
● [FETCH] → ● [META] → ⚠️ [ANALYZE*] → ● [IDEAS] → ● [TOC]
  *opcional, pode ser skipped

Fluxo sem ANALYZE (rápido MVP):
● [FETCH] → ● [META] → ● [IDEAS] → ● [TOC]
```

---

## Sumário dos 3 Wireframes Criados

| WF | Nome | Status | Uso | Componentes |
|---|---|---|---|---|
| **WF3** | Modal Confirmação | ✓ Criado | Antes de executar | ConfirmationModal, PrivacyWarning |
| **WF4** | TOC Proposals | ✓ Criado | Após seleção de título | TocProposalCard, Preview Modal, Editor |
| **WF5** | Resumo Final (futuro) | ✓ Sketched | Após seleção de TOC | Summary, Export Buttons |

---

## Documento Consolidado para Desenvolvedores

Todos os 5 wireframes agora mapeados:

1. **WF1**: Progress & Resultados (seleção de títulos) ✓ Fornecido
2. **WF2**: Entrada & Opções ✓ Fornecido
3. **WF3**: Modal Confirmação ✓ Criado
4. **WF4**: TOC Proposals ✓ Criado
5. **WF5**: Resumo & Exportação ✓ Sketched

Pronto para começar a implementação dos componentes React? Ou quer que eu detalhe mais algum wireframe / componente?

User: Primeiro vamos revisar o escopo do MVP, em seguida definir a arquitetura e modelo de dados (identificar callections necessárias no mongoDB. definir estrutura dos documentos), e então criar documentação detalhada de requisitos e arquitetura. 

GitHub Copilot: Vou fazer uma revisão completa do escopo do MVP, definir a arquitetura e criar documentação detalhada consolidando tudo. Estrutura: (1) Escopo MVP revisado, (2) Arquitetura técnica, (3) Modelo de dados MongoDB, (4) Documentação consolidada.

---

## 1. ESCOPO MVP REVISADO

### Etapas Críticas (5 no mínimo)

```
MVP Core Workflow:
┌─────────────────────────────────────────────────────────┐
│ FASE 1: Fetch & Sanitize                               │
├─────────────────────────────────────────────────────────┤
│ 1. Get Sources HTML    [FETCH HTML bruto das URLs]      │
│ 2. Extract Metadata    [Extrair title, meta, canonical] │
└─────────────────────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────────────────────┐
│ FASE 2: Consolidar Contexto                             │
├─────────────────────────────────────────────────────────┤
│ 3. Build Context       [Concatenar metadata + tocs]     │
└─────────────────────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────────────────────┐
│ FASE 3: Gerar Ideias                                    │
├─────────────────────────────────────────────────────────┤
│ 4. Suggest Titles      [IA gera ideias de títulos]     │
└─────────────────────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────────────────────┐
│ FASE 4: Gerar Estrutura                                 │
├─────────────────────────────────────────────────────────┤
│ 5. Suggest TOC Ideas   [IA gera 3 propostas de TOC]    │
└─────────────────────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────────────────────┐
│ FASE 5: Persistência                                    │
├─────────────────────────────────────────────────────────┤
│ 6. Save Request        [Persiste em MongoDB]            │
└─────────────────────────────────────────────────────────┘
```

### In-Scope (MVP)

| # | Item | Status | Razão |
|---|---|---|---|
| **F1** | Fetch múltiplas URLs | ✓ In | Núcleo do fluxo |
| **F2** | Sanitizar & extrair metadata | ✓ In | Preparação dados |
| **F3** | Extrair TOC (H2/H3) | ✓ In | Contexto para IA |
| **F4** | Consolidar contexto | ✓ In | Input para prompts |
| **F5** | Gerar ideias de títulos | ✓ In | Primeira saída IA |
| **F6** | Gerar 3 propostas de TOC | ✓ In | Segunda saída IA |
| **F7** | Persistência (MongoDB) | ✓ In | Salvaguarda dados |
| **F8** | Interface Web (React) | ✓ In | UX para usuário |
| **F9** | Validação & Retry | ✓ In | Robustez |
| **F10** | Testes & CI/CD | ✓ In | Qualidade |

### Out-of-Scope (Futuro)

| # | Item | Status | Próxima Versão |
|---|---|---|---|
| **F11** | Análise inicial com IA (summarize) | ✗ Out | v1.1 |
| **F12** | SEO Content Analysis | ✗ Out | v2.0 |
| **F13** | Enrich Content | ✗ Out | v2.0 |
| **F14** | Publicação WordPress | ✗ Out | v2.1 |
| **F15** | Segundo provider (OpenAI) | ✗ Out | v1.2 |
| **F16** | Rate-limiting / cotas | ✗ Out | v2.0 |
| **F17** | Dashboard de histórico | ✗ Out | v2.0 |
| **F18** | Editor avançado de TOC inline | ✗ Out | v1.2 |

### Entrega MVP

- [ ] Backend Python (FastAPI) com endpoints de API
- [ ] Frontend React (TypeScript) com 5 telas (entrada, progresso, seleção, propostas, resumo)
- [ ] MongoDB com 2 collections (`original_sources`, `content_generation_requests`)
- [ ] Testes unitários (70%+ coverage)
- [ ] CI/CD básico (GitHub Actions)
- [ ] Documentação: README, Swagger, arquitetura

### Prazo Estimado

**8-10 dias de trabalho** (1-2 desenvolvedores full-time):
- Bloco 1-2 (config, fetch): 2.5 dias
- Bloco 3-4 (loader, adapters): 2.5 dias
- Bloco 5-6 (orquestrador, persistência): 2 dias
- Frontend (componentes + integração): 2.5 dias
- Testes & CI/CD: 1.5 dias

---

## 2. ARQUITETURA TÉCNICA

### 2.1 Diagrama de Componentes (Alto Nível)

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (React/TS)                       │
├─────────────────────────────────────────────────────────────┤
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────┐   │
│ │ LinksList│ │ Progress │ │ TitleList│ │ TocProposals │   │
│ └──────────┘ └──────────┘ └──────────┘ └──────────────┘   │
│                   ↓ HTTP (REST API)                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              BACKEND (FastAPI / Python)                      │
├──────────────────────┬──────────────────┬──────────────────┤
│   API Layer          │  Business Logic  │  Infrastructure  │
├──────────────────────┼──────────────────┼──────────────────┤
│ • POST /fetch        │ • Prompt Loader  │ • MongoDB driver │
│ • GET /status        │ • IA Adapters    │ • Storage layer  │
│ • POST /generate     │ • Validators     │ • Config mgmt    │
│ • POST /select       │ • Orchestrators  │ • Logging        │
│ • GET /request       │ • Retry handler  │ • Error handling │
└──────────────────────┴──────────────────┴──────────────────┘
           ↓ gRPC / Internal                    ↓ (ext APIs)
┌──────────────────────────────────────────────────────────────┐
│         SERVICES & EXTERNAL                                  │
├──────────────────────┬──────────────────┬───────────────────┤
│ HTML Fetcher         │ IA Providers     │ Data Storage      │
│ • requests lib       │ • Mistral SDK    │ • MongoDB         │
│ • BeautifulSoup      │ • Groq SDK       │ • Filesystem      │
│ • Bleach sanitizer   │ • Gemini SDK     │                   │
└──────────────────────┴──────────────────┴───────────────────┘
```

### 2.2 Fluxo de Dados (End-to-End)

```
User Input (WF2)
│ URLs, Additional Info, Provider
├─→ [Validate Inputs]
│   • URL format, length, limits
│   • Storage: creates content_generation_request (pending)
│
├─→ [PHASE 1: Fetch & Extract]
│   ├─→ fetch_html(url) → raw_html
│   ├─→ sanitize_html(raw) → sanitized_html
│   ├─→ extract_metadata(html) → {title, meta_desc, canonical, pub_date}
│   ├─→ extract_toc(html) → {level, text, id}[]
│   └─→ Storage: saves original_sources documents
│
├─→ [PHASE 2: Build Context]
│   ├─→ Consolidate summaries + tocs
│   ├─→ Build initial_context (Markdown)
│   └─→ Storage: updates content_generation_request.initial_context
│
├─→ [PHASE 3: Generate Ideas]
│   ├─→ Load prompt: "content-title-ideas"
│   ├─→ Render templates with initial_context + article_title
│   ├─→ Call IA Provider (Mistral)
│   ├─→ Validate JSON output + schema
│   ├─→ Retry if validation fails
│   └─→ Storage: saves title_ideas to content_generation_request
│
├─→ [PHASE 4: Select Title]
│   ├─→ User selects from ideas
│   └─→ Storage: updates content_generation_request.selected_title
│
├─→ [PHASE 5: Generate TOC Proposals]
│   ├─→ Load prompt: "content-toc-generation"
│   ├─→ Render templates with context + selected_title
│   ├─→ Call IA Provider (Mistral)
│   ├─→ Validate JSON + 3 proposals
│   ├─→ Retry if validation fails
│   └─→ Storage: saves toc_proposals to content_generation_request
│
├─→ [PHASE 6: Select TOC]
│   ├─→ User selects one proposal
│   └─→ Storage: updates content_generation_request.selected_toc
│
└─→ [Export/Finalize]
    ├─→ Export JSON
    ├─→ Save as draft
    └─→ (Optional: Publish to WordPress)
```

### 2.3 Stack Técnico Final

**Backend:**
- Python 3.10+
- FastAPI (REST API)
- Pydantic (validation)
- Motor (async MongoDB driver)
- Mistral SDK + Groq SDK (extensível)
- BeautifulSoup4 + Bleach (HTML)
- Jinja2 (templates)
- Pytest (tests)
- Black + Flake8 (lint)

**Frontend:**
- React 18+ (TypeScript)
- Vite (bundler)
- TailwindCSS (styling)
- React Query (data fetching)
- Zustand (state management)
- i18next (pt-BR translations)
- Vitest (tests)

**Infrastructure:**
- Docker (containerization)
- MongoDB Atlas ou local (persistence)
- GitHub Actions (CI/CD)
- GitHub (version control)

---

## 3. MODELO DE DADOS MONGODB

### 3.1 Collections Necessárias

```
pigmeu_content_copilot (database)
├── original_sources          (fontes processadas)
├── content_generation_requests (requisições + resultados)
├── system_config            (configurações do sistema — opcional MVP)
└── audit_logs               (logs de auditoria — opcional MVP)
```

### 3.2 Collection: `original_sources`

**Índices:**
```javascript
db.original_sources.createIndex({ "metadata.source_url": 1 }, { unique: true });
db.original_sources.createIndex({ "processing.status": 1 });
db.original_sources.createIndex({ "processing.fetched_at": -1 });
db.original_sources.createIndex({ "metadata.pub_date": -1 });
```

**Documento Exemplo:**
```json
{
  "_id": "src-uuid-001",
  "metadata": {
    "source_url": "https://medium.com/@insorg/article",
    "canonical_url": "https://medium.com/@insorg/article?utm_source=...",
    "title": "How to Write Perfect User Stories",
    "meta_description": "Best practices for writing clear user stories in Agile projects",
    "language": "en",
    "pub_date": "2025-12-15T10:00:00Z",
    "categories": ["agile", "product-management"],
    "tags": ["user-stories", "scrum", "requirements"]
  },
  "content": {
    "raw_html": "<html>...(full HTML)...</html>",
    "sanitized_html": "<p>...(cleaned HTML)...</p>",
    "text_extracted": "Extracted plain text..."
  },
  "toc": [
    {
      "id": "intro",
      "level": 2,
      "text": "Introdução"
    },
    {
      "id": "fundamentals",
      "level": 2,
      "text": "Conceitos Fundamentais",
      "children": [
        {
          "id": "definitions",
          "level": 3,
          "text": "Definições"
        }
      ]
    }
  ],
  "ia_analysis": {
    "title": "IA Extracted Title",
    "summary": "Brief summary (max 300 words)...",
    "key_points": ["Point 1", "Point 2", "Point 3"],
    "sentiment": "positive",
    "processed_at": "2026-02-06T11:00:00Z",
    "model_used": "mistral-large-latest",
    "tokens_used": {
      "input": 850,
      "output": 250
    }
  },
  "processing": {
    "fetched_at": "2026-02-06T10:30:00Z",
    "sanitized_at": "2026-02-06T10:31:00Z",
    "analyzed_at": "2026-02-06T11:00:00Z",
    "status": "completed",
    "errors": [],
    "retry_count": 0
  },
  "metadata_audit": {
    "created_at": "2026-02-06T10:30:00Z",
    "updated_at": "2026-02-06T11:00:00Z",
    "created_by": "api-v1",
    "version": 1
  }
}
```

**Validação JSON Schema:**
```json
{
  "$jsonSchema": {
    "bsonType": "object",
    "required": ["_id", "metadata", "content", "processing"],
    "properties": {
      "_id": { "bsonType": "string", "description": "UUID v4" },
      "metadata": {
        "bsonType": "object",
        "required": ["source_url"],
        "properties": {
          "source_url": { "bsonType": "string" },
          "canonical_url": { "bsonType": "string" },
          "title": { "bsonType": "string" },
          "meta_description": { "bsonType": "string" },
          "pub_date": { "bsonType": "date" }
        }
      },
      "content": {
        "bsonType": "object",
        "required": ["raw_html", "sanitized_html"],
        "properties": {
          "raw_html": { "bsonType": "string" },
          "sanitized_html": { "bsonType": "string" },
          "text_extracted": { "bsonType": "string" }
        }
      },
      "toc": {
        "bsonType": "array",
        "items": {
          "bsonType": "object",
          "required": ["id", "level", "text"],
          "properties": {
            "id": { "bsonType": "string" },
            "level": { "bsonType": "int", "minimum": 1, "maximum": 6 },
            "text": { "bsonType": "string" },
            "children": { "bsonType": "array" }
          }
        }
      },
      "processing": {
        "bsonType": "object",
        "required": ["status"],
        "properties": {
          "status": { "enum": ["pending", "processing", "completed", "error"] },
          "errors": { "bsonType": "array", "items": { "bsonType": "string" } }
        }
      }
    }
  }
}
```

---

### 3.3 Collection: `content_generation_requests`

**Índices:**
```javascript
db.content_generation_requests.createIndex({ "request_metadata.status": 1 });
db.content_generation_requests.createIndex({ "request_metadata.created_at": -1 });
db.content_generation_requests.createIndex({ "request_metadata.user_id": 1 });
db.content_generation_requests.createIndex({ "sources.source_ids": 1 });
```

**Documento Exemplo:**
```json
{
  "_id": "req-uuid-001",
  "request_metadata": {
    "created_at": "2026-02-06T11:05:00Z",
    "updated_at": "2026-02-06T11:20:00Z",
    "status": "completed",
    "user_id": "user-123",
    "request_name": "User Stories Content Request"
  },
  "sources": {
    "source_ids": ["src-uuid-001", "src-uuid-002"],
    "count": 2,
    "fetched_at": "2026-02-06T11:05:30Z"
  },
  "initial_context": {
    "consolidation_type": "automated",
    "context_text": "## RESUMO 1: How to Write Perfect...\n## RESUMO 2: ...\n## TOC 1: ...",
    "summaries_count": 2,
    "tocs_count": 2,
    "generated_at": "2026-02-06T11:06:00Z"
  },
  "title_ideas": {
    "ideas": [
      "Writing User Stories That Actually Work",
      "User Stories Best Practices for Agile Teams",
      "Mastering the Art of User Story Writing"
    ],
    "count": 3,
    "generated_at": "2026-02-06T11:07:00Z",
    "model_used": "mistral-large-latest",
    "tokens_used": {
      "input": 2500,
      "output": 150
    }
  },
  "selected_title": "Writing User Stories That Actually Work",
  "selected_title_at": "2026-02-06T11:08:00Z",
  "toc_proposals": {
    "proposals": [
      {
        "proposal_id": 1,
        "type": "frequency_optimized",
        "description": "Based on most frequent structure patterns",
        "toc": [
          {
            "id": "intro",
            "level": 1,
            "text": "Introduction",
            "children": [
              { "id": "what", "level": 2, "text": "What are User Stories?" }
            ]
          }
        ],
        "metrics": {
          "total_h2": 5,
          "total_h3": 12,
          "seo_score": 8.5
        }
      },
      {
        "proposal_id": 2,
        "type": "enhanced_relevance",
        "description": "Enhanced with relevant new subtitles",
        "toc": [],
        "metrics": { "total_h2": 6, "total_h3": 15, "seo_score": 8.8 }
      },
      {
        "proposal_id": 3,
        "type": "research_expanded",
        "description": "Expanded with external research and SEO optimization",
        "toc": [],
        "metrics": { "total_h2": 7, "total_h3": 18, "seo_score": 9.0 }
      }
    ],
    "count": 3,
    "generated_at": "2026-02-06T11:10:00Z",
    "model_used": "mistral-large-latest",
    "tokens_used": {
      "input": 3200,
      "output": 1800
    }
  },
  "selected_toc": {
    "proposal_id": 2,
    "selected_at": "2026-02-06T11:12:00Z",
    "toc": []
  },
  "configuration": {
    "target_audience": "Software developers",
    "writing_style": "Technical",
    "allow_internet_sources": true,
    "max_idea_count": 5,
    "language": "pt-BR"
  },
  "execution_log": {
    "phases": [
      {
        "phase": "fetch_sources",
        "status": "completed",
        "started_at": "2026-02-06T11:05:30Z",
        "ended_at": "2026-02-06T11:06:00Z",
        "errors": []
      },
      {
        "phase": "build_context",
        "status": "completed",
        "started_at": "2026-02-06T11:06:00Z",
        "ended_at": "2026-02-06T11:06:30Z",
        "errors": []
      },
      {
        "phase": "generate_titles",
        "status": "completed",
        "started_at": "2026-02-06T11:06:30Z",
        "ended_at": "2026-02-06T11:07:30Z",
        "errors": []
      },
      {
        "phase": "generate_toc",
        "status": "completed",
        "started_at": "2026-02-06T11:07:30Z",
        "ended_at": "2026-02-06T11:10:00Z",
        "errors": []
      }
    ]
  },
  "metadata_audit": {
    "created_by": "api-v1",
    "version": 1,
    "environment": "production"
  }
}
```

---

### 3.4 Relação entre Collections

```
content_generation_requests
│
├─→ sources.source_ids (array de referências)
│   └─→ original_sources._id (FK)
│
└─→ selected_title (seleção de usuário)
    └─→ (valor, não referência)

Ciclo de vida do request:
"pending" → "processing" → "completed" ou "error"

Ciclo de vida das sources:
"pending" → "processing" → "completed" ou "error"
```

---

## 4. DOCUMENTAÇÃO CONSOLIDADA DE REQUISITOS E ARQUITETURA

### 4.1 Estrutura de Documentação (Arquivos)

```
pigmeu/content-copilot/
├── docs/
│   ├── 01-escopo-mvp.md          (Este documento — Escopo)
│   ├── 02-arquitetura.md         (Arquitetura técnica)
│   ├── 03-modelo-dados.md        (MongoDB schema)
│   ├── 04-requisitos-funcionais.md (Detalhes de F1-F10)
│   ├── 05-wireframes.md          (WF1-WF5)
│   ├── 06-api-endpoints.md       (Swagger / endpoints)
│   ├── 07-componentes-react.md   (Props, tipos, exemplos)
│   └── 08-plano-implementacao.md (Tasks, dependências, estimativas)
├── backend/
├── frontend/
└── README.md (entry point)
```

### 4.2 Tabela Consolidada: Features vs. Components vs. APIs

| Feature | Backend Component | Frontend Component | API Endpoint | MongoDB Collection |
|---|---|---|---|---|
| **F1: Fetch HTML** | `html_fetcher.py` | (Progress bar) | POST /fetch | original_sources |
| **F2: Extract Metadata** | `metadata_extractor.py` | (Status badge) | GET /status | original_sources |
| **F3: Extract TOC** | `toc_extractor.py` | (Info display) | GET /status | original_sources |
| **F4: Build Context** | `context_builder.py` | (Progress bar) | GET /status | content_generation_requests |
| **F5: Generate Titles** | `prompt_loader.py` + `mistral_adapter.py` | TitleList | POST /generate | content_generation_requests |
| **F6: Select Title** | (state management) | TitleList | POST /select | content_generation_requests |
| **F7: Generate TOC** | `prompt_loader.py` + `mistral_adapter.py` | TocProposalCard | POST /generate | content_generation_requests |
| **F8: Select TOC** | (state management) | TocProposalCard | POST /select | content_generation_requests |
| **F9: Persistência** | `storage_backend.py` | (Auto-save) | — | MongoDB |
| **F10: Validação & Retry** | `output_validator.py` + `retry_handler.py` | (Async handling) | — | — |

### 4.3 Matriz de Requisitos Não-Funcionais

| Requisito | Target | Verificação | Status |
|---|---|---|---|
| **Performance: Fetch** | < 5s/URL | Timeout config em requests | ✓ In |
| **Performance: Sanitize** | < 500ms/URL | Benchmark com fixtures | ✓ In |
| **Performance: IA Call** | < 60s | Timeout em provider adapter | ✓ In |
| **Performance: UI** | < 2s load + 1s updates | Lighthouse / React profiler | ✓ In |
| **Security: No API keys** | Usar env vars | Config validation schema | ✓ In |
| **Security: URL validation** | Whitelist + format | `urllib.parse.urlparse` | ✓ In |
| **Security: HTML sanitize** | Whitelist tags/attrs | Bleach library | ✓ In |
| **Security: No log secrets** | Sanitize logs | Config schema + linter | ✓ In |
| **Scalability: Stateless API** | Multiple instances | No in-memory state | ✓ In |
| **Observability: Logging** | Structured logs | JSON format + levels | ⚠️ Partial |
| **Accessibility: WCAG 2.1 A** | Keyboard nav + contrast | Axe DevTools | ✓ In |
| **Internationalization** | pt-BR + en-US ready | i18next setup | ✓ In |

### 4.4 Dependências Entre Tasks (Crítica → Não Crítica)

```
T1.1 (Config)
 └─→ T1.2 (Secrets)
      └─→ T2.1 (Fetcher)
           └─→ T2.2 (Sanitizer)
                └─→ T2.3 (Metadata)
                     ├─→ T2.4 (TOC)
                     │    └─→ T2.5 (Pipeline)
                     │         └─→ T3.1 (Loader)
                     │              └─→ T4.1 (Provider Interface)
                     │                   └─→ T4.2 (Mistral Adapter)
                     │                        └─→ T5.1 (Validator)
                     │                             └─→ T5.2 (Retry)
                     │                                  └─→ T5.3 (Runner E2E)
                     │                                       └─→ T6.1 (Storage)
                     │                                            └─→ T7.1 (Tests)
                     │                                                 └─→ T7.2 (CI/CD)
                     │
                     └─→ Frontend (paralelo após T5.3)
                          ├─→ Components (LinksList, ProgressBar, etc.)
                          ├─→ API Client
                          ├─→ i18n setup
                          └─→ Vitest + integration tests
```

### 4.5 Checklist Final (MVP Pronto)

**Backend:**
- [ ] Configs validados (no hardcoded secrets)
- [ ] Fetch & sanitize funcionando com 10+ URLs teste
- [ ] Metadata & TOC extraídos corretamente
- [ ] Context consolidado em Markdown
- [ ] Prompt loader carregando manifestos
- [ ] Provider adapter chamando Mistral
- [ ] Validação de output (JSON + schema)
- [ ] Retry policy implementada
- [ ] Persistência em MongoDB funcionando
- [ ] Logs estruturados por fase
- [ ] 70%+ coverage em testes
- [ ] CI/CD executando testes em cada PR

**Frontend:**
- [ ] 5 componentes principais (LinksList, ProgressBar, TitleList, TocProposalCard, ResultSummary)
- [ ] API client integrado
- [ ] i18n pt-BR completo
- [ ] Validação de inputs (URLs, limites)
- [ ] Polling de status em tempo real
- [ ] Modais funcionando (confirmação, preview)
- [ ] Feedback de erro (toasts)
- [ ] Acessibilidade mínima (labels, contraste)
- [ ] Vitest rodando

**Documentação:**
- [ ] README com setup + run instructions
- [ ] Especificação de requisitos (este doc)
- [ ] Arquitetura (diagrama + fluxo)
- [ ] MongoDB schema + migration script
- [ ] Swagger / API docs
- [ ] Contributing guide

---

## 5. PLANO DE IMPLEMENTAÇÃO (High-Level)

### Sprint 1 (Days 1-2): Setup & Backend Core

**Tasks:**
- T1.1, T1.2: Config & secrets
- T2.1, T2.2, T2.3, T2.4: Fetcher, sanitizer, extractors
- T2.5: CLI pipeline `fetch_and_sanitize.py`

**Deliverables:**
- ✓ Config validado
- ✓ CLI que processa 10 URLs
- ✓ JSON salvo em `data/original_sources/`

### Sprint 2 (Days 3-4): Prompts & Providers

**Tasks:**
- T3.1, T3.2: Loader module
- T4.1, T4.2, T4.3: Provider adapter (Mistral)
- T5.1: Output validator

**Deliverables:**
- ✓ Loader funcionando
- ✓ Adapter chamando Mistral
- ✓ Output validado vs schema

### Sprint 3 (Days 5-6): Orquestrator & API

**Tasks:**
- T5.2, T5.3: Retry handler + runner E2E
- T6.1: Storage (MongoDB)
- Backend REST API endpoints

**Deliverables:**
- ✓ Runner E2E funcionando (fetch → context → ideas → TOC)
- ✓ API endpoints implementados
- ✓ MongoDB collections criadas

### Sprint 4 (Days 7-8): Frontend

**Tasks:**
- Componentes React (LinksList, Progress, TitleList, TocProposalCard)
- API client
- i18n pt-BR
- Integração com backend

**Deliverables:**
- ✓ 5 telas funcionando
- ✓ Fluxo E2E clicável
- ✓ Validação de inputs

### Sprint 5 (Days 9-10): Tests & CI/CD

**Tasks:**
- T7.1, T7.2: Unit tests + CI/CD
- Documentation
- README + Swagger

**Deliverables:**
- ✓ 70%+ coverage
- ✓ CI/CD rodando
- ✓ Documentação completa

---

## 6. CRITÉRIOS DE ACEITAÇÃO GLOBAIS

### Funcionalidades

- [ ] Fluxo E2E funcionando: entrada URLs → ideias de títulos → 3 propostas TOC
- [ ] Validação de inputs (URLs, limites)
- [ ] Progresso em tempo real (polling / polling)
- [ ] Persistência em MongoDB
- [ ] Retry automático em erro de validação
- [ ] Export JSON de resultados

### Qualidade

- [ ] 70%+ code coverage (backend)
- [ ] Testes passam em CI/CD
- [ ] Sem erros/warnings em linter
- [ ] Documentação Swagger funcional

### UX

- [ ] 5 telas mapeadas (entrada, progresso, seleção título, propostas TOC, resumo)
- [ ] Feedback de erro visível (toasts, modals)
- [ ] Acessibilidade mínima (WCAG 2.1 A)
- [ ] i18n pt-BR completo

### Performance

- [ ] Fetch < 5s/URL
- [ ] Sanitize < 500ms/URL
- [ ] UI carregamento < 2s
- [ ] Progresso atualiza < 1s

---

Pronto! Agora temos:
✅ **Escopo MVP revisado** (10 features in, 8 out)
✅ **Arquitetura técnica** (diagrama + fluxo)
✅ **Modelo MongoDB** (2 collections + índices + exemplos)
✅ **Documentação** (requisitos, tabelas, matriz)
✅ **Plano de 5 sprints** (estimativa 8-10 dias)

**Próximos passos:**
1. Quer que eu gere os 8 documentos detalhados (01-escopo.md até 08-plano.md)?
2. Ou começamos a implementação (backend setup + T1.1-T1.2)?

Qual prefere?

User: Gere todos os documentos detalhados e salve no path pigmeu/content-copilot/docs

GitHub Copilot: 