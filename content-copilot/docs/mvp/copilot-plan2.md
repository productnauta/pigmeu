Mapeie tarefas MVP essencial
No tasks in progress


Mapeie as tarefas necessárias para o mvp, sugira as funcionalidades do mvp.
References prior conversation

Plan
Open

Título: Plano do MVP do Content Copilot (pipeline completo via CLI + MongoDB)

Resumo

Entregar um fluxo end-to-end a partir de URLs até ideias de título e propostas de TOC, com persistência no MongoDB.
Consolidar o framework de prompts (load/run/validate) e criar os prompts faltantes do pipeline.
Expor o MVP via CLI única, com seleção interativa de título e TOC.
Funcionalidades do MVP

Entrada de URLs e parâmetros de geração (público-alvo, estilo, quantidade de ideias).
Extração de HTML bruto, metadados e TOC, com sanitização de HTML.
Análise inicial via IA e consolidação de contexto.
Geração de ideias de títulos e seleção pelo usuário.
Geração de 3 propostas de TOC e seleção pelo usuário.
Persistência completa em original_sources e content_generation_requests.
Validação de saída via JSON Schema para todos os prompts.
Tarefas Necessárias

Corrigir configs e segredos: ajustar providers.yaml para um único provider mistral com env_api_key; remover chaves hardcoded em prompt.yaml; criar database.yaml com URI e db; criar requirements.txt.
Consolidar framework de prompts: extrair o loader de toc-test-1.py para load_prompt_bundle.py; criar prompt_runner.py para executar provider e validar JSON/schema; ajustar toc-test-2.py para usar o runner.
Registrar prompts do MVP: atualizar prompts.yaml para incluir initial-content-analysis, build-source-context, content-ideas-generation e content-toc-generation, mantendo content-toc-generation apontando para o diretório atual toc_generation.
Criar prompts faltantes: adicionar diretórios em pigmeu/content-copilot/core/prompts/ com prompt.yaml, system-prompt.md, user-message.md e output.schema.json para cada prompt do MVP, com saída JSON estrita.
Implementar ingestão: criar módulos em pigmeu/content-copilot/core/ingestion/ para fetch_html, extract_metadata, extract_toc e sanitize_html, seguindo as regras da doc; persistir resultados em original_sources.
Implementar persistência MongoDB: criar mongo.py com conexão, upserts e helpers para original_sources e content_generation_requests; definir índices mínimos por URL e request.
Orquestrar o pipeline via CLI: criar mvp_cli.py com argumentos --urls (múltiplos), --audience, --style, --ideas-count; implementar a sequência do fluxo e seleção interativa de título e TOC; garantir reexecução idempotente.
Observabilidade e erros: adicionar logging por etapa e política de retry por prompt conforme prompt.yaml; registrar falhas no documento de request.
Testes: criar pigmeu/content-copilot/tests/ com unit tests de ingestão/sanitização/TOC, tests de validação dos schemas de prompt e um teste end-to-end com provider mockado.
Mudanças em APIs/Interfaces/Tipos

CLI pública: mvp_cli.py --urls URL1 --urls URL2 --audience "..." --style "..." --ideas-count 10.
Collection original_sources: campos mínimos url, raw_html, metadata, toc, sanitized_html, ia_analysis, created_at, updated_at.
Collection content_generation_requests: campos mínimos input, source_ids, initial_context, generated_title_ideas, selected_title, generated_toc_proposals, selected_toc, status, errors.
Saída do prompt initial-content-analysis: {title, summary, key_points[]}.
Saída do prompt build-source-context: {context_md}.
Saída do prompt content-ideas-generation: {content_ideas[]}.
Saída do prompt content-toc-generation: schema existente em output.schema.json.
Testes e Cenários

Extração de metadados e TOC a partir de HTML com H1/H2/H3 e metas canônicas.
Sanitização remove script/style e mantém tags permitidas.
Prompt runner valida JSON minificado e schema para cada prompt.
Pipeline end-to-end com 2 URLs e provider mockado preenche todos os campos de ambas as collections.
Fluxo de seleção registra selected_title e selected_toc corretamente.
Assunções e Defaults

