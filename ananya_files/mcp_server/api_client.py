"""
HTTP client wrapper for the Ananya PHP Word Processor API.
Makes async HTTP requests to api.php/{category}/{action}?params.
"""

import httpx
from typing import Any, Optional
from config import API_BASE_URL


class AnanyaAPIClient:
    """Async HTTP client for the Ananya word-processing PHP API."""

    def __init__(self, base_url: str = API_BASE_URL):
        self.base_url = base_url.rstrip('/')

    async def _call(self, category: str, action: str, params: dict) -> dict:
        """
        Make a GET request to the PHP API and return the parsed JSON response.
        URL pattern: {base_url}/{category}/{action}?string=...&language=...
        """
        url = f"{self.base_url}/{category}/{action}"
        # Filter out None values
        clean_params = {k: v for k, v in params.items() if v is not None}

        async with httpx.AsyncClient(timeout=15.0) as client:
            try:
                resp = await client.get(url, params=clean_params)
                resp.raise_for_status()
                data = resp.json()
                return data
            except httpx.HTTPStatusError as e:
                return {"success": False, "error": f"HTTP {e.response.status_code}: {e.response.text}"}
            except httpx.RequestError as e:
                return {"success": False, "error": f"Request failed: {str(e)}"}
            except Exception as e:
                return {"success": False, "error": f"Unexpected error: {str(e)}"}

    def _extract_result(self, data: dict) -> Any:
        """Extract the meaningful result from an API response."""
        if data.get("success") is False and data.get("error"):
            return f"API Error: {data['error']}"
        # Prefer 'result' (new format), fall back to 'data' (legacy)
        return data.get("result", data.get("data", data))

    # ── TEXT APIs ────────────────────────────────────────────────────────

    async def text_length(self, string: str, language: str = "english") -> Any:
        data = await self._call("text", "length", {"string": string, "language": language})
        return self._extract_result(data)

    async def text_reverse(self, string: str, language: str = "english") -> Any:
        data = await self._call("text", "reverse", {"string": string, "language": language})
        return self._extract_result(data)

    async def text_randomize(self, string: str, language: str = "english") -> Any:
        data = await self._call("text", "randomize", {"string": string, "language": language})
        return self._extract_result(data)

    async def text_split(self, string: str, delimiter: str = "-", language: str = "english") -> Any:
        data = await self._call("text", "split", {"string": string, "input2": delimiter, "language": language})
        return self._extract_result(data)

    async def text_replace(self, string: str, search: str, replace: str, language: str = "english") -> Any:
        data = await self._call("text", "replace", {
            "string": string, "input2": search, "input3": replace, "language": language
        })
        return self._extract_result(data)

    # ── CHARACTER APIs ──────────────────────────────────────────────────

    async def characters_logical(self, string: str, language: str = "english") -> Any:
        data = await self._call("characters", "logical", {"string": string, "language": language})
        return self._extract_result(data)

    async def characters_base(self, string: str, language: str = "english") -> Any:
        data = await self._call("characters", "base", {"string": string, "language": language})
        return self._extract_result(data)

    async def characters_codepoints(self, string: str, language: str = "english") -> Any:
        data = await self._call("characters", "codepoints", {"string": string, "language": language})
        return self._extract_result(data)

    async def characters_codepoint_length(self, string: str, language: str = "english") -> Any:
        data = await self._call("characters", "codepoint-length", {"string": string, "language": language})
        return self._extract_result(data)

    async def characters_random_logical(self, string: str, count: int = 5, language: str = "english") -> Any:
        data = await self._call("characters", "random-logical", {
            "string": string, "count": str(count), "language": language
        })
        return self._extract_result(data)

    async def characters_logical_at(self, string: str, index: int = 0, language: str = "english") -> Any:
        data = await self._call("characters", "logical-at", {
            "string": string, "input2": str(index), "language": language
        })
        return self._extract_result(data)

    # ── ANALYSIS APIs ───────────────────────────────────────────────────

    async def analysis_is_palindrome(self, string: str, language: str = "english") -> Any:
        data = await self._call("analysis", "is-palindrome", {"string": string, "language": language})
        return self._extract_result(data)

    async def analysis_is_anagram(self, string: str, string2: str, language: str = "english") -> Any:
        data = await self._call("analysis", "is-anagram", {
            "string": string, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def analysis_word_strength(self, string: str, language: str = "english") -> Any:
        data = await self._call("analysis", "word-strength", {"string": string, "language": language})
        return self._extract_result(data)

    async def analysis_word_weight(self, string: str, language: str = "english") -> Any:
        data = await self._call("analysis", "word-weight", {"string": string, "language": language})
        return self._extract_result(data)

    async def analysis_word_level(self, string: str, language: str = "english") -> Any:
        data = await self._call("analysis", "word-level", {"string": string, "language": language})
        return self._extract_result(data)

    async def analysis_can_make_word(self, source: str, target: str, language: str = "english") -> Any:
        data = await self._call("analysis", "can-make-word", {
            "string": source, "input2": target, "language": language
        })
        return self._extract_result(data)

    async def analysis_can_make_all_words(self, source: str, words: str, language: str = "english") -> Any:
        data = await self._call("analysis", "can-make-all-words", {
            "string": source, "input2": words, "language": language
        })
        return self._extract_result(data)

    async def analysis_detect_language(self, string: str) -> Any:
        data = await self._call("analysis", "detect-language", {"string": string, "language": "english"})
        return self._extract_result(data)

    async def analysis_parse_to_logical_chars(self, string: str, language: str = "english") -> Any:
        data = await self._call("analysis", "parse-to-logical-chars", {"string": string, "language": language})
        return self._extract_result(data)

    async def analysis_is_intersecting(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("analysis", "is-intersecting", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def analysis_intersecting_rank(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("analysis", "intersecting-rank", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def analysis_are_ladder_words(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("analysis", "are-ladder-words", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def analysis_are_head_tail_words(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("analysis", "are-head-tail-words", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def analysis_split_into_chunks(self, string: str, language: str = "english") -> Any:
        data = await self._call("analysis", "split-into-chunks", {"string": string, "language": language})
        return self._extract_result(data)

    async def analysis_get_match_id_string(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("analysis", "get-match-id-string", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    # ── COMPARISON APIs ─────────────────────────────────────────────────

    async def comparison_equals(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("comparison", "equals", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def comparison_starts_with(self, string: str, prefix: str, language: str = "english") -> Any:
        data = await self._call("comparison", "starts-with", {
            "string": string, "input2": prefix, "language": language
        })
        return self._extract_result(data)

    async def comparison_ends_with(self, string: str, suffix: str, language: str = "english") -> Any:
        data = await self._call("comparison", "ends-with", {
            "string": string, "input2": suffix, "language": language
        })
        return self._extract_result(data)

    async def comparison_compare(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("comparison", "compare", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def comparison_reverse_equals(self, string1: str, string2: str, language: str = "english") -> Any:
        data = await self._call("comparison", "reverse-equals", {
            "string": string1, "input2": string2, "language": language
        })
        return self._extract_result(data)

    async def comparison_index_of(self, string: str, search: str, language: str = "english") -> Any:
        data = await self._call("comparison", "index-of", {
            "string": string, "input2": search, "language": language
        })
        return self._extract_result(data)

    # ── VALIDATION APIs ─────────────────────────────────────────────────

    async def validation_contains_space(self, string: str, language: str = "english") -> Any:
        data = await self._call("validation", "contains-space", {"string": string, "language": language})
        return self._extract_result(data)

    async def validation_contains_char(self, string: str, char: str, language: str = "english") -> Any:
        data = await self._call("validation", "contains-char", {
            "string": string, "input2": char, "language": language
        })
        return self._extract_result(data)

    async def validation_contains_string(self, string: str, substring: str, language: str = "english") -> Any:
        data = await self._call("validation", "contains-string", {
            "string": string, "input2": substring, "language": language
        })
        return self._extract_result(data)

    async def validation_is_consonant(self, string: str, language: str = "english") -> Any:
        data = await self._call("validation", "is-consonant", {"string": string, "language": language})
        return self._extract_result(data)

    async def validation_is_vowel(self, string: str, language: str = "english") -> Any:
        data = await self._call("validation", "is-vowel", {"string": string, "language": language})
        return self._extract_result(data)

    async def validation_contains_logical_chars(self, string: str, chars: str, language: str = "english") -> Any:
        data = await self._call("validation", "contains-logical-chars", {
            "string": string, "input2": chars, "language": language
        })
        return self._extract_result(data)

    async def validation_contains_all_logical_chars(self, string: str, chars: str, language: str = "english") -> Any:
        data = await self._call("validation", "contains-all-logical-chars", {
            "string": string, "input2": chars, "language": language
        })
        return self._extract_result(data)

    # ── UTILITY APIs ────────────────────────────────────────────────────

    async def utility_length_no_spaces(self, string: str, language: str = "english") -> Any:
        data = await self._call("utility", "length-no-spaces", {"string": string, "language": language})
        return self._extract_result(data)

    async def utility_length_no_spaces_commas(self, string: str, language: str = "english") -> Any:
        data = await self._call("utility", "length-no-spaces-commas", {"string": string, "language": language})
        return self._extract_result(data)
