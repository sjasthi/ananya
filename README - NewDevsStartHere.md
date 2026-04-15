# Ananya - Indic Language Text Processing API

> Comprehensive text processing toolkit for Indic languages with REST API interface

## 📖 How to Use This Guide

**You are here:** This README gets you from zero to running in about 15 minutes.

1. Follow the Quick Start section below to deploy and verify the app
2. Once running, explore the app UI and Swagger docs to learn capabilities
3. Reference the docs and app for API details — this guide is setup-only

---

## 🌍 Supported Languages

- ✅ **Telugu** (Fully implemented)
- ✅ **English** (Fully implemented)
- ✅ **Hindi** (Implemented)
- ✅ **Gujarati** (Implemented)
- ✅ **Malayalam** (Implemented)
- ❌ Tamil (TBD)
- ❌ Kannada (TBD)

## 🚀 Quick Start

### 0. Prerequisites Setup

Before starting, ensure you have:

- **PHP/Apache**: Install [XAMPP](https://www.apachefriends.org/) (Windows/Mac/Linux)
- **Python 3.10+**: Download from [python.org](https://www.python.org/downloads/)
  - Windows: check "Add Python to PATH" during install
- **LLM Provider API Key** (for optional MCP chat):
  - [Groq](https://console.groq.com/) (recommended, free tier available)
  - [Gemini](https://makersuite.google.com/app/apikey)
  - [OpenAI](https://platform.openai.com/api-keys)

### 1. Local Deployment

```bash
# Deploy to your web server
cp -r ananya/ /path/to/htdocs/

# Access the application
http://localhost/ananya/
```

### 2. MCP Server (Optional)

The MCP server provides tool-orchestrated chat behavior on top of Ananya APIs.

1. **Install MCP dependencies**

   ```powershell
   cd ananya/mcp_server
   python -m pip install -r requirements.txt
   ```

1. **Configure environment**

   ```powershell
   cd ananya/mcp_server
   cp .env.example .env
   ```

   Update `.env` as needed. Baseline example:

   ```env
   LLM_PROVIDER=groq
   GROQ_API_KEY=your_groq_api_key_here
   LLM_MODEL=llama-3.3-70b-versatile
   API_BASE_URL=http://localhost/ananya/api.php
   MCP_HOST=localhost
   MCP_PORT=8000
   ```

1. **Start MCP server**

   ```powershell
   cd ananya/mcp_server
   python server.py
   ```

### 3. Verify Local Setup

- App: <http://localhost/ananya/>
- API docs UI: <http://localhost/ananya/docs/api.php>
- Swagger UI: <http://localhost/ananya/docs/swagger.php>
- MCP health (if running): <http://localhost:8000/health>

Quick API check:

```bash
GET http://localhost/ananya/api.php/text/length?string=అమెరికా&language=telugu
```

### 4. Explore in the App

Use the product UI and live docs as your primary reference:

- Main app: <http://localhost/ananya/>
- Chat UI: <http://localhost/ananya/chat.php>
- Telugu playground: <http://localhost/ananya/play_with_telugu.php>
- API docs: <http://localhost/ananya/docs/api.php>
- Swagger explorer: <http://localhost/ananya/docs/swagger.php>
- Swagger explorer: <http://localhost/ananya/docs/swagger.php>

## 🌐 Remote MCP Setup (For Live Demos)

Use this when hosting at <https://ananya.telugupuzzles.com/index.php>.

1. **Deploy code to server**
   - Ensure `ananya/` and `mcp_server/` are present.

1. **Install dependencies**

   ```bash
   cd /path/to/ananya/mcp_server
   python -m pip install -r requirements.txt
   ```

1. **Configure `.env`**

   ```env
   LLM_PROVIDER=groq
   GROQ_API_KEY=your_groq_api_key_here
   LLM_MODEL=llama-3.3-70b-versatile
   API_BASE_URL=http://ananya.telugupuzzle.com/api.php
   MCP_HOST=0.0.0.0
   MCP_PORT=8000
   ```

1. **Start server**

   ```bash
   cd /path/to/ananya/mcp_server
   python server.py
   ```

1. **Verify**
   - <http://localhost:8000/health>

## 📖 Documentation

- Interactive API docs: [docs/api.php](docs/api.php)
- Swagger UI: [docs/swagger.php](docs/swagger.php)
- OpenAPI spec: [docs/openapi.yaml](docs/openapi.yaml)
- Markdown API reference: [docs/API_Reference.md](docs/API_Reference.md)

## ✅ Testing

- Contract checks:

  ```bash
  python scripts/contract_check.py
  ```

- API test script:

  ```bash
  python api_telugu_tester.py
  ```

## 🧰 Troubleshooting

- **MCP health endpoint fails**
  - Confirm `python server.py` is running in `ananya/mcp_server`.
- **API calls fail locally**
  - Confirm Apache/XAMPP is serving `http://localhost/ananya/`.
- **Provider/auth failures**
  - Check `LLM_PROVIDER`, `LLM_MODEL`, and provider API key in `mcp_server/.env`.

## 🤝 Contributing

1. Fork repository
1. Create branch (`git checkout -b feature/AmazingFeature`)
1. Run checks (`python scripts/contract_check.py`)
1. Commit and open PR

## 📄 License

This project is open source. See repository files for details.
