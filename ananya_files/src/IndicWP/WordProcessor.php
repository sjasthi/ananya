<?php

namespace IndicWP;

use IndicWP\Parsers\LanguageParserInterface;
use IndicWP\Parsers\TeluguParser;
use IndicWP\Utils\StringUtils;
use IndicWP\Utils\WordAnalyzer;
use IndicWP\Config;

/**
 * Main WordProcessor class for processing Indic language text
 * 
 * This class provides a clean interface for word processing operations
 * while delegating language-specific logic to appropriate parsers.
 */
class WordProcessor
{
    /** @var string The original word being processed */
    protected $word = "";

    /** @var array Array of logical characters */
    protected $logicalChars = array();

    /** @var array 2D array of Unicode code points */
    protected $codePoints = array();

    /** @var string The language of the word */
    protected $language;

    /** @var LanguageParserInterface Language-specific parser */
    protected $parser;

    /**
     * Constructor
     * 
     * @param string $word The word to process
     * @param string $language The language of the word
     */
    public function __construct(string $word, string $language)
    {
        $this->language = $language;
        $this->parser = $this->createParser($language);
        
        if (is_string($word)) {
            $this->setWord($word);
        }
    }

    /**
     * Create appropriate parser for the given language
     * 
     * @param string $language Language name
     * @return LanguageParserInterface Parser instance
     */
    protected function createParser(string $language): LanguageParserInterface
    {
        $parserClass = Config::getParserClass($language);
        $fullClassName = "IndicWP\\Parsers\\{$parserClass}";
        
        if (class_exists($fullClassName)) {
            return new $fullClassName();
        }
        
        // Fallback to Telugu parser
        return new TeluguParser();
    }

    /**
     * Set the word and parse it
     * 
     * @param string $word The word to set
     */
    public function setWord(string $word): void
    {
        if (!is_string($word)) {
            return;
        }

        $this->word = $word;
        $this->parseWord();
    }

    /**
     * Parse the current word into logical characters and code points
     */
    protected function parseWord(): void
    {
        $this->codePoints = $this->parser->parseToLogicalChars($this->word);
        $this->logicalChars = $this->parser->parseToLogicalCharacters($this->codePoints);
    }

    /**
     * Get the original word
     * 
     * @return string The original word
     */
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * Get logical characters
     * 
     * @return array Array of logical characters
     */
    public function getLogicalChars(): array
    {
        return $this->logicalChars;
    }

    /**
     * Get logical characters without invalid characters
     * 
     * @return array Filtered array of logical characters
     */
    public function getLogicalChars2(): array
    {
        return StringUtils::removeInvalidCharacters($this->logicalChars);
    }

    /**
     * Get code points
     * 
     * @return array 2D array of code points
     */
    public function getCodePoints(): array
    {
        return $this->codePoints;
    }

    /**
     * Get length based on logical characters
     * 
     * @return int Number of logical characters
     */
    public function getLength(): int
    {
        return count($this->logicalChars);
    }

    /**
     * Get length without invalid characters
     * 
     * @return int Number of valid logical characters
     */
    public function getLength2(): int
    {
        return count($this->getLogicalChars2());
    }

    /**
     * Get length based on code points
     * 
     * @return int Number of code point groups
     */
    public function getCodePointLength(): int
    {
        return count($this->codePoints);
    }

    /**
     * Get length without spaces
     * 
     * @return int Length excluding spaces
     */
    public function getLengthNoSpaces(): int
    {
        $noSpaces = array_filter($this->logicalChars, function($char) {
            return !StringUtils::isSpace($char);
        });
        return count($noSpaces);
    }

    /**
     * Get length without spaces and commas
     * 
     * @return int Length excluding spaces and commas
     */
    public function getLengthNoSpacesNoCommas(): int
    {
        $filtered = array_filter($this->logicalChars, function($char) {
            return !StringUtils::isSpace($char) && $char !== ',';
        });
        return count($filtered);
    }

    // Word comparison methods

    /**
     * Check if word starts with given characters
     * 
     * @param string $startChars Characters to check
     * @return bool True if starts with given characters
     */
    public function startsWith(string $startChars): bool
    {
        return strpos($this->word, $startChars) === 0;
    }

    /**
     * Check if word ends with given characters
     * 
     * @param string $endChars Characters to check
     * @return bool True if ends with given characters
     */
    public function endsWith(string $endChars): bool
    {
        return substr($this->word, -strlen($endChars)) === $endChars;
    }

    /**
     * Check if word contains a string
     * 
     * @param string $toFind String to find
     * @return bool True if contains the string
     */
    public function containsString(string $toFind): bool
    {
        return strpos($this->word, $toFind) !== false;
    }

    /**
     * Check if word contains a character
     * 
     * @param string $char Character to find
     * @return bool True if contains the character
     */
    public function containsChar(string $char): bool
    {
        return StringUtils::containsChar($this->logicalChars, $char);
    }

