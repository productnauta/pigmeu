# Setup and Installation Guide

---

## **1. Introduction**
### **1.1 Purpose**
This document provides **step-by-step instructions** for setting up and installing the **CITO (Content Copilot) MVP**. It covers prerequisites, installation, configuration, and execution of the system.

### **1.2 Audience**
This guide is intended for:
- **Developers** responsible for deploying and maintaining the CITO MVP.
- **Project Managers** overseeing the implementation of the content generation pipeline.
- **AI/ML Engineers** configuring AI providers and prompts.

### **1.3 Scope**
This document includes:
- Prerequisites for running the CITO MVP.
- Installation steps for dependencies and the codebase.
- Configuration of AI providers, models, and prompts.
- Execution of the content generation pipeline.
- Troubleshooting common issues.

### **1.4 References**
- [Requirements Documentation](requirements.md)
- [Architecture Documentation](architecture.md)
- [Pipeline Documentation](pipeline.md)
- [Prompts Documentation](prompts.md)

---

## **2. Prerequisites**
Before setting up the CITO MVP, ensure the following prerequisites are met:

### **2.1 System Requirements**
| Requirement | Details |
|-------------|---------|
| **Operating System** | Linux (Ubuntu 20.04/22.04), macOS (10.15+), or Windows (WSL2) |
| **Python Version** | Python 3.9 or higher |
| **Memory** | Minimum 8GB RAM (16GB recommended for large-scale processing) |
| **Storage** | Minimum 1GB free disk space |
| **Network** | Stable internet connection for API calls to AI providers |

