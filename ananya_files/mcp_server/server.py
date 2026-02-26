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
    OPENAI_API_KEY, GEMINI_API_KEY, OLLAMA_URL, LLM_PROVIDER, LLM_MODEL, LLM_MAX_TOKENS, LLM_TEMPERATURE,
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

# ── Tool categories for two-stage filtering ────────────────────────────
TOOL_CATEGORIES = {
    "text": {
        "keywords": ["reverse", "length", "long", "characters", "randomize",
                     "scramble", "shuffle", "split", "replace", "swap"],
        "tools": ["reverse_text", "get_text_length", "randomize_text",
                  "split_text", "replace_in_text"],
    },
    "characters": {
        "keywords": ["logical", "grapheme", "base char", "codepoint", "unicode",
                     "character at", "position", "parse"],
        "tools": ["get_logical_characters", "get_base_characters",
                  "get_code_points", "get_character_at_position",
                  "get_codepoint_length", "get_random_logical_chars"],
    },
    "analysis": {
        "keywords": ["palindrome", "anagram",
                     "can make", "can i make", "make word", "make the word",
                     "make from", "form word", "form the word", "spell from",
                     "form", "spell",
                     "strength", "weight", "level", "difficulty", "detect",
                     "language", "intersect", "ladder", "head tail",
                     "chunk", "match"],
        "tools": ["check_palindrome", "check_anagram", "can_make_word",
                  "can_make_all_words", "get_word_strength", "get_word_weight",
                  "get_word_level", "detect_language", "check_intersecting",
                  "get_intersecting_rank", "check_ladder_words",
                  "check_head_tail_words", "parse_to_logical_chars"],
    },
    "comparison": {
        "keywords": ["starts with", "begins", "ends with", "compare",
                     "equal", "same", "reverse equal", "index", "find",
                     "position of", "where"],
        "tools": ["check_starts_with", "check_ends_with", "compare_words",
                  "check_equals", "check_reverse_equals", "find_index_of"],
    },
    "validation": {
        "keywords": ["contains", "has", "have", "include", "letter",
                     "consonant", "vowel", "space"],
        "tools": ["check_contains_char", "check_contains_string",
                  "check_is_consonant", "check_is_vowel",
                  "check_contains_space"],
    },
    "utility": {
        "keywords": ["length no space", "without space"],
        "tools": ["get_length_no_spaces"],
    },
}

# Maps every tool name to its category for quick lookup
_TOOL_TO_CATEGORY: dict[str, str] = {}
for _cat, _info in TOOL_CATEGORIES.items():
    for _t in _info["tools"]:
        _TOOL_TO_CATEGORY[_t] = _cat


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


def _filter_tools_by_categories(all_tools: list[dict], categories: list[str]) -> list[dict]:
    """Return only the tools belonging to the given categories."""
    allowed = set()
    for cat in categories:
        if cat in TOOL_CATEGORIES:
            allowed.update(TOOL_CATEGORIES[cat]["tools"])
    return [t for t in all_tools if t["function"]["name"] in allowed]


def _build_compact_tool_list() -> str:
    """Generate a compact one-line-per-tool description for Stage 1 prompts.
    Auto-derived from registered MCP tools so it never goes out of sync."""
    lines = []
    for tool_name, tool_obj in mcp._tool_manager._tools.items():
        desc = (tool_obj.description or "").split("\n")[0].strip().rstrip(".")
        props = tool_obj.parameters.get("properties", {})
        param_str = ", ".join(props.keys())
        lines.append(f"  {tool_name}({param_str}): {desc}")
    return "\n".join(lines)