Provider único do MVP: Mistral via env_api_key.
Saídas dos prompts em JSON estrito e minificado.
Python 3.11; dependências principais: requests, beautifulsoup4, lxml, bleach, pymongo, jinja2, jsonschema, mistralai.
User attachment
cONTINUE O PLANEJAMENTO CONSIDERANDO OS PROTÓTIPOS EM ANEXO

Para continuar o planejamento considerando o protótipo, preciso destas definições:

Superfície do MVP: Web app, Somente backend/CLI, ou Web + CLI?
Ao clicar em “Give me ideas”, execução: Síncrono até finalizar, Assíncrono com job, ou Síncrono com progresso por etapas?
Quando “Allow internet access” estiver ativo: Buscar novas fontes e adicionar ao pipeline, Apenas usar URLs fornecidas, ou Usar provider com browsing sem adicionar fontes ao banco?

Síncrono com progresso por etapas
Síncrono com progresso por etapas
NAVEGAR E BUSCAR NA INTERNET QUANDO A =OPÇÃO ESTIVER ATIVA

Falta confirmar a superfície do MVP. É Web app (tela do protótipo)?


wEB APP (PYTHON/FLASK...)

<proposed_plan>
Título: Plano do MVP com Web App (Flask) e Progresso por Etapas

Resumo

Implementar um Web App em Flask com a tela do protótipo (links, texto adicional e opções).
Executar o fluxo de geração de ideias de forma síncrona, exibindo progresso por etapas.
Quando “Allow internet access” estiver ativo, realizar busca na internet para adicionar novas fontes ao pipeline.
Funcionalidades do MVP (UI + fluxo)

Tela Content Copilot com:
Campo de links (lista de URLs).
Campo “Additional Information” (texto livre).
Opções: Allow internet access e IA Provider.
Botão “Give me ideas”.
Execução síncrona com progresso por etapas (ex.: etapa atual + percentuais).
Persistência completa em MongoDB:
original_sources e content_generation_requests.
Geração de:
Ideias de títulos.
3 propostas de TOC.
Seleção do título e TOC pelo usuário.
Tarefas Necessárias

Web App (Flask):
Criar pigmeu/content-copilot/web/ com app.py, templates e assets.
Implementar página principal conforme protótipo.
Implementar endpoint /generate para disparar o fluxo.
Progresso por etapas (síncrono):
Implementar mecanismo de progresso no backend (etapa atual).
Retornar progresso para a UI via SSE ou polling curto.
Pipeline e prompts:
Completar prompts faltantes (initial-content-analysis, build-source-context, content-ideas-generation, content-toc-generation) com templates e schemas.
Consolidar runner de prompts e validação JSON.
Ingestão e IA:
Buscar HTML, extrair metadados, TOC e sanitizar HTML.
Rodar análise inicial, consolidar contexto, gerar ideias e TOCs.
Internet Access:
Quando ativo, executar busca na web e adicionar URLs relevantes às fontes.
Persistência MongoDB:
Implementar coleções e estrutura conforme doc.
Seleção:
Salvar título e TOC escolhidos pelo usuário.
Mudanças em APIs/Interfaces

Interface Web: página única Content Copilot.
Endpoints:
GET / (formulário).
POST /generate (executa pipeline).
GET /progress/<request_id> (atualizações de etapa).
content_generation_requests armazenará status e etapa atual.
Testes

Teste de UI para submissão do formulário e retorno de progresso.
Testes unitários de ingestão e sanitização.
Teste end-to-end simulando execução com 2 URLs.
Testes de schema para cada prompt.
Assunções

Flask como framework web.
Execução síncrona com progresso por etapas via polling ou SSE.
Internet access = busca e adição de fontes no pipeline.
</proposed_plan>

cRIE UM DOCUMENTO DE REQUISITOS COMPLETO

Preciso de 3 definições para criar o documento:

Escopo: somente MVP Web App, produto completo, ou MVP + roadmap?
Destino: requirements.md, atualizar content-copilot.md, ou outro caminho?
Idioma: pt-BR, en-US, ou bilingue?

1

Você escolheu a opção 1 para o escopo. Falta definir:

Destino: requirements.md ou atualizar content-copilot.md?
Idioma: pt-BR ou en-US?

Destino: requirements.md

pt-BR

<proposed_plan>
Título: Plano para Documento de Requisitos do MVP (Web App Flask)

Resumo

