<?php

namespace IndicWP\Utils;

/**
 * Word analysis utility functions for Indic languages
 * 
 * This class provides methods for analyzing words for linguistic properties
 * such as strength, weight, palindromes, anagrams, etc.
 */
class WordAnalyzer
{
    /**
     * Calculate word strength (maximum character complexity)
     * 
     * @param array $codePoints 2D array of code points
     * @param bool $isIndicLanguage Whether the word is in an Indic language
     * @return int Word strength
     */
    public static function getWordStrength(array $codePoints, bool $isIndicLanguage = true): int
    {
        if (!$isIndicLanguage) {
            return count($codePoints);
        }

        $strength = 1;
        foreach ($codePoints as $char) {
            $charComplexity = count($char);
            if ($charComplexity > $strength) {
                $strength = $charComplexity;
            }
        }

        return $strength;
    }

    /**
     * Calculate word weight (total character complexity)
     * 
     * @param array $codePoints 2D array of code points
     * @param bool $isIndicLanguage Whether the word is in an Indic language
     * @return int Word weight
     */
    public static function getWordWeight(array $codePoints, bool $isIndicLanguage = true): int
    {
        if (!$isIndicLanguage) {
            return count($codePoints);
        }

        $weight = 0;
        foreach ($codePoints as $char) {
            $weight += count($char);
        }

        return $weight;
    }

    /**
     * Check if a word is a palindrome
     * 
     * @param array $logicalChars Array of logical characters
     * @return bool True if palindrome, false otherwise
     */
    public static function isPalindrome(array $logicalChars): bool
    {
        $reversed = array_reverse($logicalChars);
        return $logicalChars === $reversed;
    }

    /**
     * Check if two words are anagrams
     * 
     * @param array $word1Chars Logical characters of first word
     * @param array $word2Chars Logical characters of second word
     * @return bool True if anagrams, false otherwise
     */
    public static function areAnagrams(array $word1Chars, array $word2Chars): bool
    {
        if (count($word1Chars) !== count($word2Chars)) {
            return false;
        }

        $sorted1 = $word1Chars;
        $sorted2 = $word2Chars;
        sort($sorted1);
        sort($sorted2);

        return $sorted1 === $sorted2;
    }

    /**
     * Check if a word contains spaces
     * 
     * @param array $logicalChars Array of logical characters
     * @return bool True if contains spaces, false otherwise
     */
    public static function containsSpace(array $logicalChars): bool
    {
        return in_array(" ", $logicalChars);
    }