### **2.2 Software Dependencies**
| Dependency | Purpose | Installation Command |
|------------|---------|----------------------|
| **Python 3.9+** | Core programming language | `sudo apt-get install python3.9` (Linux) or [Python Official Website](https://www.python.org/downloads/) |
| **pip** | Python package manager | `sudo apt-get install python3-pip` (Linux) or bundled with Python |
| **MongoDB** | NoSQL database for storing content and metadata | [MongoDB Installation Guide](https://docs.mongodb.com/manual/installation/) |
| **Git** | Version control for cloning the repository | `sudo apt-get install git` (Linux) or [Git Official Website](https://git-scm.com/downloads) |

### **2.3 AI Provider Accounts**
CITO MVP supports the following AI providers. Ensure you have active accounts and API keys for at least one of them:

| Provider | API Key Required | Signup Link |
|----------|------------------|-------------|
| **Mistral** | Yes | [Mistral AI](https://mistral.ai/) |
| **Groq** | Yes | [Groq Console](https://console.groq.com/) |
| **Gemini** | Yes | [Google AI Studio](https://aistudio.google.com/) |

---

## **3. Installation**
### **3.1 Clone the Repository**
Clone the CITO MVP repository to your local machine:

```bash
# Clone the repository
git clone https://github.com/your-organization/content-copilot.git

# Navigate to the project directory
cd content-copilot
```

### **3.2 Set Up a Python Virtual Environment**
Create and activate a Python virtual environment to isolate dependencies:

```bash
# Create a virtual environment
python3 -m venv venv

# Activate the virtual environment
# Linux/macOS
source venv/bin/activate
# Windows
venv\Scripts\activate
```

### **3.3 Install Python Dependencies**
Install the required Python packages using `pip`:

```bash
pip install -r requirements.txt
```

#### **Sample `requirements.txt`**
```text
beautifulsoup4==4.12.2
jinja2==3.1.2
pymongo==4.6.1
pyyaml==6.0.1
requests==2.31.0
jsonschema==4.19.0
```

### **3.4 Set Up MongoDB**
Ensure MongoDB is installed and running locally or configure a remote instance:

#### **Local MongoDB Setup**
1. Install MongoDB using the [official installation guide](https://docs.mongodb.com/manual/installation/).
2. Start the MongoDB service:
   ```bash
   sudo systemctl start mongod
   ```
3. Verify the service is running:
   ```bash
   sudo systemctl status mongod
   ```

#### **Remote MongoDB Setup**
If using a remote MongoDB instance (e.g., MongoDB Atlas), ensure:
- The connection URI is configured in the CITO MVP.
- Network access is allowed from your machine to the MongoDB instance.

---

## **4. Configuration**
### **4.1 Configure AI Providers**
Create or update the `configs/providers.yaml` file to include your AI provider API keys and endpoints:

```yaml
providers:
  mistral:
    api_key: "your-mistral-api-key"
    base_url: "https://api.mistral.ai/v1"
  groq:
    api_key: "your-groq-api-key"
    base_url: "https://api.groq.com/openai/v1"
  gemini:
    api_key: "your-gemini-api-key"
    base_url: "https://generativelanguage.googleapis.com/v1beta"
```

### **4.2 Configure Model Presets**
Update the `configs/model_presets.yaml` file to define model parameters for different use cases:

```yaml
model_presets:
  deterministic_json:
    temperature: 0.3
    max_tokens: 2000
    response_format: { "type": "json_object" }
  creative_text:
    temperature: 0.7
    max_tokens: 1000
```

### **4.3 Configure Prompts**
The `configs/prompts.yaml` file acts as a registry for available prompts. Ensure it includes all prompts used in the pipeline:

```yaml
prompts:
  - id: toc_generation
    description: "Generates three TOC proposals for a blog article."
    path: "core/prompts/toc_generation/prompt.yaml"
```

### **4.4 Configure Environment Variables**
Create a `.env` file in the project root to store sensitive configuration:

```text
# MongoDB Configuration
MONGO_URI="mongodb://localhost:27017"
MONGO_DB_NAME="cito_db"

# AI Provider API Keys (Optional: Overrides providers.yaml)
MISTRAL_API_KEY="your-mistral-api-key"
GROQ_API_KEY="your-groq-api-key"
GEMINI_API_KEY="your-gemini-api-key"
```

---

## **5. Execution**
### **5.1 Run the Content Generation Pipeline**
The CITO MVP pipeline consists of multiple stages, each executed by a dedicated script. Below are examples of how to run key scripts:

#### **1. HTML Processing**
Process and sanitize HTML input:

```bash
python core/scripts/html_processor.py --input input.html --output sanitized_output.json
```

#### **2. TOC Generation**
Generate TOC proposals using an AI provider:

```bash
python core/scripts/toc-test-2.py --input prompt_bundle.json --output toc_proposals.json
```

#### **3. Store Results in MongoDB**
Store the generated TOC proposals in MongoDB:

```bash
python core/scripts/store_results.py --input toc_proposals.json --collection original_sources
```

### **5.2 End-to-End Pipeline Execution**
To run the entire pipeline end-to-end, use the following command:

```bash
# Example: Process HTML, generate TOC, and store results
python core/scripts/html_processor.py --input input.html --output sanitized_output.json && \
python core/scripts/toc-test-2.py --input sanitized_output.json --output toc_proposals.json && \
python core/scripts/store_results.py --input toc_proposals.json --collection original_sources
```

---

## **6. Troubleshooting**
### **6.1 Common Issues and Solutions**
| Issue | Possible Cause | Solution |
|-------|----------------|----------|
| **ModuleNotFoundError** | Missing Python dependencies | Run `pip install -r requirements.txt` |
| **MongoDB Connection Error** | MongoDB service not running or incorrect URI | Start MongoDB service or update `MONGO_URI` in `.env` |
| **AI Provider API Error** | Invalid API key or rate limits | Verify API key in `providers.yaml` or `.env` |
| **JSON Schema Validation Error** | AI output does not match schema | Check `output.schema.json` for the prompt and validate AI output |
| **File Not Found Error** | Incorrect file path or missing file | Verify file paths and ensure files exist |

### **6.2 Logging and Debugging**
- **Logs**: Check the `logs/` directory for detailed execution logs.
- **Debug Mode**: Run scripts with the `--debug` flag to enable verbose logging:
  ```bash
  python core/scripts/toc-test-2.py --input prompt_bundle.json --output toc_proposals.json --debug
  ```

### **6.3 Contact Support**
If you encounter issues not listed here, contact the development team with:
- A **description of the issue**.
- **Logs** from the `logs/` directory.
- **Steps to reproduce** the issue.

---

## **7. Next Steps**
### **7.1 Post-Setup Validation**
After setting up the CITO MVP, validate the installation by:
1. Running the **end-to-end pipeline** with a sample HTML file.
2. Verifying that **TOC proposals** are generated and stored in MongoDB.
3. Checking **logs** for errors or warnings.

### **7.2 Explore Advanced Features**
- **Custom Prompts**: Add new prompts for additional content generation tasks (e.g., article drafting).
- **WordPress Integration**: Configure the system to publish generated content directly to WordPress.
- **Performance Optimization**: Adjust model parameters in `model_presets.yaml` for faster or higher-quality outputs.

### **7.3 Contribute to Development**
- **Report Bugs**: Open issues on the [GitHub repository](https://github.com/your-organization/content-copilot).
- **Submit Pull Requests**: Contribute improvements or new features.
- **Documentation Updates**: Help improve this guide or other documentation files.

---

## **8. Appendix**
### **8.1 Directory Structure**
```text
content-copilot/
├── configs/
│   ├── providers.yaml
│   ├── model_presets.yaml
│   └── prompts.yaml
├── core/
│   ├── prompts/
│   │   └── toc_generation/
│   │       ├── prompt.yaml
│   │       ├── template/
│   │       └── schemas/
│   ├── scripts/
│   │   ├── html_processor.py
│   │   ├── toc-test-2.py
│   │   └── store_results.py
│   └── utils/
│       ├── loaders.py
│       └── validators.py
├── docs/
│   └── mvp/
│       ├── requirements.md
│       ├── architecture.md
│       ├── data_model.md
│       ├── pipeline.md
│       ├── prompts.md
│       ├── scripts.md
│       └── setup.md
├── logs/
├── .env
├── requirements.txt
└── README.md
```

### **8.2 Example Input and Output**
#### **Input HTML (`input.html`)**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Sample Blog</title>
</head>
<body>
    <h1>Introduction to AI</h1>
    <p>AI is transforming industries.</p>
    <h2>Applications of AI</h2>
    <p>AI is used in healthcare, finance, and more.</p>
</body>
</html>
```

#### **Output TOC Proposals (`toc_proposals.json`)**
```json
{
  "proposals": [
    {
      "title": "Understanding AI: A Comprehensive Guide",
      "toc": [
        {
          "level": "h1",
          "text": "Introduction to AI"
        },
        {
          "level": "h2",
          "text": "Applications of AI",
          "children": [
            {
              "level": "h3",
              "text": "Healthcare"
            },
            {
              "level": "h3",
              "text": "Finance"
            }
          ]
        }
      ]
    }
  ]
}
```