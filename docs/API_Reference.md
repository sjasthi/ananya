# Indic Language Word Processor API Reference

A comprehensive REST API for processing Telugu and English text, providing advanced linguistic analysis and manipulation capabilities.

## 🚀 Quick Start

### Base URL
```
https://ananya.telugupuzzles.com/api.php/
```

### Basic Request Format
```
GET https://ananya.telugupuzzles.com/api.php/{category}/{action}?string={text}&language={lang}
```

### Response Format
All APIs return JSON responses with this structure:
```json
{
  "response_code": 200,
  "message": "Operation completed successfully",
  "string": "input_string",
  "language": "English",
  "data": "result_data"
}
```

## 📊 API Categories

- **String Operations** - Basic string manipulation and processing
- **Character Analysis** - Character-level analysis and processing  
- **String Comparison** - String comparison and matching operations
- **String Search** - String searching and pattern matching
- **Word Analysis** - Word-level analysis and metrics
- **Language Processing** - Language detection and language-specific operations
- **User Management** - User authentication and management

## 🔧 String Operations

### Get Length
Returns the logical length of a string.

**Endpoint:** `GET /text/length`

**Parameters:**
- `string` (required) - Input string
- `language` (required) - Language (English/Telugu)

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/getLength.php?string=hello&language=English"
```

**Response:**
```json
{
  "response_code": 200,
  "message": "Length Calculated",
  "string": "hello",
  "language": "English",
  "data": 5
}
```

### Reverse String
Returns the reverse of the input string.

**Endpoint:** `GET /reverse.php`

**Parameters:**
- `string` (required) - Input string
- `language` (required) - Language (English/Telugu)

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/reverse.php?string=hello&language=English"
```

**Response:**
```json
{
  "response_code": 200,
  "message": "String Reversed",
  "string": "hello",
  "language": "English",
  "data": "olleh"
}
```

### Randomize String
Returns the input string with characters in random order.

**Endpoint:** `GET /randomize.php`

**Parameters:**
- `string` (required) - Input string
- `language` (required) - Language (English/Telugu)

### Replace Substring
Replaces occurrences of a substring with another string.

**Endpoint:** `GET /replace.php`

**Parameters:**
- `input1` (required) - Original string
- `input2` (required) - Language
- `input3` (required) - String to replace
- `input4` (required) - Replacement string

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/replace.php?input1=hello&input2=English&input3=ell&input4=i"
```

### Get Length (No Spaces)
Returns string length ignoring space characters.

**Endpoint:** `GET /getLengthNoSpaces.php`

### Get Length (No Spaces/Commas)
Returns string length ignoring spaces and commas.

**Endpoint:** `GET /getLengthNoSpacesNoCommas.php`

## 🔍 Character Analysis

### Get Code Point Length
Returns the number of Unicode code points in the string.

**Endpoint:** `GET /getCodePointLength.php`

**Parameters:**
- `string` (required) - Input string
- `language` (required) - Language (English/Telugu)

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/getCodePointLength.php?string=అమెరికా&language=Telugu"
```

### Get Code Points
Returns an array of Unicode code points for each character.

**Endpoint:** `GET /getCodePoints.php`

**Example Response:**
```json
{
  "response_code": 200,
  "message": "Code Points Retrieved",
  "string": "hello",
  "language": "English",
  "data": [[104], [101], [108], [108], [111]]
}
```

### Get Logical Characters
Returns an array of logical characters from the string.

**Endpoint:** `GET /getLogicalChars.php`

**Example Response:**
```json
{
  "response_code": 200,
  "message": "Logical Characters Retrieved",
  "string": "hello",
  "language": "English",
  "data": ["h", "e", "l", "l", "o"]
}
```

### Get Logical Character At Position
Returns the logical character at the specified position.

**Endpoint:** `GET /logicalCharAt.php`

**Parameters:**
- `input1` (required) - Input string
- `input2` (required) - Language
- `input3` (required) - Position index (0-based)

## ⚖️ String Comparison

### Check String Equality
Checks if two strings are exactly equal.

**Endpoint:** `GET /equals.php`

**Parameters:**
- `input1` (required) - First string
- `input2` (required) - Language
- `input3` (required) - Second string

### Compare Strings
Compares two strings lexicographically.

**Endpoint:** `GET /compareTo.php`

### Compare Strings (Case Insensitive)
Compares two strings ignoring case differences.

**Endpoint:** `GET /compareToIgnoreCase.php`