    /**
     * Check if one word can be made from another word's characters
     * 
     * @param array $availableChars Characters available to use
     * @param array $targetChars Characters needed to make the word
     * @return bool True if word can be made, false otherwise
     */
    public static function canMakeWord(array $availableChars, array $targetChars): bool
    {
        $availableCounts = array_count_values($availableChars);
        $targetCounts = array_count_values($targetChars);

        foreach ($targetCounts as $char => $neededCount) {
            $availableCount = isset($availableCounts[$char]) ? $availableCounts[$char] : 0;
            if ($availableCount < $neededCount) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if all words in a list can be made from available characters
     * 
     * @param array $availableChars Characters available to use
     * @param array $wordsList List of words (each word is an array of characters)
     * @return bool True if all words can be made, false otherwise
     */
    public static function canMakeAllWords(array $availableChars, array $wordsList): bool
    {
        foreach ($wordsList as $word) {
            if (!self::canMakeWord($availableChars, $word)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get intersecting rank between two words
     * 
     * @param array $word1Chars Logical characters of first word
     * @param array $word2Chars Logical characters of second word
     * @return int Number of common characters
     */
    public static function getIntersectingRank(array $word1Chars, array $word2Chars): int
    {
        $intersection = array_intersect($word1Chars, $word2Chars);
        return count($intersection);
    }

    /**
     * Check if two words are intersecting (have common characters)
     * 
     * @param array $word1Chars Logical characters of first word
     * @param array $word2Chars Logical characters of second word
     * @return bool True if intersecting, false otherwise
     */
    public static function areIntersecting(array $word1Chars, array $word2Chars): bool
    {
        return self::getIntersectingRank($word1Chars, $word2Chars) > 0;
    }

    /**
     * Get unique intersecting logical characters between a word and a list of words
     * 
     * @param array $wordChars Logical characters of the main word
     * @param array $wordsList List of words to compare against
     * @return array Unique intersecting characters
     */
    public static function getUniqueIntersectingLogicalChars(array $wordChars, array $wordsList): array
    {
        $intersectingChars = array();
        
        foreach ($wordsList as $word) {
            $intersection = array_intersect($wordChars, $word);
            $intersectingChars = array_merge($intersectingChars, $intersection);
        }
        
        return array_unique($intersectingChars);
    }

    /**
     * Get unique intersecting rank (count of unique common characters)
     * 
     * @param array $wordChars Logical characters of the main word
     * @param array $wordsList List of words to compare against
     * @return int Count of unique intersecting characters
     */
    public static function getUniqueIntersectingRank(array $wordChars, array $wordsList): int
    {
        $uniqueChars = self::getUniqueIntersectingLogicalChars($wordChars, $wordsList);
        return count($uniqueChars);
    }

    /**
     * Check if two words are ladder words (differ by exactly one character)
     * 
     * @param array $word1Chars Logical characters of first word
     * @param array $word2Chars Logical characters of second word
     * @return bool True if ladder words, false otherwise
     */
    public static function areLadderWords(array $word1Chars, array $word2Chars): bool
    {
        if (count($word1Chars) !== count($word2Chars)) {
            return false;
        }

        $differences = 0;
        for ($i = 0; $i < count($word1Chars); $i++) {
            if ($word1Chars[$i] !== $word2Chars[$i]) {
                $differences++;
                if ($differences > 1) {
                    return false;
                }
            }
        }

        return $differences === 1;
    }

    /**
     * Check if two words are head and tail words (first and last chars match opposite positions)
     * 
     * @param array $word1Chars Logical characters of first word
     * @param array $word2Chars Logical characters of second word
     * @return bool True if head and tail words, false otherwise
     */
    public static function areHeadAndTailWords(array $word1Chars, array $word2Chars): bool
    {
        if (empty($word1Chars) || empty($word2Chars)) {
            return false;
        }

        $word1First = $word1Chars[0];
        $word1Last = end($word1Chars);
        $word2First = $word2Chars[0];
        $word2Last = end($word2Chars);

        return ($word1First === $word2Last) && ($word1Last === $word2First);
    }

    /**
     * Compare two words lexicographically
     * 
     * @param array $word1Chars Logical characters of first word
     * @param array $word2Chars Logical characters of second word
     * @param bool $ignoreCase Whether to ignore case
     * @return int -1 if word1 < word2, 0 if equal, 1 if word1 > word2
     */
    public static function compareWords(array $word1Chars, array $word2Chars, bool $ignoreCase = false): int
    {
        $word1Str = implode('', $word1Chars);
        $word2Str = implode('', $word2Chars);

        if ($ignoreCase) {
            $word1Str = strtolower($word1Str);
            $word2Str = strtolower($word2Str);
        }

        return strcmp($word1Str, $word2Str);
    }

    /**
     * Calculate word level based on complexity (implementation can be customized)
     * 
     * @param array $logicalChars Array of logical characters
     * @param array $codePoints 2D array of code points
     * @return int Word level
     */
    public static function getWordLevel(array $logicalChars, array $codePoints): int
    {
        $length = count($logicalChars);
        $weight = self::getWordWeight($codePoints);
        $strength = self::getWordStrength($codePoints);

        // Simple calculation - can be made more sophisticated
        return intval(($length + $weight + $strength) / 3);
    }
}