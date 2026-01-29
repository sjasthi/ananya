<?php

namespace IndicWP\Utils;

/**
 * String utility functions for Indic word processing
 * 
 * This class contains common string manipulation functions that are
 * language-agnostic and can be used across different parsers.
 */
class StringUtils
{
    /**
     * Check if a character is a symbol (non-alphanumeric)
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if symbol, false otherwise
     */
    public static function isSymbol(int $codePoint): bool
    {
        return (($codePoint > 32 && $codePoint < 48) || 
                ($codePoint > 57 && $codePoint < 65) ||
                ($codePoint > 90 && $codePoint < 97) || 
                ($codePoint > 122 && $codePoint < 127));
    }

    /**
     * Check if a character is a space
     * 
     * @param string $char Character to check
     * @return bool True if space, false otherwise
     */
    public static function isSpace(string $char): bool
    {
        return strcmp($char, " ") === 0;
    }

    /**
     * Reverse an array of logical characters
     * 
     * @param array $logicalChars Array of logical characters
     * @return array Reversed array
     */
    public static function reverseLogicalChars(array $logicalChars): array
    {
        return array_reverse($logicalChars);
    }

    /**
     * Join logical characters into a string
     * 
     * @param array $logicalChars Array of logical characters
     * @param string $separator Separator to use (default: empty string)
     * @return string Joined string
     */
    public static function joinLogicalChars(array $logicalChars, string $separator = ""): string
    {
        return implode($separator, $logicalChars);
    }

    /**
     * Remove invalid characters from logical characters array
     * 
     * @param array $logicalChars Array of logical characters
     * @return array Filtered array
     */
    public static function removeInvalidCharacters(array $logicalChars): array
    {
        $invalidCharacters = array(
            "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=", 
            "{", "}", "[", "]", ":", ";", "\"", "'", "<", ",", ">", ".", "?", 
            "/", "|", "\\", " "
        );
        
        $filtered = array();
        foreach ($logicalChars as $char) {
            if (!in_array($char, $invalidCharacters)) {
                $filtered[] = $char;
            }
        }
        
        return $filtered;
    }

    /**
     * Strip all symbols from logical characters array
     * 
     * @param array $logicalChars Array of logical characters
     * @param array $codePoints Array of corresponding code points
     * @return array Filtered array without symbols
     */
    public static function stripSymbols(array $logicalChars, array $codePoints): array
    {
        $build = array();
        
        for ($i = 0; $i < count($logicalChars); $i++) {
            if (isset($codePoints[$i][0])) {
                $chr = $codePoints[$i][0];
                if (!self::isSymbol($chr)) {
                    $build[] = $logicalChars[$i];
                }
            }
        }
        
        return $build;
    }

    /**
     * Insert a character at a specific index in logical characters array
     * 
     * @param array $logicalChars Array of logical characters
     * @param int $index Index to insert at
     * @param string $char Character to insert
     * @return array Modified array
     */
    public static function insertCharacterAt(array $logicalChars, int $index, string $char): array
    {
        if ($index >= count($logicalChars)) {
            $logicalChars[] = $char;
            return $logicalChars;
        }
        
        array_splice($logicalChars, $index, 0, $char);
        return $logicalChars;
    }

    /**
     * Add character at the end of logical characters array
     * 
     * @param array $logicalChars Array of logical characters
     * @param string $char Character to add
     * @return array Modified array
     */
    public static function addCharacterAtEnd(array $logicalChars, string $char): array
    {
        $logicalChars[] = $char;
        return $logicalChars;
    }

    /**
     * Split a word into chunks of specified column width
     * 
     * @param array $logicalChars Array of logical characters
     * @param int $cols Number of columns per chunk
     * @return array 2D array of chunks
     */
    public static function splitIntoChunks(array $logicalChars, int $cols): array
    {
        if ($cols <= 0) {
            return array($logicalChars);
        }
        
        return array_chunk($logicalChars, $cols);
    }

    /**
     * Find the index of a character in logical characters array
     * 
     * @param array $logicalChars Array to search in
     * @param string $char Character to find
     * @return int Index of character, or -1 if not found
     */
    public static function indexOf(array $logicalChars, string $char): int
    {
        $index = array_search($char, $logicalChars);
        return $index !== false ? $index : -1;
    }

    /**
     * Check if logical characters array contains a specific character
     * 
     * @param array $logicalChars Array to search in
     * @param string $char Character to find
     * @return bool True if found, false otherwise
     */
    public static function containsChar(array $logicalChars, string $char): bool
    {
        return in_array($char, $logicalChars);
    }

    /**
     * Check if logical characters array contains a sequence of characters
     * 
     * @param array $logicalChars Array to search in
     * @param array $sequence Sequence to find
     * @return bool True if sequence found, false otherwise
     */
    public static function containsSequence(array $logicalChars, array $sequence): bool
    {
        $haystack = implode('|', $logicalChars);
        $needle = implode('|', $sequence);
        return strpos($haystack, $needle) !== false;
    }

    /**
     * Randomize an array of strings
     * 
     * @param array $strings Array of strings to randomize
     * @return array Shuffled array
     */
    public static function randomize(array $strings): array
    {
        $shuffled = $strings;
        shuffle($shuffled);
        return $shuffled;
    }
}