### Is Anagram
Determines if two strings are anagrams of each other.

**Endpoint:** `GET /isAnagram.php`

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/isAnagram.php?input1=listen&input2=English&input3=silent"
```

**Response:**
```json
{
  "response_code": 200,
  "message": "Anagram Assessed",
  "string": "listen",
  "language": "English",
  "data": true
}
```

### Is Palindrome
Determines if a string reads the same forwards and backwards.

**Endpoint:** `GET /isPalindrome.php`

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/isPalindrome.php?string=racecar&language=English"
```

### Reverse Equals
Checks if the second string equals the reverse of the first.

**Endpoint:** `GET /reverseEquals.php`

## 🔎 String Search

### Starts With
Checks if the string begins with the specified character.

**Endpoint:** `GET /startsWith.php`

**Parameters:**
- `input1` (required) - Input string
- `input2` (required) - Language
- `input3` (required) - Character to check

### Ends With
Checks if the string ends with the specified character.

**Endpoint:** `GET /endsWith.php`

### Contains String
Checks if the first string contains the second string.

**Endpoint:** `GET /containsString.php`

### Contains Character
Checks if the string contains the specified character.

**Endpoint:** `GET /containsChar.php`

### Contains Space
Checks if the string contains any space characters.

**Endpoint:** `GET /containsSpace.php`

### Find Character Index
Returns the zero-based index of the first occurrence of a character.

**Endpoint:** `GET /indexOf.php`

### Contains Logical Characters
Checks if any of the specified characters are in the string.

**Endpoint:** `GET /containsLogicalChars.php`

### Contains All Logical Characters
Checks if all of the specified characters are in the string.

**Endpoint:** `GET /containsAllLogicalChars.php`

### Contains Logical Character Sequence
Checks if the second string is a substring of the first.

**Endpoint:** `GET /containsLogicalCharSequence.php`

## 📈 Word Analysis

### Get Word Level
Returns the word level as an integer value.

**Endpoint:** `GET /getWordLevel.php`

### Get Word Strength
Returns the word strength as an integer value.

**Endpoint:** `GET /getWordStrength.php`

### Get Word Weight
Returns the word weight as an integer value.

**Endpoint:** `GET /getWordWeight.php`

### Is Intersecting
Checks if two strings have any common characters.

**Endpoint:** `GET /isIntersecting.php`

### Get Intersecting Rank
Returns the intersecting rank between two strings.

**Endpoint:** `GET /getIntersectingRank.php`

### Get Unique Intersecting Rank
Returns the unique intersecting rank between two strings.

**Endpoint:** `GET /getUniqueIntersectingRank.php`

### Get Unique Intersecting Logical Characters
Returns unique intersecting logical characters.

**Endpoint:** `GET /getUniqueIntersectingLogicalChars.php`

### Can Make Word
Checks if the second string can be made from the first string.

**Endpoint:** `GET /canMakeWord.php`

### Can Make All Words
Checks if all words in a list can be made from the input string.

**Endpoint:** `GET /canMakeAllWords.php`

### Split Word
Splits a string into chunks based on the specified number.

**Endpoint:** `GET /splitWord.php`

**Parameters:**
- `input1` (required) - Input string
- `input2` (required) - Language
- `input3` (required) - Number of chunks

### Split Into 15 Chunks
Splits a string into exactly 15 random chunks while retaining order.

**Endpoint:** `GET /splitInto15Chunks.php`

### Add Character At Position
Inserts a character at the specified position in the string.

**Endpoint:** `GET /addCharacterAt.php`

**Parameters:**
- `input1` (required) - Input string
- `input2` (required) - Language
- `input3` (required) - Position index
- `input4` (required) - Character to insert

### Add Character At End
Appends a character to the end of the string.

**Endpoint:** `GET /addCharacterAtEnd.php`

### Are Ladder Words
Checks if two strings differ by exactly one character.

**Endpoint:** `GET /areLadderWords.php`

### Are Head and Tail Words
Checks if the last character of the first word equals the first character of the second.

**Endpoint:** `GET /areHeadAndTailWords.php`

### Base Consonants
Checks if two strings have the same consonants and length.

**Endpoint:** `GET /baseConsonants.php`

## 🌐 Language Processing

### Detect Language
Automatically detects if a string is English, Telugu, or mixed.

**Endpoint:** `GET /getLangForString.php`