    /**
     * Check if word contains logical characters
     * 
     * @param array $toFind Array of characters to find
     * @return bool True if contains all characters
     */
    public function containsLogicalChars(array $toFind): bool
    {
        foreach ($toFind as $char) {
            if (!StringUtils::containsChar($this->logicalChars, $char)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if word contains all logical characters
     * 
     * @param array $toFind Array of characters to find
     * @return bool True if contains all characters
     */
    public function containsAllLogicalChars(array $toFind): bool
    {
        return $this->containsLogicalChars($toFind);
    }

    /**
     * Check if word contains a sequence of logical characters
     * 
     * @param array $sequence Sequence to find
     * @return bool True if contains the sequence
     */
    public function containsLogicalCharSequence(array $sequence): bool
    {
        return StringUtils::containsSequence($this->logicalChars, $sequence);
    }

    /**
     * Check if word contains spaces
     * 
     * @return bool True if contains spaces
     */
    public function containsSpace(): bool
    {
        return WordAnalyzer::containsSpace($this->logicalChars);
    }

    // Word analysis methods

    /**
     * Check if word is a palindrome
     * 
     * @return bool True if palindrome
     */
    public function isPalindrome(): bool
    {
        return WordAnalyzer::isPalindrome($this->logicalChars);
    }

    /**
     * Check if word is an anagram of another word
     * 
     * @param string $word Word to compare with
     * @return bool True if anagram
     */
    public function isAnagram(string $word): bool
    {
        $otherProcessor = new WordProcessor($word, $this->language);
        return WordAnalyzer::areAnagrams($this->logicalChars, $otherProcessor->getLogicalChars());
    }

    /**
     * Check if this word can make another word
     * 
     * @param string $word Word to try to make
     * @return bool True if can make the word
     */
    public function canMakeWord(string $word): bool
    {
        $otherProcessor = new WordProcessor($word, $this->language);
        return WordAnalyzer::canMakeWord($this->logicalChars, $otherProcessor->getLogicalChars());
    }

    /**
     * Check if this word can make all words in a list
     * 
     * @param array $words Array of words to try to make
     * @return bool True if can make all words
     */
    public function canMakeAllWords(array $words): bool
    {
        $wordCharArrays = array();
        foreach ($words as $word) {
            $processor = new WordProcessor($word, $this->language);
            $wordCharArrays[] = $processor->getLogicalChars();
        }
        return WordAnalyzer::canMakeAllWords($this->logicalChars, $wordCharArrays);
    }

    // Word modification methods

    /**
     * Reverse the word
     * 
     * @return string Reversed word
     */
    public function reverse(): string
    {
        $reversed = StringUtils::reverseLogicalChars($this->logicalChars);
        return StringUtils::joinLogicalChars($reversed);
    }

    /**
     * Replace substring in the word
     * 
     * @param string $search String to search for
     * @param string $replace String to replace with
     * @return string Modified word
     */
    public function replace(string $search, string $replace): string
    {
        return str_replace($search, $replace, $this->word);
    }

    /**
     * Add character at specific index
     * 
     * @param int $index Index to add at
     * @param string $char Character to add
     * @return string Modified word
     */
    public function addCharacterAt(int $index, string $char): string
    {
        $newChars = StringUtils::insertCharacterAt($this->logicalChars, $index, $char);
        return StringUtils::joinLogicalChars($newChars);
    }

    /**
     * Add character at the end
     * 
     * @param string $char Character to add
     * @return string Modified word
     */
    public function addCharacterAtEnd(string $char): string
    {
        $newChars = StringUtils::addCharacterAtEnd($this->logicalChars, $char);
        return StringUtils::joinLogicalChars($newChars);
    }

    // Utility methods

    /**
     * Get character at specific index
     * 
     * @param int $index Index to get character from
     * @return string|null Character at index, or null if invalid index
     */
    public function logicalCharAt(int $index): ?string
    {
        return isset($this->logicalChars[$index]) ? $this->logicalChars[$index] : null;
    }

    /**
     * Find index of character
     * 
     * @param string $char Character to find
     * @return int Index of character, or -1 if not found
     */
    public function indexOf(string $char): int
    {
        return StringUtils::indexOf($this->logicalChars, $char);
    }

    /**
     * Split word into chunks
     * 
     * @param int $cols Number of columns per chunk
     * @return array 2D array of chunks
     */
    public function splitWord(int $cols): array
    {
        return StringUtils::splitIntoChunks($this->logicalChars, $cols);
    }

    /**
     * Get word strength
     * 
     * @return int Word strength
     */
    public function getWordStrength(): int
    {
        $isIndic = $this->parser->getLanguageName() !== 'English';
        return WordAnalyzer::getWordStrength($this->codePoints, $isIndic);
    }

    /**
     * Get word weight
     * 
     * @return int Word weight
     */
    public function getWordWeight(): int
    {
        $isIndic = $this->parser->getLanguageName() !== 'English';
        return WordAnalyzer::getWordWeight($this->codePoints, $isIndic);
    }

    /**
     * Get word level
     * 
     * @return int Word level
     */
    public function getWordLevel(): int
    {
        return WordAnalyzer::getWordLevel($this->logicalChars, $this->codePoints);
    }

    /**
     * Compare with another word
     * 
     * @param string $word Word to compare with
     * @return int Comparison result (-1, 0, 1)
     */
    public function compareTo(string $word): int
    {
        $otherProcessor = new WordProcessor($word, $this->language);
        return WordAnalyzer::compareWords($this->logicalChars, $otherProcessor->getLogicalChars());
    }

    /**
     * Compare with another word (case insensitive)
     * 
     * @param string $word Word to compare with
     * @return int Comparison result (-1, 0, 1)
     */
    public function compareToIgnoreCase(string $word): int
    {
        $otherProcessor = new WordProcessor($word, $this->language);
        return WordAnalyzer::compareWords($this->logicalChars, $otherProcessor->getLogicalChars(), true);
    }

    /**
     * Check if equals another word
     * 
     * @param string $word Word to compare with
     * @return bool True if equal
     */
    public function equals(string $word): bool
    {
        return $this->word === $word;
    }

    /**
     * Check if reverse equals another word
     * 
     * @param string $word Word to compare with
     * @return bool True if reverse equals
     */
    public function reverseEquals(string $word): bool
    {
        return $this->reverse() === $word;
    }

    /**
     * String representation
     * 
     * @return string The word
     */
    public function __toString(): string
    {
        return $this->word;
    }
}