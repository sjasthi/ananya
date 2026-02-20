"""
Configuration loader for the Ananya MCP Server.
Reads settings from .env file or environment variables.
"""

import os
from dotenv import load_dotenv

# Load .env from the same directory as this file
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), '.env')
load_dotenv(_env_path)

# LLM provider
LLM_PROVIDER = os.getenv('LLM_PROVIDER', 'openai')
OPENAI_API_KEY = os.getenv('OPENAI_API_KEY', '')Here’s the approach that best fits those goals and is stable across semesters:

Core idea: deterministic pipeline + LLM only where it adds value.

LLM for “idea generation” only (word candidates, themes, synonyms, ranking).
APIs for correctness (parse logical chars, validation, word length, etc.).
Router for reliability: map clear intents directly to APIs so student work doesn’t break when models change.
Local goals (FP3–FP4):

Implement an intent router for common English prompts and Telugu‑mixed prompts.
Use the LLM only to interpret ambiguous requests or extract candidate words.
Always finish with API execution, not LLM guesses.
Remote goals (FP5–FP6):

Add a health check + API smoke tests on the remote host.
Log every failure with reason + API name + params so students can triage quickly.
Maintain a single .env‑based config for local vs hosted API endpoints.
Puzzle generation (FP7–FP10):
Pipeline should be explicit and repeatable:

LLM proposes 10 words for the theme (e.g., Dances of India).
Ananya APIs normalize and validate (logical chars, language checks).
Deterministic puzzle builder places words into a grid (16x12 default).
Solution grid generated alongside.
HTML output for puzzles + solutions (batch mode for 100 themes).
Why this fits semester‑to‑semester transitions:

Deterministic logic lives in code, not in model behavior.
LLM is replaceable: if models change, only word suggestion quality shifts, not API correctness.
Students can debug in clear stages (LLM → validate → build → render).
If you want, I can outline the exact pipeline functions and file layout for the puzzle module so it’s easy for next semester’s team to pick up.
OLLAMA_URL = os.getenv('OLLAMA_URL', 'http://localhost:11434')
LLM_MODEL = os.getenv('LLM_MODEL', 'gpt-4o-mini')
if LLM_PROVIDER.lower() == 'ollama' and 'LLM_MODEL' not in os.environ:
	LLM_MODEL = 'mistral'
LLM_MAX_TOKENS = int(os.getenv('LLM_MAX_TOKENS', '1200'))
LLM_TEMPERATURE = float(os.getenv('LLM_TEMPERATURE', '0.2'))

# PHP API backend
API_BASE_URL = os.getenv('API_BASE_URL', 'http://localhost/ananya/ananya_files/api.php')

# MCP Server
MCP_HOST = os.getenv('MCP_HOST', 'localhost')
MCP_PORT = int(os.getenv('MCP_PORT', '8000'))
