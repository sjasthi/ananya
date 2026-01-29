<?php

namespace IndicWP;

/**
 * Configuration class for IndicWP
 * 
 * Centralized configuration management for the Indic Word Processor
 */
class Config
{
    /**
     * Supported languages and their parser classes
     */
    const SUPPORTED_LANGUAGES = [
        'telugu' => 'TeluguParser',
        'english' => 'TeluguParser', // Using Telugu parser for basic functionality
        'hindi' => 'TeluguParser',    // TODO: Implement HindiParser
        'malayalam' => 'TeluguParser', // TODO: Implement MalayalamParser
        'gujarati' => 'TeluguParser',  // TODO: Implement GujaratiParser
    ];

    /**
     * Default language if none specified
     */
    const DEFAULT_LANGUAGE = 'telugu';

    /**
     * API response cache duration (in seconds)
     */
    const CACHE_DURATION = 7200; // 2 hours

    /**
     * Invalid characters for word processing
     */
    const INVALID_CHARACTERS = [
        "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=",
        "{", "}", "[", "]", ":", ";", "\"", "'", "<", ",", ">", ".", "?",
        "/", "|", "\\", " "
    ];

    /**
     * Maximum word length for processing
     */
    const MAX_WORD_LENGTH = 1000;

    /**
     * Get parser class name for a language
     * 
     * @param string $language Language name
     * @return string Parser class name
     */
    public static function getParserClass(string $language): string
    {
        $language = strtolower(trim($language));
        return self::SUPPORTED_LANGUAGES[$language] ?? self::SUPPORTED_LANGUAGES[self::DEFAULT_LANGUAGE];
    }

    /**
     * Check if a language is supported
     * 
     * @param string $language Language name
     * @return bool True if supported, false otherwise
     */
    public static function isLanguageSupported(string $language): bool
    {
        return array_key_exists(strtolower(trim($language)), self::SUPPORTED_LANGUAGES);
    }

    /**
     * Get list of supported languages
     * 
     * @return array Array of supported language names
     */
    public static function getSupportedLanguages(): array
    {
        return array_keys(self::SUPPORTED_LANGUAGES);
    }

    /**
     * Validate word length
     * 
     * @param string $word Word to validate
     * @return bool True if valid length, false otherwise
     */
    public static function isValidWordLength(string $word): bool
    {
        return strlen($word) <= self::MAX_WORD_LENGTH;
    }
}