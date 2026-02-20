"""
Ananya MCP Server
=================
A Python MCP (Model Context Protocol) server that exposes Ananya's
word-processing PHP APIs as LLM-callable tools.

Also provides a /chat HTTP endpoint for the PHP frontend to call,
which handles the full LLM orchestration loop:
    user question → LLM provider (with tools) → tool calls → PHP API → final answer

Usage:
    python server.py            # starts MCP (SSE) + /chat on port 8000
"""

import json
import asyncio
import logging
import re
from typing import Any

from mcp.server.fastmcp import FastMCP
from starlette.applications import Starlette
from starlette.requests import Request
from starlette.responses import JSONResponse
from starlette.routing import Route, Mount
from starlette.middleware.cors import CORSMiddleware
import uvicorn
import openai

from config import (
    OPENAI_API_KEY, OLLAMA_URL, LLM_PROVIDER, LLM_MODEL, LLM_MAX_TOKENS, LLM_TEMPERATURE,
    API_BASE_URL, MCP_HOST, MCP_PORT,
)
from api_client import AnanyaAPIClient

# ── Logging ─────────────────────────────────────────────────────────────
logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
logger = logging.getLogger("ananya-mcp")

# ── MCP Server ──────────────────────────────────────────────────────────
mcp = FastMCP(
    "ananya-word-processor",
    instructions=(
        "Ananya Word Processor API server. Provides tools for text analysis, "
        "character parsing, word comparison, and validation for English and "
        "Indic languages (Telugu, Hindi, Gujarati, Malayalam)."
    ),
)

api = AnanyaAPIClient(API_BASE_URL)

# ═══════════════════════════════════════════════════════════════════════
# TEXT TOOLS
# ═══════════════════════════════════════════════════════════════════════

