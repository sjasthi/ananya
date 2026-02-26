<?php

namespace IndicWP\Parsers;

use IndicWP\Parsers\LanguageParserInterface;

/**
 * Telugu language parser implementation
 * 
 * This class handles parsing and processing of Telugu text according to
 * Telugu script rules and Unicode standards.
 */
class TeluguParser implements LanguageParserInterface
{
    /**
     * Parse a word into logical characters
     * 
     * @param string $word The Telugu word to parse
     * @return array 2D array of logical characters with their code points
     */
    public function parseToLogicalChars(string $word): array
    {
        $wordArray = $this->explodeToCodePoints(json_encode($word));
        $i = 0;
        $logicalChars = array();
        $charBuffer = array();

        while ($i < count($wordArray)) {
            $currentChar = $wordArray[$i++];
            $charBuffer[] = $currentChar;
            
            if ($i == count($wordArray)) {
                $logicalChars[] = $charBuffer;
                continue;
            }
            
            $nextChar = $wordArray[$i];
            
            if ($this->isDependent($nextChar)) {
                $charBuffer[] = $nextChar;
                $i++;
                $logicalChars[] = $charBuffer;
                $charBuffer = array();
                continue;
            }
            
            if ($this->isHalant($currentChar)) {
                if ($this->isConsonant($nextChar) && $i < count($wordArray)) {
                    continue;
                }
                $logicalChars[] = $charBuffer;
                $charBuffer = array();
                continue;
            } elseif ($this->isConsonant($currentChar)) {
                if (($this->isHalant($nextChar) || $this->isDependentVowel($nextChar)) && $i < count($wordArray)) {
                    continue;
                }
                $logicalChars[] = $charBuffer;
                $charBuffer = array();
                continue;
            } elseif ($this->isVowel($currentChar)) {
                if ($this->isDependentVowel($nextChar)) {
                    $charBuffer[] = $nextChar;
                    $i++;
                }
                $logicalChars[] = $charBuffer;
                $charBuffer = array();
                continue;
            }
            
            $logicalChars[] = $charBuffer;
            $charBuffer = array();
        }

        return $logicalChars;
    }

    /**
     * Parse logical characters to displayable characters
     * 
     * @param array $logicalChars Array of logical character code points
     * @return array Array of displayable characters
     */
    public function parseToLogicalCharacters(array $logicalChars): array
    {
        if (is_array($logicalChars)) {
            $result = array();
            foreach ($logicalChars as $logicalChar) {
                $result[] = $this->parseToCharacter($logicalChar);
            }
            return $result;
        }
        
        return array();
    }

    /**
     * Convert logical character code points to displayable character
     * 
     * @param array $logicalChar Array of code points representing one logical character
     * @return string Displayable character
     */
    private function parseToCharacter(array $logicalChar): string
    {
        $teluguChar = "";
        foreach ($logicalChar as $char) {
            if ($this->isLanguageCharacter($char)) {
                $teluguChar .= sprintf("\\u%'04s", dechex($char));
            }
        }
        return json_decode('"' . $teluguChar . '"');
    }

    /**
     * Check if a character is a consonant in Telugu
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if consonant, false otherwise
     */
    public function isConsonant(int $codePoint): bool
    {
        return ($codePoint >= 0x0c15 && $codePoint <= 0x0c39);
    }

    /**
     * Check if a character is a vowel in Telugu
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if vowel, false otherwise
     */
    public function isVowel(int $codePoint): bool
    {
        return ($codePoint >= 0x0c05 && $codePoint <= 0x0c14);
    }

    /**
     * Check if a character is a dependent vowel in Telugu
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if dependent vowel, false otherwise
     */
    public function isDependentVowel(int $codePoint): bool
    {
        return ($codePoint >= 0x0c3e && $codePoint <= 0x0c4c);
    }

    /**
     * Check if a character is a halant (virama) in Telugu
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if halant, false otherwise
     */
    public function isHalant(int $codePoint): bool
    {
        return $codePoint == 0x0c4d;
    }

    /**
     * Check if a character is a dependent character in Telugu
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if dependent, false otherwise
     */
    public function isDependent(int $codePoint): bool
    {
        return ($codePoint == 0x0c01 || $codePoint == 0x0c02 || $codePoint == 0x0c03);
    }

    /**
     * Check if a character is a Telugu number
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if Telugu number, false otherwise
     */
    public function isTeluguNumber(int $codePoint): bool
    {
        return ($codePoint >= 0x0c66 && $codePoint <= 0x0c6f);
    }

    /**
     * Check if a character belongs to Telugu script
     * 
     * @param int $codePoint Unicode code point
     * @return bool True if belongs to Telugu, false otherwise
     */
    public function isLanguageCharacter(int $codePoint): bool
    {
        return ($codePoint >= 0x0c00 && $codePoint <= 0x0c7f) || ($codePoint == 0x200c);
    }

    /**
     * Get the language name
     * 
     * @return string Language name
     */
    public function getLanguageName(): string
    {
        return "Telugu";
    }

    /**
     * Strip spaces from logical characters array while preserving Telugu-specific rules
     * 
     * @param array $logicalChars Array of logical characters
     * @return array Filtered array without spaces
     */
    public function stripSpaces(array $logicalChars): array
    {
        $codePoints = $this->parseToLogicalChars(implode($logicalChars));
        $build = array();
        $buildIndex = 0;
        
        for ($i = 0; $i < count($codePoints); $i++) {
            if (strcmp($logicalChars[$i], " ") == 0) {
                continue;
            }
            
            $build[$buildIndex++] = $logicalChars[$i];
            if ($this->isHalant(end($codePoints[$i])) && $i + 1 < count($codePoints)) {
                if ($codePoints[$i + 1] == 32) { // if the next character is a space...
                    $build[$buildIndex][count($build[$buildIndex])] = json_decode("\u200c");
                }
            }
        }
        
        return $build;
    }

    /**
     * Check if a Telugu character position is blank/undefined
     * 
     * @param string $hexVal Hexadecimal value of the character
     * @return bool True if blank, false otherwise
     */
    public function isBlankTelugu(string $hexVal): bool
    {
        $blankArray = array("c00", "c01", "c02", "c03", "c0d", "c11", "c29", "c34");
        return in_array($hexVal, $blankArray);
    }

    /**
     * Explode a JSON-encoded string into Telugu code points
     * 
     * @param string $toExplode JSON-encoded string to explode
     * @return array Array of Unicode code points
     */
    private function explodeToCodePoints(string $toExplode): array
    {
        $pos = 0;
        $explodedPos = 0;
        $exploded = array();
        
        while ($pos < strlen($toExplode) - 1) {
            if (strcmp($toExplode[$pos], "\"") == 0) {
                $pos++;
                continue;
            }
            
            if (strcmp($toExplode[$pos], "\\") == 0) { // if the character in question is a slash...
                if (strcmp($toExplode[$pos + 1], "u") == 0) { // ...followed by a u...
                    // Convert hex string to number
                    $char = intval(substr($toExplode, $pos + 2, 4), 16);
                    if ($this->isLanguageCharacter($char)) {
                        // if it matches, add it as a character, bump the counter up by six, and continue
                        $exploded[$explodedPos++] = $char;
                        $pos += 6;
                        continue;
                    }
                }
            }
            $exploded[$explodedPos++] = ord($toExplode[$pos++]);
        }

        // Remove hidden char space 8204 (ZWNJ)
        if (($key = array_search('8204', $exploded)) !== false) {
            unset($exploded[$key]);
        }
        $exploded = array_values(array_filter($exploded));
        
        return $exploded;
    }
}