Criar requirements.md em pt-BR.
Documento focado no MVP Web App com fluxo síncrono e progresso por etapas.
Requisitos alinhados ao protótipo anexado e ao pipeline descrito em content-copilot.md.
Estrutura e Conteúdo do Documento

Visão Geral

Objetivo do MVP.
Descrição do problema que resolve.
Resultado esperado (ideias de títulos + propostas de TOC).
Escopo

Em escopo: Web App, ingestão de fontes, IA, persistência, seleção de título/TOC, progresso por etapas, busca na internet com toggle ativo.
Fora de escopo: publicação em WordPress, módulos SEO avançados, geração de artigo completo.
Usuários e Casos de Uso

Perfil principal: criador de conteúdo.
Caso de uso principal: inserir links + info adicional → receber ideias e TOC.
Requisitos Funcionais (FR)

UI conforme protótipo:
Lista de links com adicionar/remover.
Campo “Additional Information” (texto livre).
Opções: Allow internet access e IA Provider.
Botão “Give me ideas”.
Execução síncrona com progresso por etapas:
Etapas: ingestão → metadados → TOC → sanitização → análise IA → contexto → ideias → TOC.
UI mostra etapa atual e status.
Internet access:
Quando ativo: buscar na web e adicionar novas URLs ao pipeline.
Quando inativo: usar somente URLs fornecidas.
Pipeline de IA:
Análise inicial, contexto, ideias de títulos e propostas de TOC.
Seleção do usuário:
Escolha de título e TOC com persistência.
Requisitos de Dados

Definir coleções e campos mínimos:
original_sources: url, raw_html, metadata, toc, sanitized_html, ia_analysis, timestamps.
content_generation_requests: input, source_ids, initial_context, generated_title_ideas, selected_title, generated_toc_proposals, selected_toc, status, progress_stage, errors.
Integrações Externas

LLM (Mistral) via configuração.
Fetch de URLs (HTML).
Busca na web (provedor configurável, sem detalhar vendor).
Requisitos Não Funcionais (NFR)

Performance: limites de URLs por request e timeouts.
Segurança: chaves via env vars, sem hardcode.
Confiabilidade: retries configuráveis para prompts.
Observabilidade: logs por etapa e por request.
Requisitos de UX

Alinhamento visual com o protótipo (layout, sidebar, cartões).
Estados de loading e mensagens de erro por etapa.
Critérios de Aceite

Fluxo completo executa e persiste dados.
UI exibe progresso e resultados.
Seleção salva corretamente.
Assunções

Flask como framework web.
MongoDB como persistência principal.
Provedores de busca e LLM configuráveis.
Mudanças/Interfaces

Definir endpoints esperados:
GET / (tela principal).
POST /generate (disparo do pipeline).
GET /progress/<request_id> (progresso).
Mapear campos do formulário para o payload do backend.
Testes a Descrever

Unitários: sanitização, extração, validação JSON.
Integração: pipeline com mocks.
E2E: submissão do formulário → resultados.
Assunções e Defaults

Idioma pt-BR.
Documentação voltada ao MVP Web App.
</proposed_plan>

Crie a documentação

Segue o conteúdo para requirements.md (pt-BR).

# Content Copilot — Requisitos do MVP (Web App Flask)

## 1. Visão Geral
- MVP entrega ideias de títulos e 3 propostas de TOC a partir de URLs fornecidas.
- Execução síncrona com progresso por etapas, iniciada via botão “Give me ideas”.
- Quando “Allow internet access” estiver ativo, o sistema busca novas fontes na web e as adiciona ao pipeline.

## 2. Objetivos
- Reduzir tempo de pesquisa e estruturação inicial de artigos.
- Garantir saídas estruturadas (JSON validado) e persistidas.
- Oferecer interface simples (protótipo anexo) para input e acompanhamento do fluxo.

## 3. Escopo
- Em escopo: Web app Flask, formulário de links e texto livre, toggle de internet access, escolha de provider, pipeline de ingestão → análise → contexto → ideias → TOC, persistência em MongoDB, seleção de título e TOC, exibição de progresso.
- Fora de escopo: publicação em WordPress, geração do artigo completo, SEO tools avançadas, contas/SSO, controle de acesso granular.

