# CITO (Content Copilot) - Prompt Framework Documentation

---

## **1. Introduction**

### **1.1 Purpose**
This document describes the **prompt framework** used in the **CITO (Content Copilot)** system, focusing on the **Minimum Viable Product (MVP)**. It provides an overview of the structure, templates, and validation mechanisms for prompts used in AI model interactions.

### **1.2 Scope**
The prompt framework described in this document covers:
- The **structure** of prompts.
- **Templates** for system and user messages.
- **Validation** of AI model outputs using JSON schemas.
- **Examples** of prompts used in the MVP.

Post-MVP prompts, such as those for advanced content generation, are not covered in this document.

### **1.3 Audience**
This document is intended for:
- **Developers** working on the CITO system.
- **AI engineers** designing and validating prompts.
- **Project managers** overseeing the development of CITO.

---

## **2. Prompt Framework Overview**

### **2.1 Structure of Prompts**
Prompts in CITO are modular and isolated in individual directories (e.g., `core/prompts/toc_generation/`). Each prompt consists of:

1. **Manifest File (`prompt.yaml`)**: Defines the prompt's metadata, configuration, and structure.
2. **Templates**: Markdown files for system and user messages (e.g., `template/system-prompt.md`).
3. **JSON Schema**: A schema for validating AI model outputs (e.g., `schemas/output.schema.json`).

### **2.2 Example Prompt Directory**
```
core/prompts/toc_generation/
├── prompt.yaml          # Manifest file
├── template/
│   ├── system-prompt.md # System message template
│   └── user-message.md  # User message template
└── schemas/
    └── output.schema.json # JSON schema for output validation
```

---

## **3. Prompt Manifest (`prompt.yaml`)**

### **3.1 Structure**
The `prompt.yaml` file defines the prompt's metadata, configuration, and structure. It includes the following sections:

| Section | Description |
|---------|-------------|
| `id` | Unique identifier for the prompt. |
| `description` | Description of the prompt's purpose. |
| `runtime` | Configuration for AI model execution (e.g., provider, model, parameters). |
| `messages` | List of messages (system and user) to be rendered. |
| `io` | Input and output configurations, including JSON schema validation. |
| `validation` | Additional validation rules for AI model outputs. |

### **3.2 Example `prompt.yaml`**
```yaml
id: toc_generation
description: Generates three TOC proposals for a blog article.

runtime:
  provider: mistral
  model: mistral-large-latest
  mode: chat
  model_preset: deterministic_json
  parameters:
    temperature: 0.3
    max_tokens: 2000

messages:
  - role: system
    template: template/system-prompt.md
  - role: user
    template: template/user-message.md

io:
  output:
    schema_ref: schemas/output.schema.json
    must_be_minified: true

validation:
  disallow_markdown: true
  schema:
    enabled: true
```

### **3.3 Key Fields**

#### **Runtime Configuration**
| Field | Description |
|-------|-------------|
| `provider` | The AI provider (e.g., `mistral`, `groq`, `gemini`). |
| `model` | The AI model to use (e.g., `mistral-large-latest`). |
| `mode` | The interaction mode (e.g., `chat`). |
| `model_preset` | The preset for model parameters (e.g., `deterministic_json`). |
| `parameters` | Additional parameters for the AI model (e.g., `temperature`, `max_tokens`). |

#### **Messages**
| Field | Description |
|-------|-------------|
| `role` | The role of the message (e.g., `system`, `user`). |
| `template` | The path to the template file for the message. |

#### **IO Configuration**
| Field | Description |
|-------|-------------|
| `schema_ref` | The path to the JSON schema for output validation. |
| `must_be_minified` | Whether the output must be minified (e.g., `true`). |

#### **Validation**
| Field | Description |
|-------|-------------|
| `disallow_markdown` | Whether markdown is disallowed in the output (e.g., `true`). |
| `schema.enabled` | Whether JSON schema validation is enabled (e.g., `true`). |

---

## **4. Templates**

### **4.1 Structure**
Templates are Markdown files that define the content of system and user messages. They are rendered using the **Jinja2** templating engine and support dynamic content injection.

### **4.2 Example Templates**

