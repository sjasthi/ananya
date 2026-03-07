# Ananya API Reference

A comprehensive REST API for processing Telugu and English text, providing advanced linguistic analysis and manipulation capabilities.

## 🚀 Quick Start

### Base URL

```text
Local Development: http://localhost/ananya/api.php/
Production: https://ananya.telugupuzzles.com/api.php/
```

### Request Format

```text
GET {base_url}/{category}/{action}?string={text}&language={lang}
```

### API Categories

- **text** - Text operations (length, reverse, randomize, replace, split)
- **characters** - Character analysis (base, logical, codepoints, add-at, add-end)
- **analysis** - Advanced analysis (word-strength, palindrome, anagram, intersecting, is-consonant)
- **comparison** - String comparison (equals, starts-with, ends-with, compare)
- **validation** - Content validation (contains-space, contains-char)
- **utility** - Utility functions (length variations, parsing functions)

### Response Format

All APIs return JSON responses:

```json
{
  "response_code": 200,
  "message": "Operation completed successfully",
  "string": "input_string", 
  "language": "telugu",
  "data": "result_data"
}
```

## 📊 API Endpoints

### Text Operations

#### Get Text Length

Returns the logical length of a string.

```text
GET /text/length?string={text}&language={lang}
```

**Example:**

```bash
curl "http://localhost/ananya/api.php/text/length?string=కండలు&language=telugu"
```

#### Reverse Text

Reverses the logical characters in a string.

```text
GET /text/reverse?string={text}&language={lang}
```

#### Randomize Text

Randomizes the order of logical characters.

```text
GET /text/randomize?string={text}&language={lang}
```

#### Replace Text

Replaces occurrences of a substring.

```text
GET /text/replace?string={text}&language={lang}&input2={find}&input3={replace}
```

#### Split Text

Splits text into specified number of columns.

```text
GET /text/split?string={text}&language={lang}&input2={columns}
```

### Character Analysis

#### Get Base Characters

Returns the base characters of a string.

```text
GET /characters/base?string={text}&language={lang}
```

#### Get Logical Characters

Returns logical character breakdown.

```text
GET /characters/logical?string={text}&language={lang}
```

#### Get Code Points

Returns Unicode code points.

```text
GET /characters/codepoints?string={text}&language={lang}
```

#### Get Code Point Length

Returns the number of code points.

```text
GET /characters/codepoint-length?string={text}&language={lang}
```

#### Add Character At Position

Adds a character at specified position.

```text
GET /characters/add-at?string={text}&language={lang}&input2={char}&input3={position}
```

#### Add Character At End

Adds a character at the end.

```text
GET /characters/add-end?string={text}&language={lang}&input2={char}
```

#### Get Logical Character At Position

Gets logical character at specific position.

```text
GET /characters/logical-at?string={text}&language={lang}&input2={position}
```

### Analysis Operations

#### Check Palindrome

Checks if text is a palindrome.

```text
GET /analysis/is-palindrome?string={text}&language={lang}
```

#### Get Word Strength

Calculates word strength metric.

```text
GET /analysis/word-strength?string={text}&language={lang}
```

#### Get Word Weight

Calculates word weight metric.

```text
GET /analysis/word-weight?string={text}&language={lang}
```

#### Get Word Level

Determines word complexity level.

```text
GET /analysis/word-level?string={text}&language={lang}
```

#### Check Anagram

Checks if two strings are anagrams.

```text
GET /analysis/is-anagram?string={text1}&language={lang}&input2={text2}
```

#### Can Make Word

Checks if first string can make second string.

```text
GET /analysis/can-make-word?string={text1}&language={lang}&input2={text2}
```

### Comparison Operations

#### Check Equality

Checks if two strings are equal.

```text
GET /comparison/equals?string={text1}&language={lang}&input2={text2}
```

#### Check Starts With

Checks if string starts with substring.

```text
GET /comparison/starts-with?string={text}&language={lang}&input2={prefix}
```

#### Check Ends With

Checks if string ends with substring.

```text
GET /comparison/ends-with?string={text}&language={lang}&input2={suffix}
```

#### Compare Strings

Compares two strings lexicographically.

```text
GET /comparison/compare?string={text1}&language={lang}&input2={text2}
```

### Validation Operations

#### Contains Space

Checks if string contains spaces.

```text
GET /validation/contains-space?string={text}&language={lang}
```

#### Contains Character

Checks if string contains specific character.

```text
GET /validation/contains-char?string={text}&language={lang}&input2={char}
```

#### Contains String

Checks if string contains substring.

```text
GET /validation/contains-string?string={text}&language={lang}&input2={substring}
```

#### Is Consonant

Checks if character is a consonant.

```text
GET /analysis/is-consonant?string={text}&language={lang}
```

#### Is Vowel

Checks if character is a vowel.

```text
GET /validation/is-vowel?string={text}&language={lang}&input2={char}
```

### Utility Operations

#### Length No Spaces

Gets length excluding spaces.

```text
GET /utility/length-no-spaces?string={text}&language={lang}
```

#### Length No Spaces or Commas

Gets length excluding spaces and commas.

```text
GET /utility/length-no-spaces-commas?string={text}&language={lang}
```

#### Alternative Length

Gets alternative length calculation.

```text
GET /utility/length-alternative?string={text}&language={lang}
```

## 🔧 Usage Examples

### Telugu Text Processing

```bash
# Get length of Telugu word
curl "http://localhost/ananya/api.php/text/length?string=కండలు&language=telugu"

# Get base characters
curl "http://localhost/ananya/api.php/characters/base?string=కండలు&language=telugu"

# Check if palindrome
curl "http://localhost/ananya/api.php/analysis/is-palindrome?string=కక&language=telugu"
```

### English Text Processing

```bash
# Reverse English text
curl "http://localhost/ananya/api.php/text/reverse?string=hello&language=english"

# Check anagram
curl "http://localhost/ananya/api.php/analysis/is-anagram?string=listen&language=english&input2=silent"
```

## 📝 Notes

- All `string` and `language` parameters are required
- Use `input2`, `input3` for additional parameters as needed
- Language values: `telugu`, `english`
- All responses are in JSON format
- HTTP status codes indicate success (200) or errors (400, 404, 500)

## 🚀 Status

Currently **52%** of all planned endpoints are fully functional and tested.
