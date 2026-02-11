"""
Configuration loader for the Ananya MCP Server.
Reads settings from .env file or environment variables.
"""

import os
from dotenv import load_dotenv

# Load .env from the same directory as this file
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), '.env')
load_dotenv(_env_path)

# OpenAI
OPENAI_API_KEY = os.getenv('OPENAI_API_KEY', '')
LLM_MODEL = os.getenv('LLM_MODEL', 'gpt-4o-mini')
LLM_MAX_TOKENS = int(os.getenv('LLM_MAX_TOKENS', '1200'))
LLM_TEMPERATURE = float(os.getenv('LLM_TEMPERATURE', '0.2'))

# PHP API backend
API_BASE_URL = os.getenv('API_BASE_URL', 'http://localhost/ananya/ananya_files/api.php')

# MCP Server
MCP_HOST = os.getenv('MCP_HOST', 'localhost')
MCP_PORT = int(os.getenv('MCP_PORT', '8000'))