async def _identify_intent_and_tool(
    client: openai.AsyncOpenAI, question: str, language: str
) -> dict:
    """
    Stage 1: LLM reads the question and returns one of:
      {"action": "tool",   "tool": "<name>", "params": {...}}  — single tool identified
      {"action": "multi"}                                        — needs several tools
      {"action": "direct"}                                       — no tool needed

    Uses a compact prompt (~120 token budget for response) so it stays fast (~3-5s).
    This replaces all regex-based intent routing — the LLM handles all languages.
    """
    tool_list = _build_compact_tool_list()

    prompt = f"""You are a request router for a word-processing API.

Available tools:
{tool_list}

Given the user question, respond with ONLY valid JSON (no markdown, no explanation).
The "action" field MUST be exactly one of: "tool", "multi", or "direct" — never a tool name.

If ONE tool answers it:
{{"action": "tool", "tool": "<tool_name>", "params": {{"<param>": "<value>", "language": "{language}"}}}}

If MULTIPLE tool calls are needed:
{{"action": "multi"}}

If no tool is needed (general knowledge, greetings, non-word-analysis):
{{"action": "direct"}}

Rules:
- "action" must be "tool", "multi", or "direct" — never the tool name itself
- Extract EXACT word/string values from the question as param values
- Language defaults to "{language}" unless question specifies another (telugu/hindi/gujarati/malayalam)
- For Indic script text in the question, set language accordingly
- Prefer "tool" over "multi" whenever a single tool clearly answers the question
- Only use "direct" if the question clearly cannot be answered by any tool above
- "has the letter X", "contains X", "have the letter X" → check_contains_char with char=X

Question: {question}"""

    try:
        response = await client.chat.completions.create(
            model=LLM_MODEL,
            messages=[
                {"role": "system", "content": "Output only valid JSON. No markdown, no explanation."},
                {"role": "user", "content": prompt},
            ],
            max_tokens=120,
            temperature=0.0,
        )
        raw = (response.choices[0].message.content or "").strip()
        # Strip markdown fences if model adds them
        raw = re.sub(r'^```(?:json)?\s*|\s*```$', '', raw, flags=re.MULTILINE).strip()
        logger.info(f"Stage 1 raw: {raw}")

        result = json.loads(raw)
        action = result.get("action", "multi")

        # ── Recovery: Mistral sometimes uses the tool name as the action value ──
        # e.g. {"action": "check_contains_char", "char": "e", "word": "fruit"}
        # Detect this and normalise it into the proper format.
        known_tools = mcp._tool_manager._tools
        if action not in ("tool", "multi", "direct") and action in known_tools:
            # The model flattened the structure — reconstruct it
            params = {k: v for k, v in result.items() if k != "action"}
            logger.info(f"Stage 1 recovery: flat format detected for tool '{action}', params={params}")
            return {"action": "tool", "tool": action, "params": params}

        if action == "tool" and result.get("tool"):
            if result["tool"] in known_tools:
                return result
            logger.warning(f"Stage 1 picked unknown tool '{result['tool']}' — falling back to multi")
            return {"action": "multi"}

        if action == "direct":
            return {"action": "direct"}

        return {"action": "multi"}

    except Exception as e:
        logger.error(f"Stage 1 failed: {e} — defaulting to multi")
        return {"action": "multi"}


def _keyword_match_categories(question: str) -> list[str]:
    """Fast keyword scan to narrow tool categories for the Stage 2 multi-path."""
    q = question.lower()
    matched = []
    for cat, info in TOOL_CATEGORIES.items():
        for kw in info["keywords"]:
            if kw in q:
                matched.append(cat)
                break
    return matched


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
    """Create an OpenAI-compatible client for Gemini, OpenAI, or Ollama."""
    provider = LLM_PROVIDER.lower()
    if provider == "gemini":
        return openai.AsyncOpenAI(
            api_key=GEMINI_API_KEY,
            base_url="https://generativelanguage.googleapis.com/v1beta/openai/",
            timeout=60.0,
        )
    if provider == "ollama":
        base_url = OLLAMA_URL.rstrip("/") + "/v1"
        return openai.AsyncOpenAI(
            api_key="ollama",
            base_url=base_url,
            timeout=120.0,  # Local Ollama: generous for cold starts + tool calls
        )
    return openai.AsyncOpenAI(api_key=OPENAI_API_KEY)