**Parameters:**
- `input1` (required) - Input string

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/getLangForString.php?input1=hello"
```

**Response:**
```json
{
  "response_code": 200,
  "message": "Language Detected",
  "string": "hello",
  "language": null,
  "data": "English"
}
```

### Is Character Vowel
Determines if a character is a vowel in the specified language.

**Endpoint:** `GET /isCharVowel.php`

### Is Character Consonant
Determines if a character is a consonant in the specified language.

**Endpoint:** `GET /isCharConsonant.php`

### Get Base Characters
Gets the base characters for Telugu letters.

**Endpoint:** `GET /getBaseCharacters.php`

### Get Filler Characters
Returns random characters of the specified type (vowel/consonant).

**Endpoint:** `GET /getFillerCharacters.php`

**Parameters:**
- `input1` (required) - Number of characters
- `input2` (required) - Language
- `input3` (required) - Type ("vowel" or "consonant")

### Get Random Logical Characters
Returns a random set of logical characters.

**Endpoint:** `GET /getRandomLogicalChars.php`

**Parameters:**
- `input1` (required) - Number of characters to return
- `input2` (required) - Language

### Parse to Logical Characters
Parses a string into logical characters.

**Endpoint:** `GET /parseToLogicalCharacters.php`

### Get Match ID String
Compares two strings and returns a match pattern.

**Endpoint:** `GET /get_match_id_string.php`

Returns:
- `1` - Character matches and is in the same position
- `2` - Character exists but in different position
- `5` - Character doesn't exist in the first string

## 👤 User Management

### Check User Exists
Checks if a user exists in the system by email.

**Endpoint:** `GET /userExists.php`

**Parameters:**
- `email` (required) - User email address

**Example:**
```bash
curl "https://wpapi.telugupuzzles.com/api/userExists.php?email=user@example.com"
```

### User Login
Authenticates a user with email and password.

**Endpoint:** `GET /ws_login.php`

**Parameters:**
- `email` (required) - User email address
- `password` (required) - User password

### Get User Role
Returns the role of a user by email.

**Endpoint:** `GET /getRole.php`

**Parameters:**
- `email` (required) - User email address

## 📝 Error Handling

All APIs return consistent error responses:

```json
{
  "response_code": 400,
  "message": "Error description",
  "string": null,
  "language": null,
  "data": null
}
```

### Common Error Messages
- `"Invalid or Empty Word"` - The string parameter is empty or invalid
- `"Invalid or Empty Language"` - The language parameter is empty or invalid
- `"Invalid Request"` - Required parameters are missing

## 🔧 Response Codes

| Code | Description |
|------|-------------|
| 200  | Success - Request completed successfully |
| 400  | Bad Request - Invalid parameters or request format |

## 🌍 Supported Languages

| Language | Code |
|----------|------|
| English  | `English` |
| Telugu   | `Telugu` |

## 💡 Usage Examples

### JavaScript/Fetch
```javascript
async function getStringLength(text, language) {
  const response = await fetch(
    `https://wpapi.telugupuzzles.com/api/getLength.php?string=${encodeURIComponent(text)}&language=${language}`
  );
  return await response.json();
}

// Usage
getStringLength("hello", "English").then(result => {
  console.log(result.data); // 5
});
```

### Python/Requests
```python
import requests

def get_string_length(text, language):
    url = "https://wpapi.telugupuzzles.com/api/getLength.php"
    params = {"string": text, "language": language}
    response = requests.get(url, params=params)
    return response.json()

# Usage
result = get_string_length("hello", "English")
print(result["data"])  # 5
```

### PHP/cURL
```php
function getStringLength($text, $language) {
    $url = "https://wpapi.telugupuzzles.com/api/getLength.php";
    $params = http_build_query([
        'string' => $text,
        'language' => $language
    ]);
    
    $response = file_get_contents($url . '?' . $params);
    return json_decode($response, true);
}

// Usage
$result = getStringLength("hello", "English");
echo $result['data']; // 5
```

## 🔗 Additional Resources

- **OpenAPI Specification:** [openapi.yaml](openapi.yaml)
- **Interactive Documentation:** [swagger.html](swagger.html)
- **Postman Collection:** [postman_collection.json](postman_collection.json)
- **Thunder Client Collection:** [thunder_collection.json](thunder_collection.json)

## 📞 Support

For questions, issues, or feature requests:
- Email: support@telugupuzzles.com
- GitHub: [telugupuzzles/wpapi](https://github.com/telugupuzzles/wpapi)

## 📄 License

This API is licensed under the MIT License. See the LICENSE file for details.

---

*Last updated: November 2025*