## 4. Personas e Uso
- Persona: criador/analista de conteúdo.
- Uso típico: inserir 2–5 URLs, descrever objetivo no campo Additional Information, escolher provider, ativar ou não internet access, clicar em “Give me ideas”, acompanhar progresso, selecionar título e TOC.

## 5. Requisitos Funcionais
- UI (protótipo):
  - Campo de lista de links com adicionar/remover.
  - Campo “Additional Information” sem limite prático de texto.
  - Checkbox `Allow internet access` e seletor `IA Provider`.
  - Botão “Give me ideas”.
- Execução e progresso:
  - Etapas mínimas: `ingestion`, `metadata`, `toc_extraction`, `sanitization`, `ia_analysis`, `context_build`, `title_ideas`, `toc_proposals`, `user_selection`.
  - Backend devolve etapa atual e status; UI exibe barra/etiqueta de progresso.
- Internet access:
  - Quando ativo, serviço de busca coleta URLs adicionais relevantes e adiciona à fila de ingestão do mesmo request.
  - Quando inativo, somente URLs fornecidas são usadas.
- Pipeline IA:
  - Prompts: `initial-content-analysis`, `build-source-context`, `content-ideas-generation`, `content-toc-generation` (schema existente).
  - Saídas sempre JSON minificado, validadas por JSON Schema.
- Seleção:
  - UI exibe lista de ideias de título; usuário escolhe uma.
  - UI exibe 3 propostas de TOC; usuário escolhe uma.
  - Escolhas são persistidas.
- Erros e retries:
  - Retries configuráveis para chamadas de LLM conforme `prompt.yaml`.
  - Falhas por etapa registradas no request com mensagem para UI.

## 6. Requisitos de Dados
- Coleção `original_sources`:
  - Campos: `url`, `raw_html`, `metadata`, `toc`, `sanitized_html`, `ia_analysis`, `created_at`, `updated_at`.
- Coleção `content_generation_requests`:
  - Campos: `input` (links, info adicional, flags), `source_ids`, `initial_context`, `generated_title_ideas`, `selected_title`, `generated_toc_proposals`, `selected_toc`, `status`, `progress_stage`, `errors`, `created_at`, `updated_at`.
- Logs de busca na web (opcional): `search_query`, `found_urls`, `chosen_urls`.

## 7. Fluxo de Execução (alto nível)
1) Receber formulário (links, texto, toggles, provider).
2) Se `allow_internet_access` ativo, buscar URLs adicionais e unir à lista.
3) Para cada URL: baixar HTML → salvar `raw_html`; extrair metadados → salvar `metadata`; extrair TOC → salvar `toc`; sanitizar HTML → salvar `sanitized_html`.
4) Rodar `initial-content-analysis` por fonte → salvar em `ia_analysis`.
5) Consolidar contexto com `build-source-context` → salvar `initial_context`.
6) Gerar ideias com `content-ideas-generation` → salvar `generated_title_ideas`.
7) Gerar TOCs com `content-toc-generation` → salvar `generated_toc_proposals`.
8) UI apresenta ideias e TOCs; usuário seleciona; salvar `selected_title` e `selected_toc`.
9) Atualizar `status=done`.

## 8. Interfaces / Endpoints
- `GET /` — renderiza UI.
- `POST /generate` — inicia fluxo; retorna `request_id`.
- `GET /progress/<request_id>` — progresso (etapa, status, percentuais simples).
- `GET /result/<request_id>` — dados finais (ideias, TOCs, seleções).
- (Interno) serviço de busca web acionado quando toggle ativo.

## 9. Requisitos Não Funcionais
- Performance: suportar 5 URLs por request; timeout por etapa configurável; LLM max 8k tokens por resposta.
- Disponibilidade: tolerar falhas de uma URL sem abortar todo o request; marcar erro parcial.
- Segurança: chaves de provider e busca via variáveis de ambiente; sem hardcode; sanitização rígida de HTML.
- Observabilidade: logs por request/etapa; IDs correlacionáveis; métricas básicas de duração por etapa.
- Conformidade: armazenar apenas dados necessários; respeitar robots.txt nas buscas (quando aplicável).

## 10. UX
- Estados: idle, em execução, sucesso, erro parcial, erro geral.
- Feedback: mensagem por etapa; aviso em caso de falha em alguma fonte.
- Acessibilidade: labels claros, foco no teclado, contraste adequado.