def _format_direct_answer(tool_name: str, params: dict, tool_result: str, question: str) -> str:
    """Convert a direct single-tool result into a human-readable answer."""
    # Parse raw JSON result from the tool
    value = tool_result
    try:
        parsed = json.loads(tool_result)
        # API wraps answer in {success, result} — unwrap it
        if isinstance(parsed, dict):
            # Detect tool execution errors
            if parsed.get("error"):
                return f"Sorry, I couldn't complete that request: {parsed['error']}"
            value = parsed.get("result", parsed.get("data", parsed))
        else:
            value = parsed
    except Exception:
        pass  # keep raw string

    if isinstance(value, str) and value.startswith("API Error:"):
        return f"Sorry, I couldn't complete that: {value}"

    word  = params.get("word",  params.get("source_word", params.get("text", "")))
    word2 = params.get("word2", params.get("target_word", ""))
    lang  = params.get("language", "english")
    lang_note = f" ({lang})" if lang != "english" else ""

    def yn(v): return "Yes" if v else "No"

    answers = {
        "get_text_length":          f'The length of "{word}"{lang_note} is {value} character(s).',
        "reverse_text":             f'The reverse of "{word}" is "{value}".',
        "randomize_text":           f'A scrambled version of "{word}": "{value}".',
        "split_text":               f'Splitting "{word}": {value}',
        "replace_in_text":          f'Result after replacement: "{value}".',
        "get_logical_characters":   f'Logical characters of "{word}"{lang_note}: {value}',
        "get_base_characters":      f'Base characters of "{word}"{lang_note}: {value}',
        "get_code_points":          f'Code points of "{word}": {value}',
        "get_character_at_position": f'Character at position {params.get("index", 0)} in "{word}": "{value}".',
        "parse_to_logical_chars":   f'Logical character components of "{word}"{lang_note}: {value}',
        "check_palindrome":         lambda: f'"{word}" is{" " if value else " not "}a palindrome.',
        "check_anagram":            lambda: f'"{word}" and "{word2}" are{" " if value else " not "}anagrams.',
        "can_make_word":            lambda: f'"{word2}" can{" " if value else "not "}be made from the letters of "{word}".',
        "can_make_all_words":       lambda: f'All words can{" " if value else "not "}be made from "{word}".',
        "get_word_strength":        f'Strength of "{word}": {value}.',
        "get_word_weight":          f'Weight of "{word}": {value}.',
        "get_word_level":           f'Difficulty level of "{word}": {value}.',
        "detect_language":          f'Detected language: {value}.',
        "check_intersecting":       lambda: f'"{word}" and "{word2}" do{" " if value else " not "}share common characters.',
        "get_intersecting_rank":    f'Shared characters between "{word}" and "{word2}": {value}.',
        "check_ladder_words":       lambda: f'"{word}" and "{word2}" are{" " if value else " not "}ladder words (differ by one character).',
        "check_head_tail_words":    lambda: f'"{word}" and "{word2}" are{" " if value else " not "}head-tail words.',
        "check_starts_with":        lambda: f'"{word}" does{" " if value else " not "}start with "{params.get("prefix", "")}".',
        "check_ends_with":          lambda: f'"{word}" does{" " if value else " not "}end with "{params.get("suffix", "")}".',
        "compare_words":            f'Comparison of "{word}" vs "{word2}": {value}.',
        "check_equals":             lambda: f'"{word}" and "{word2}" are{" " if value else " not "}equal.',
        "check_reverse_equals":     lambda: f'The reverse of "{word}" does{" " if value else " not "}equal "{word2}".',
        "find_index_of":            f'"{params.get("search", "")}" found at index {value} in "{word}".',
        "check_contains_char":      lambda: f'"{word}" does{" " if value else " not "}contain the character "{params.get("char", "")}".',
        "check_contains_string":    lambda: f'"{word}" does{" " if value else " not "}contain "{params.get("substring", params.get("search", ""))}".',
        "check_is_consonant":       lambda: f'"{params.get("character", word)}" is{" " if value else " not "}a consonant.',
        "check_is_vowel":           lambda: f'"{params.get("character", word)}" is{" " if value else " not "}a vowel.',
        "check_contains_space":     lambda: f'"{word}" does{" " if value else " not "}contain spaces.',
        "get_length_no_spaces":     f'Length of "{word}" without spaces: {value}.',
    }

    template = answers.get(tool_name)
    if template is None:
        return f"Result: {value}"
    return template() if callable(template) else template


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

    Three-path orchestration:
      Stage 1 — LLM identifies intent + specific tool + params (compact prompt, ~3-5s)
        → "tool"   : fast path — call tool directly, format answer, return (no loop)
        → "direct" : LLM answers from knowledge with no tools
        → "multi"  : keyword-filter tools, run orchestration loop (Stage 2)
    """
    try:
        body = await request.json()
    except Exception:
        return JSONResponse({"error": "Invalid JSON body"}, status_code=400)

    question = (body.get("question") or "").strip()
    language = body.get("language", "english")

    if not question:
        return JSONResponse({"error": "Missing question parameter"}, status_code=400)

    provider = LLM_PROVIDER.lower()
    if provider == "gemini" and not GEMINI_API_KEY:
        return JSONResponse({
            "question": question, "language": language,
            "answer": "Error: GEMINI_API_KEY is not configured. Please set it in mcp_server/.env"
        })
    if provider == "openai" and not OPENAI_API_KEY:
        return JSONResponse({
            "question": question, "language": language,
            "answer": "Error: OPENAI_API_KEY is not configured. Please set it in mcp_server/.env"
        })

    client = _create_llm_client()

    # ── Stage 1: LLM identifies intent + specific tool + extracts params ──────
    intent = await _identify_intent_and_tool(client, question, language)
    logger.info(f"Stage 1 → {intent}")

    # ── Fast path: single tool identified ────────────────────────────────────
    if intent["action"] == "tool":
        tool_name = intent["tool"]
        tool_args = intent.get("params", {})

        # Verify all required params are present; if not, drop to multi-path
        # (Stage 1 sometimes extracts only partial params for multi-arg tools)
        tool_obj = mcp._tool_manager._tools.get(tool_name)
        required = tool_obj.parameters.get("required", []) if tool_obj else []
        missing = [p for p in required if p not in tool_args]
        if missing:
            logger.warning(f"Fast path: missing required params {missing} for {tool_name} — falling to multi")
            intent = {"action": "multi"}
        else:
            logger.info(f"Fast path — {tool_name}({tool_args})")
            tool_result = await _execute_tool(tool_name, tool_args)
            logger.info(f"Tool result: {tool_result[:200]}")
            answer = _format_direct_answer(tool_name, tool_args, tool_result, question)
            return JSONResponse({"question": question, "language": language, "answer": answer})

    # ── Direct path: LLM answers from knowledge, no tools ────────────────────
    if intent["action"] == "direct":
        logger.info("Direct LLM response (no tools)")
        messages = [
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": f"[Language: {language}]\n\n{question}"},
        ]
        try:
            response = await client.chat.completions.create(
                model=LLM_MODEL, messages=messages,
                max_tokens=LLM_MAX_TOKENS, temperature=LLM_TEMPERATURE,
            )
            answer = (response.choices[0].message.content or "").strip()
        except openai.APIError as e:
            answer = f"LLM service error: {str(e)}"
        return JSONResponse({"question": question, "language": language, "answer": answer})

    # ── Multi path: keyword-filter tools, orchestration loop ─────────────────
    all_tools = _build_openai_tools()
    categories = _keyword_match_categories(question)
    if categories:
        filtered_tools = _filter_tools_by_categories(all_tools, categories)
        logger.info(f"Stage 2 multi — {len(filtered_tools)} tools for categories {categories}")
    else:
        filtered_tools = all_tools
        logger.info(f"Stage 2 multi — all {len(all_tools)} tools (no keyword match)")

    messages = [
        {"role": "system", "content": SYSTEM_PROMPT},
        {"role": "user", "content": f"[Language: {language}]\n\n{question}"},
    ]

    max_iterations = 10
    for iteration in range(max_iterations):
        try:
            response = await client.chat.completions.create(
                model=LLM_MODEL,
                messages=messages,
                tools=filtered_tools if filtered_tools else None,
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

        if choice.finish_reason == "stop" or not message.tool_calls:
            answer = (message.content or "").strip()
            logger.info(f"Multi path complete after {iteration + 1} iteration(s)")
            return JSONResponse({"question": question, "language": language, "answer": answer})

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
