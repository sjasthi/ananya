<?php

namespace IndicWP\Parsers;

/**
 * Interface for language-specific parsers
 * 
 * This interface standardizes the methods that all language parsers must implement
 * to ensure consistent handling across different Indic languages.
 */
interface LanguageParserInterface
{
    /**
     * Parse a word into logical characters
     * 
     * @param string $word The word to parse
     * @return array 2D array of logical characters with their code points
     */
    public function parseToLogicalChars(string $word): array;

    /**
     * Parse logical characters to displayable characters
     * 
     * @param array $logicalChars Array of logical character code points
     * @return array Array of displayable characters
     */
    public function parseToLogicalCharacters(array $logicalChars): array;

    /**
     * Check if a character is a consonant in this language
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if consonant, false otherwise
     */
    public function isConsonant(int $codePoint): bool;

    /**
     * Check if a character is a vowel in this language
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if vowel, false otherwise
     */
    public function isVowel(int $codePoint): bool;

    /**
     * Check if a character is a dependent vowel in this language
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if dependent vowel, false otherwise
     */
    public function isDependentVowel(int $codePoint): bool;

    /**
     * Check if a character is a halant (virama) in this language
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if halant, false otherwise
     */
    public function isHalant(int $codePoint): bool;

    /**
     * Check if a character belongs to this language's script
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if belongs to this language, false otherwise
     */
    public function isLanguageCharacter(int $codePoint): bool;

    /**
     * Get the language name
     * 
     * @return string Language name (e.g., "Telugu", "Hindi", etc.)
     */
    public function getLanguageName(): string;

    /**
     * Strip spaces from logical characters array while preserving language-specific rules
     * 
     * @param array $logicalChars Array of logical characters
     * @return array Filtered array without spaces
     */
    public function stripSpaces(array $logicalChars): array;
}