## 11. Configuração
- Arquivos em `pigmeu/content-copilot/configs/`: `providers.yaml`, `model_presets.yaml`, `database.yaml` (a criar), `prompts.yaml`.
- Variáveis de ambiente: `MISTRAL_API_KEY`, `SEARCH_API_KEY`, `MONGO_URI`, `MONGO_DB`.

## 12. Critérios de Aceite
- Enviar 2 URLs, toggle de internet ativo, fluxo completa e grava dados em ambas as coleções.
- Progresso exibe ao menos 5 etapas distintas durante a execução.
- Retorno final contém ≥3 ideias de título e exatamente 3 propostas de TOC válidas por schema.
- Seleções persistem e são retornadas em `/result/<request_id>`.

## 13. Riscos e Mitigações
- Respostas inválidas do LLM → retries + validação de schema.
- Busca web irrelevante → limitar por domínio/linguagem e rankear por relevância.
- HTML malformado → parser tolerante + sanitização.

## 14. Pendências
- Definir provedor de busca e política de ranking.
- Definir limite de URLs adicionadas via busca.
- Definir layout detalhado de progresso (barra vs. lista de etapas).

User attachment
User attachment
aNALISE OS WIREFRAMES DAS IMAGENS, e identifique os requisitos, itens do fluxo, funcionalidaes,

Requisitos de UI

Sidebar fixa com seções: Analytics, Content Copilot (ativo), SEO Tools, Prompts, Settings, Logout.
Tela 1 (entrada): lista de links com adicionar/remover; campo “Additional Information” texto livre; painel “Options” com toggle Allow internet access, seletor IA Provider, botão “GIVE ME IDEAS”.
Tela 2 (progresso/resultado): barra/linha de etapas com estado atual destacado; cartões de títulos das fontes analisadas; lista de sugestões de títulos destacando seleção; botões REGENERATE e ACCEPT; painel lateral “Step” com resumo de parâmetros (content sources, prompt, model, temperature).
Fluxo (etapas visuais)

Get Sources HTML
Extract Metadata
Summarize Sources Content
Suggest Title Ideas (etapa destacada no wireframe)
SEO Content Analysis
Suggest TOC Idea
Enrich Content
Review and Publish
Funcionalidades inferidas

Entrada de múltiplas URLs como fontes de conhecimento; remoção individual.
Entrada de contexto adicional em texto livre.
Opção para permitir busca/acesso à internet para ampliar contexto/fontes.
Escolha do provider/modelo de IA (ex.: Mistral, Llama 3.1 8b) e temperatura.
Execução do pipeline exibindo progresso por etapa.
Exibição dos títulos das fontes analisadas.
Geração de múltiplas ideias de título; possibilidade de regenerar e aceitar uma.
Seleção de uma ideia de título para continuar o fluxo.
Persistência/uso de parâmetros de execução exibidos no painel “Step”.

Considere to o contexto que já obteve, e entendimento sobre o sistema, e resuma o fluxo.

Fluxo resumido do Content Copilot (MVP Web App)

Entrada: usuário adiciona links (fontes) e texto livre; escolhe permitir internet e provider/modelo; clica “Give me ideas”.
Ingestão: baixa HTML de cada URL, extrai metadados e TOC, sanitiza HTML; se internet access ativo, busca novas fontes e adiciona.
Análise IA por fonte: roda prompt de análise inicial sobre HTML sanitizado e salva título, resumo, pontos-chave.
Consolidação: concatena resumos e TOCs, roda prompt de contexto (build-source-context) e salva initial_context.
Ideias de título: roda prompt (content-ideas-generation) usando contexto + inputs; retorna lista de ideias; UI permite regenerar e selecionar uma.
Propostas de TOC: roda prompt (content-toc-generation) com contexto + título escolhido + TOCs; retorna 3 propostas; usuário seleciona uma.
Progresso: UI mostra etapas da pipeline e status atual; painel lateral exibe parâmetros (fontes, prompt, modelo, temperatura).
Persistência (MongoDB): original_sources guarda HTML bruto, metadados, TOC, sanitizado, análises; content_generation_requests guarda inputs, contexto, ideias, seleções, status/progresso e erros.





GPT-5.2-Codex

Extra High


Local

Default permission