@mcp.tool()
async def reverse_text(word: str, language: str = "english") -> str:
    """Reverse a word or string character-by-character.

    Args:
        word: The word or text to reverse.
        language: Language of the text (english, telugu, hindi, etc.). Defaults to english.
    """
    result = await api.text_reverse(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_text_length(word: str, language: str = "english") -> str:
    """Get the length (number of characters) of a word or string.

    Args:
        word: The word or text to measure.
        language: Language of the text. Defaults to english.
    """
    result = await api.text_length(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def randomize_text(word: str, language: str = "english") -> str:
    """Randomly shuffle the characters of a word or string.

    Args:
        word: The word or text to randomize.
        language: Language of the text. Defaults to english.
    """
    result = await api.text_randomize(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def split_text(word: str, delimiter: str = "-", language: str = "english") -> str:
    """Split a word or string by a delimiter.

    Args:
        word: The word or text to split.
        delimiter: The character to split on. Defaults to hyphen.
        language: Language of the text. Defaults to english.
    """
    result = await api.text_split(word, delimiter, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def replace_in_text(word: str, search: str, replace_with: str, language: str = "english") -> str:
    """Find and replace a substring within a word or string.

    Args:
        word: The original word or text.
        search: The substring to find.
        replace_with: The replacement substring.
        language: Language of the text. Defaults to english.
    """
    result = await api.text_replace(word, search, replace_with, language)
    return json.dumps(result) if not isinstance(result, str) else result


# ═══════════════════════════════════════════════════════════════════════
# CHARACTER TOOLS
# ═══════════════════════════════════════════════════════════════════════

@mcp.tool()
async def get_logical_characters(word: str, language: str = "english") -> str:
    """Parse a word into its logical characters (grapheme clusters).
    For Indic scripts, this correctly groups base + modifier characters.

    Args:
        word: The word to parse.
        language: Language of the text. Defaults to english.
    """
    result = await api.characters_logical(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_base_characters(word: str, language: str = "english") -> str:
    """Get the base characters of a word (stripping modifiers in Indic scripts).

    Args:
        word: The word to analyze.
        language: Language of the text. Defaults to english.
    """
    result = await api.characters_base(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_code_points(word: str, language: str = "english") -> str:
    """Get the Unicode code points of each character in the word.

    Args:
        word: The word to analyze.
        language: Language of the text. Defaults to english.
    """
    result = await api.characters_codepoints(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_character_at_position(word: str, index: int = 0, language: str = "english") -> str:
    """Get the logical character at a specific position in the word.

    Args:
        word: The word to index into.
        index: Zero-based position of the character. Defaults to 0.
        language: Language of the text. Defaults to english.
    """
    result = await api.characters_logical_at(word, index, language)
    return json.dumps(result) if not isinstance(result, str) else result


# ═══════════════════════════════════════════════════════════════════════
# ANALYSIS TOOLS
# ═══════════════════════════════════════════════════════════════════════

@mcp.tool()
async def check_palindrome(word: str, language: str = "english") -> str:
    """Check if a word is a palindrome (reads the same forwards and backwards).

    Args:
        word: The word to check.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_is_palindrome(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_anagram(word1: str, word2: str, language: str = "english") -> str:
    """Check if two words are anagrams of each other (same letters, different order).

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_is_anagram(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def can_make_word(source_word: str, target_word: str, language: str = "english") -> str:
    """Check if a target word can be formed using only the letters from the source word.
    For example, can_make_word('minneapolis', 'mine') checks if 'mine' can be spelled
    using letters from 'minneapolis'.

    Args:
        source_word: The source word providing available letters.
        target_word: The target word to try to form.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_can_make_word(source_word, target_word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def can_make_all_words(source_word: str, words: str, language: str = "english") -> str:
    """Check if ALL given words can be formed from the source word's letters.

    Args:
        source_word: The source word providing available letters.
        words: Comma-separated list of words to check.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_can_make_all_words(source_word, words, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_word_strength(word: str, language: str = "english") -> str:
    """Calculate the strength metric of a word (based on character diversity and complexity).

    Args:
        word: The word to analyze.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_word_strength(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_word_weight(word: str, language: str = "english") -> str:
    """Calculate the weight metric of a word.

    Args:
        word: The word to analyze.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_word_weight(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_word_level(word: str, language: str = "english") -> str:
    """Calculate the difficulty level of a word.

    Args:
        word: The word to analyze.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_word_level(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def detect_language(text: str) -> str:
    """Detect the language of a given text string. Returns the detected language name.

    Args:
        text: The text whose language to detect.
    """
    result = await api.analysis_detect_language(text)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_intersecting(word1: str, word2: str, language: str = "english") -> str:
    """Check if two words share common (intersecting) characters.

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_is_intersecting(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def get_intersecting_rank(word1: str, word2: str, language: str = "english") -> str:
    """Get the intersecting rank (count of shared characters) between two words.

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_intersecting_rank(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_ladder_words(word1: str, word2: str, language: str = "english") -> str:
    """Check if two words are ladder words (differ by exactly one character).

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_are_ladder_words(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_head_tail_words(word1: str, word2: str, language: str = "english") -> str:
    """Check if two words have a head-tail relationship (last char of word1 = first char of word2).

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_are_head_tail_words(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def parse_to_logical_chars(word: str, language: str = "english") -> str:
    """Parse a word into its logical character components. Similar to get_logical_characters
    but uses a different parsing algorithm.

    Args:
        word: The word to parse.
        language: Language of the text. Defaults to english.
    """
    result = await api.analysis_parse_to_logical_chars(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


# ═══════════════════════════════════════════════════════════════════════
# COMPARISON TOOLS
# ═══════════════════════════════════════════════════════════════════════

@mcp.tool()
async def check_starts_with(word: str, prefix: str, language: str = "english") -> str:
    """Check if a word starts with a given prefix.

    Args:
        word: The word to check.
        prefix: The prefix to look for.
        language: Language of the text. Defaults to english.
    """
    result = await api.comparison_starts_with(word, prefix, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_ends_with(word: str, suffix: str, language: str = "english") -> str:
    """Check if a word ends with a given suffix.

    Args:
        word: The word to check.
        suffix: The suffix to look for.
        language: Language of the text. Defaults to english.
    """
    result = await api.comparison_ends_with(word, suffix, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def compare_words(word1: str, word2: str, language: str = "english") -> str:
    """Lexicographically compare two words. Returns negative if word1 < word2,
    zero if equal, positive if word1 > word2.

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.comparison_compare(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_equals(word1: str, word2: str, language: str = "english") -> str:
    """Check if two words are exactly equal.

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.comparison_equals(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_reverse_equals(word1: str, word2: str, language: str = "english") -> str:
    """Check if the reverse of word1 equals word2.

    Args:
        word1: The first word.
        word2: The second word.
        language: Language of the text. Defaults to english.
    """
    result = await api.comparison_reverse_equals(word1, word2, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def find_index_of(word: str, search: str, language: str = "english") -> str:
    """Find the position (index) of a substring within a word. Returns -1 if not found.

    Args:
        word: The word to search within.
        search: The substring to find.
        language: Language of the text. Defaults to english.
    """
    result = await api.comparison_index_of(word, search, language)
    return json.dumps(result) if not isinstance(result, str) else result


# ═══════════════════════════════════════════════════════════════════════
# VALIDATION TOOLS
# ═══════════════════════════════════════════════════════════════════════

@mcp.tool()
async def check_contains_char(word: str, char: str, language: str = "english") -> str:
    """Check if a word contains a specific character.

    Args:
        word: The word to search.
        char: The character to look for.
        language: Language of the text. Defaults to english.
    """
    result = await api.validation_contains_char(word, char, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_contains_string(word: str, substring: str, language: str = "english") -> str:
    """Check if a word contains a specific substring.

    Args:
        word: The word to search.
        substring: The substring to look for.
        language: Language of the text. Defaults to english.
    """
    result = await api.validation_contains_string(word, substring, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_is_consonant(character: str, language: str = "english") -> str:
    """Check if a character is a consonant.

    Args:
        character: The character to check.
        language: Language context. Defaults to english.
    """
    result = await api.validation_is_consonant(character, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_is_vowel(character: str, language: str = "english") -> str:
    """Check if a character is a vowel.

    Args:
        character: The character to check.
        language: Language context. Defaults to english.
    """
    result = await api.validation_is_vowel(character, language)
    return json.dumps(result) if not isinstance(result, str) else result


@mcp.tool()
async def check_contains_space(word: str, language: str = "english") -> str:
    """Check if a word or string contains any spaces.

    Args:
        word: The word or text to check.
        language: Language of the text. Defaults to english.
    """
    result = await api.validation_contains_space(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


# ═══════════════════════════════════════════════════════════════════════
# UTILITY TOOLS
# ═══════════════════════════════════════════════════════════════════════

@mcp.tool()
async def get_length_no_spaces(word: str, language: str = "english") -> str:
    """Get the length of a string excluding spaces.

    Args:
        word: The text to measure.
        language: Language of the text. Defaults to english.
    """
    result = await api.utility_length_no_spaces(word, language)
    return json.dumps(result) if not isinstance(result, str) else result


# ═══════════════════════════════════════════════════════════════════════
# CHAT ORCHESTRATION ENDPOINT (non-MCP, for PHP frontend)
# ═══════════════════════════════════════════════════════════════════════

# Build the OpenAI tool definitions from MCP tools at startup
def _build_openai_tools() -> list[dict]:
    """Convert MCP tool definitions into OpenAI function-calling tool format."""
    tools = []
    for tool_name, tool_obj in mcp._tool_manager._tools.items():
        # Build parameters schema from the tool's input schema
        input_schema = tool_obj.parameters
        tools.append({
            "type": "function",
            "function": {
                "name": tool_name,
                "description": tool_obj.description or "",
                "parameters": input_schema,
            }
        })
    return tools


async def _execute_tool(name: str, arguments: dict) -> str:
    """Execute an MCP tool by name with the given arguments."""
    try:
        tool_fn = mcp._tool_manager._tools.get(name)
        if not tool_fn:
            return json.dumps({"error": f"Unknown tool: {name}"})
        result = await tool_fn.run(arguments)
        # FastMCP tool.run() returns content list; extract text
        if hasattr(result, '__iter__') and not isinstance(result, str):
            texts = []
            for item in result:
                if hasattr(item, 'text'):
                    texts.append(item.text)
                else:
                    texts.append(str(item))
            return "\n".join(texts)
        return str(result)
    except Exception as e:
        logger.error(f"Tool execution error ({name}): {e}")
        return json.dumps({"error": str(e)})


def _create_llm_client() -> openai.AsyncOpenAI:
    """Create an OpenAI-compatible client for either OpenAI or Ollama."""
    provider = LLM_PROVIDER.lower()
    if provider == "ollama":
        base_url = OLLAMA_URL.rstrip("/") + "/v1"
        return openai.AsyncOpenAI(api_key="ollama", base_url=base_url)
    return openai.AsyncOpenAI(api_key=OPENAI_API_KEY)


def _infer_string_from_question(question: str) -> str:
    match = re.search(r"\"([^\"]+)\"|'([^']+)'", question)
    if match:
        return match.group(1) or match.group(2) or ""
    parts = re.split(r"\s+", question.strip())
    return parts[-1] if parts else ""


def _detect_intent(question: str, language: str) -> tuple[str, dict] | None:
    q = question.lower()
    if re.search(r"\b(scramble|randomize|shuffle)\b", q):
        word = _infer_string_from_question(question)
        if not word:
            return None
        return ("randomize_text", {"word": word, "language": language})
    return None


def _format_tool_answer(tool_name: str, word: str, tool_result: str) -> str:
    result_text = tool_result
    try:
        parsed = json.loads(tool_result)
        if isinstance(parsed, dict):
            result_text = parsed.get("result") or parsed.get("data") or tool_result
    except Exception:
        pass

    if tool_name == "randomize_text":
        return f"Scrambled version of \"{word}\" is \"{result_text}\"."
    return str(result_text)


SYSTEM_PROMPT = """You are Ananya, a helpful AI assistant specialized in word processing and text analysis.
You have access to a set of tools that can analyze, compare, validate, and transform words and text
in English and Indic languages (Telugu, Hindi, Gujarati, Malayalam).

When a user asks a question about words or text, use the appropriate tool(s) to get accurate results.
For example:
- To reverse a word, use the reverse_text tool.
- To check if a word is a palindrome, use the check_palindrome tool.
- To check if a word can be formed from letters of another word, use the can_make_word tool.
- To check prefixes or suffixes, use check_starts_with or check_ends_with.

For questions about generating words (like rhyming words, words with a prefix, or words from letters),
use your own knowledge to generate candidate words, then verify them with the tools when appropriate.
For instance, if asked for words that can be made from "minneapolis", generate candidates from your
knowledge, then use can_make_word to verify each one.

Always provide clear, concise answers. If a tool returns data, interpret it for the user in plain language.
Do not show raw JSON to the user unless they specifically ask for it."""


async def chat_endpoint(request: Request) -> JSONResponse:
    """
    POST /chat
    Body: {"question": "...", "language": "english"}
    Returns: {"question": "...", "language": "...", "answer": "..."}

    This endpoint orchestrates the full LLM + tool-calling loop:
    1. Send user question + tools to the configured LLM provider
    2. If LLM responds with tool_calls, execute them against the MCP tools
    3. Send tool results back to LLM
    4. Repeat until LLM produces a final text answer
    """
    try:
        body = await request.json()
    except Exception:
        return JSONResponse({"error": "Invalid JSON body"}, status_code=400)

    question = (body.get("question") or "").strip()
    language = body.get("language", "english")

    if not question:
        return JSONResponse({"error": "Missing question parameter"}, status_code=400)

    # Fast-path: obvious intent without LLM
    intent = _detect_intent(question, language)
    if intent:
        tool_name, tool_args = intent
        tool_result = await _execute_tool(tool_name, tool_args)
        word = tool_args.get("word", "")
        answer = _format_tool_answer(tool_name, word, tool_result)
        return JSONResponse({
            "question": question,
            "language": language,
            "answer": answer,
        })

    if LLM_PROVIDER.lower() == "openai" and not OPENAI_API_KEY:
        return JSONResponse({
            "question": question,
            "language": language,
            "answer": "Error: OPENAI_API_KEY is not configured. Please set it in mcp_server/.env"
        })

    client = _create_llm_client()
    openai_tools = _build_openai_tools()

    messages = [
        {"role": "system", "content": SYSTEM_PROMPT},
        {"role": "user", "content": f"[Language: {language}]\n\n{question}"},
    ]

    # Orchestration loop — max 10 iterations to prevent runaway
    max_iterations = 10
    for iteration in range(max_iterations):
        try:
            response = await client.chat.completions.create(
                model=LLM_MODEL,
                messages=messages,
                tools=openai_tools if openai_tools else None,
                max_tokens=LLM_MAX_TOKENS,
                temperature=LLM_TEMPERATURE,
            )
        except openai.APIError as e:
            logger.error(f"OpenAI API error: {e}")
            return JSONResponse({
                "question": question, "language": language,
                "answer": f"LLM service error: {str(e)}"
            })

        choice = response.choices[0]
        message = choice.message

        # If the LLM produced a final text answer (no tool calls), we're done
        if choice.finish_reason == "stop" or not message.tool_calls:
            answer = message.content or ""
            logger.info(f"Chat complete after {iteration + 1} iteration(s)")
            return JSONResponse({
                "question": question,
                "language": language,
                "answer": answer.strip(),
            })

        # Otherwise, execute each tool call and feed results back
        # First, append the assistant message with tool_calls
        messages.append(message.model_dump())

        for tool_call in message.tool_calls:
            fn_name = tool_call.function.name
            try:
                fn_args = json.loads(tool_call.function.arguments)
            except json.JSONDecodeError:
                fn_args = {}

            logger.info(f"Executing tool: {fn_name}({fn_args})")
            tool_result = await _execute_tool(fn_name, fn_args)
            logger.info(f"Tool result: {tool_result[:200]}")

            messages.append({
                "role": "tool",
                "tool_call_id": tool_call.id,
                "content": tool_result,
            })

    # If we exhausted iterations, return what we have
    return JSONResponse({
        "question": question, "language": language,
        "answer": "I processed your request but it required too many steps. Please try a simpler question."
    })


async def health_endpoint(request: Request) -> JSONResponse:
    """GET /health — simple health check."""
    tool_count = len(mcp._tool_manager._tools) if hasattr(mcp, '_tool_manager') else 0
    return JSONResponse({
        "status": "ok",
        "server": "ananya-mcp",
        "tools_available": tool_count,
        "model": LLM_MODEL,
        "provider": LLM_PROVIDER,
    })


# ═══════════════════════════════════════════════════════════════════════
# APPLICATION SETUP
# ═══════════════════════════════════════════════════════════════════════

def create_app() -> Starlette:
    """Create the combined Starlette app with /chat, /health, and MCP SSE routes."""

    # Get the MCP SSE app (handles /sse and /messages endpoints)
    mcp_app = mcp.sse_app()

    routes = [
        Route("/chat", chat_endpoint, methods=["POST"]),
        Route("/health", health_endpoint, methods=["GET"]),
        # Mount the MCP SSE app at /mcp for MCP protocol clients
        Mount("/mcp", app=mcp_app),
    ]

    app = Starlette(routes=routes)

    # Add CORS middleware so the PHP frontend can call /chat
    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],
        allow_methods=["*"],
        allow_headers=["*"],
    )

    return app


if __name__ == "__main__":
    logger.info(f"Starting Ananya MCP Server on {MCP_HOST}:{MCP_PORT}")
    logger.info(f"  API Backend: {API_BASE_URL}")
    logger.info(f"  LLM Model:   {LLM_MODEL}")
    logger.info(f"  Chat:        http://{MCP_HOST}:{MCP_PORT}/chat")
    logger.info(f"  Health:      http://{MCP_HOST}:{MCP_PORT}/health")
    logger.info(f"  MCP SSE:     http://{MCP_HOST}:{MCP_PORT}/mcp/sse")

    app = create_app()
    uvicorn.run(app, host=MCP_HOST, port=MCP_PORT, log_level="info")