#### **System Prompt Template (`system-prompt.md`)**
```markdown
You are an expert content strategist specializing in blog article structuring.
Your task is to generate three Table of Contents (TOC) proposals for a blog article based on the provided context.

### Instructions:
1. Analyze the provided context, including the sanitized text and metadata.
2. Generate three distinct TOC proposals, each with a unique title and hierarchical structure.
3. Ensure the TOC proposals are logical, coherent, and aligned with the content's themes.
4. Return the proposals in JSON format, adhering to the provided schema.
```

#### **User Message Template (`user-message.md`)**
```markdown
### Context:
**Title**: {{ metadata.title }}
**Author**: {{ metadata.author }}
**Publication Date**: {{ metadata.publication_date }}
**Sanitized Text**:
{{ sanitized_text }}

### Key Themes:
{% for theme in ia_analysis.key_themes %}
- {{ theme.theme }} (Relevance: {{ theme.relevance }})
{% endfor %}

### Task:
Generate three TOC proposals for this blog article. Each proposal must include:
1. A unique title for the article.
2. A structured TOC with hierarchical headings (e.g., H1, H2, H3).

Return the proposals in JSON format, adhering to the following schema:
```json
{
  "proposals": [
    {
      "title": "string",
      "toc": [
        {
          "level": "string",
          "text": "string",
          "children": [
            {
              "level": "string",
              "text": "string"
            }
          ]
        }
      ]
    }
  ]
}
```
```

---

## **5. JSON Schema Validation**

### **5.1 Purpose**
JSON schemas are used to validate the outputs of AI models, ensuring they conform to expected structures.

### **5.2 Example Schema (`output.schema.json`)**
```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "proposals": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "title": { "type": "string" },
          "toc": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "level": { "type": "string", "enum": ["h1", "h2", "h3"] },
                "text": { "type": "string" },
                "children": { "type": "array" }
              },
              "required": ["level", "text"]
            }
          }
        },
        "required": ["title", "toc"]
      },
      "minItems": 3,
      "maxItems": 3
    }
  },
  "required": ["proposals"]
}
```

### **5.3 Validation Process**
1. **Load Schema**: The JSON schema is loaded from the prompt directory.
2. **Validate Output**: The AI model output is validated against the schema.
3. **Handle Errors**: Errors are logged and handled gracefully.

---

## **6. Example Prompts**

### **6.1 TOC Generation Prompt**
This prompt is used to generate three TOC proposals for a blog article.

#### **Manifest (`prompt.yaml`)**
```yaml
id: toc_generation
description: Generates three TOC proposals for a blog article.

runtime:
  provider: mistral
  model: mistral-large-latest
  mode: chat
  model_preset: deterministic_json
  parameters:
    temperature: 0.3
    max_tokens: 2000

messages:
  - role: system
    template: template/system-prompt.md
  - role: user
    template: template/user-message.md

io:
  output:
    schema_ref: schemas/output.schema.json
    must_be_minified: true

validation:
  disallow_markdown: true
  schema:
    enabled: true
```

#### **System Prompt (`system-prompt.md`)**
```markdown
You are an expert content strategist specializing in blog article structuring.
Your task is to generate three Table of Contents (TOC) proposals for a blog article based on the provided context.

### Instructions:
1. Analyze the provided context, including the sanitized text and metadata.
2. Generate three distinct TOC proposals, each with a unique title and hierarchical structure.
3. Ensure the TOC proposals are logical, coherent, and aligned with the content's themes.
4. Return the proposals in JSON format, adhering to the provided schema.
```

#### **User Message (`user-message.md`)**
```markdown
### Context:
**Title**: {{ metadata.title }}
**Author**: {{ metadata.author }}
**Publication Date**: {{ metadata.publication_date }}
**Sanitized Text**:
{{ sanitized_text }}

### Key Themes:
{% for theme in ia_analysis.key_themes %}
- {{ theme.theme }} (Relevance: {{ theme.relevance }})
{% endfor %}

### Task:
Generate three TOC proposals for this blog article. Each proposal must include:
1. A unique title for the article.
2. A structured TOC with hierarchical headings (e.g., H1, H2, H3).

Return the proposals in JSON format.
```

---

## **7. References**
- [CITO Requirements Documentation](../requirements.md)
- [CITO Architecture Documentation](../architecture.md)
- [CITO Data Model Documentation](../data_model.md)
- [CITO Pipeline Documentation](../pipeline.md)