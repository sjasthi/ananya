# Refactored Indic Word Processor

This document describes the refactored structure of the Indic Word Processor project.

## New Structure

```
src/
├── IndicWP/
    ├── WordProcessor.php           # Main WordProcessor class
    ├── Parsers/
    │   ├── LanguageParserInterface.php  # Interface for language parsers
    │   └── TeluguParser.php            # Telugu language parser
    └── Utils/
        ├── StringUtils.php             # String manipulation utilities
        └── WordAnalyzer.php            # Word analysis utilities
```

## Key Improvements

### 1. **Separation of Concerns**
- **WordProcessor**: Core word processing functionality
- **Parsers**: Language-specific parsing logic
- **Utils**: Reusable utility functions

### 2. **Interface-Driven Design**
- `LanguageParserInterface` standardizes language parser implementations
- Easy to add new languages by implementing the interface

### 3. **PSR-4 Autoloading**
- Follows PHP standards for class loading
- No more manual `require` statements

### 4. **Improved Code Organization**
- Reduced monolithic files (WordProcessor: 1514 lines → ~350 lines)
- Better maintainability and testability
- Clear namespace structure

## Usage

### Basic Usage

```php
<?php
require 'autoload.php';

use IndicWP\WordProcessor;

// Create a word processor for Telugu
$processor = new WordProcessor('అమ్మ', 'Telugu');

// Get word properties
echo $processor->getLength();        // Logical character count
echo $processor->getWordWeight();    // Word complexity weight
echo $processor->reverse();          // Reversed word
echo $processor->isPalindrome();     // Check if palindrome
```

### API Integration

```php
<?php
require '../autoload.php';

use IndicWP\WordProcessor;

// In your API endpoint
$processor = new WordProcessor($_GET['string'], $_GET['language']);
$result = $processor->getLength();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['data' => $result]);
```

## Migration Guide

### From Old Structure
```php
// Old way
require("word_processor.php");
$wp = new wordProcessor($word, $language);
```

### To New Structure
```php
// New way
require("autoload.php");
use IndicWP\WordProcessor;
$wp = new WordProcessor($word, $language);
```

## Benefits

1. **Maintainability**: Smaller, focused classes are easier to maintain
2. **Extensibility**: Easy to add new languages and features
3. **Testability**: Individual components can be tested in isolation
4. **Performance**: Better memory usage and loading performance
5. **Standards Compliance**: Follows PHP PSR-4 standards

## Testing

Use the provided test file to verify functionality:

```bash
# Access via browser
http://localhost/wpapi/test_refactored.php

# Or run specific API endpoint
http://localhost/wpapi/api/getLength_refactored.php?string=అమ్మ&language=Telugu
```

## Future Enhancements

1. **Additional Language Parsers**: Hindi, Malayalam, Gujarati parsers
2. **Caching**: Implement result caching for better performance
3. **Validation**: Input validation and sanitization
4. **Error Handling**: Comprehensive error handling and logging
5. **Unit Tests**: Complete test coverage
6. **Documentation**: API documentation generation

## Backward Compatibility

The original files are preserved, so existing integrations continue to work. The refactored version is available alongside the original implementation for gradual migration.