# Ananya - Indic Language Text Processing API
> Comprehensive text processing toolkit for Indic languages with REST API interface

## 🌍 Supported Languages

- **Telugu** ✅ (Fully implemented)
- **English** ✅ (Fully implemented) 
- **Hindi** ✅ (Implemented)
- **Gujarati** ✅ (Implemented)
- **Malayalam** ✅ (Implemented)
- Tamil (TBD)
- Kannada (TBD)

## 🚀 Quick Start

### Local Deployment
```bash
# Deploy to your web server
cp -r ananya/ /path/to/htdocs/

# Access the application
http://localhost/ananya/
```

### API Usage
```bash
# Basic text length example
GET http://localhost/ananya/api.php/text/length?string=అమెరికా&language=telugu

# Response
{
  "response_code": 200,
  "message": "Length calculated",
  "string": "అమెరికా",
  "language": "telugu", 
  "data": 4
}
```

## 📋 Complete API Reference

### **📊 Total API Endpoints: 51**

| **Category** | **Count** | **Percentage** | **Description** |
|--------------|-----------|----------------|-----------------|
| 🔤 **Characters** | 10 endpoints | 19.6% | Character manipulation & analysis |
| 📝 **Text** | 5 endpoints | 9.8% | Basic text operations |
| 🔍 **Analysis** | 17 endpoints | 33.3% | Advanced linguistic analysis |
| ⚖️ **Comparison** | 7 endpoints | 13.7% | String comparison operations |
| ✅ **Validation** | 8 endpoints | 15.7% | Content validation & checks |
| 🛠️ **Utility** | 3 endpoints | 5.9% | Helper functions |
| 🔐 **Authentication** | 1 endpoint | 2.0% | User management |

### **🔤 Characters Operations (10 endpoints)**
```
1. characters/base                    - Get base characters
2. characters/logical                 - Get logical characters
3. characters/codepoints             - Get Unicode codepoints
4. characters/codepoint-length       - Get codepoint length
5. characters/random-logical         - Generate random logical chars
6. characters/add-end                - Add character at end
7. characters/logical-at             - Get logical char at position
8. characters/base-consonants        - Get base consonants
9. characters/add-at                 - Add character at position
10. characters/filler                - Generate filler characters
```

### **📝 Text Operations (5 endpoints)**
```
1. text/length                       - Calculate text length
2. text/reverse                      - Reverse text
3. text/randomize                    - Randomize character order
4. text/split                        - Split text into parts
5. text/replace                      - Replace text patterns
```

### **🔍 Analysis Operations (17 endpoints)**
```
1. analysis/is-palindrome            - Check if palindrome
2. analysis/word-strength            - Calculate word strength
3. analysis/word-weight              - Calculate word weight
4. analysis/word-level               - Get word complexity level
5. analysis/is-anagram               - Check if anagram
6. analysis/parse-to-logical-chars   - Parse to logical characters
7. analysis/parse-to-logical-characters - Alternative parsing method
8. analysis/split-into-chunks        - Split into 15-char chunks
9. analysis/can-make-word            - Check word formation
10. analysis/can-make-all-words      - Check multiple word formation
11. analysis/is-intersecting         - Check character intersection
12. analysis/intersecting-rank       - Get intersection count
13. analysis/unique-intersecting-rank - Get unique intersection count
14. analysis/unique-intersecting-logical-chars - Get unique intersecting chars
15. analysis/are-ladder-words        - Check ladder word relationship
16. analysis/are-head-tail-words     - Check head-tail relationship
17. analysis/get-match-id-string     - Generate position-based match ID
```

### **⚖️ Comparison Operations (7 endpoints)**
```
1. comparison/equals                 - Check equality
2. comparison/starts-with            - Check prefix match
3. comparison/ends-with              - Check suffix match
4. comparison/compare                - Lexicographic comparison
5. comparison/compare-ignore-case    - Case-insensitive comparison
6. comparison/reverse-equals         - Check reverse equality
7. comparison/index-of               - Find substring position
```

### **✅ Validation Operations (8 endpoints)**
```
1. validation/contains-space         - Check for spaces
2. validation/contains-char          - Check for specific character
3. validation/contains-logical-chars - Check for logical characters
4. validation/contains-all-logical-chars - Check for all specified chars
5. validation/contains-logical-sequence - Check for character sequence
6. validation/is-consonant           - Check if character is consonant
7. validation/is-vowel               - Check if character is vowel
8. validation/contains-string        - Check for substring
```

### **🛠️ Utility Operations (3 endpoints)**
```
1. utility/length-no-spaces          - Length excluding spaces
2. utility/length-no-spaces-commas   - Length excluding spaces & commas
3. utility/length-alternative        - Alternative length calculation
```

### **🔐 Authentication (1 endpoint)**
```
1. auth/user-exists                  - Check user existence
```

## 📖 Documentation

- **Full API Documentation**: [docs/api.php](docs/api.php) - Interactive documentation with examples
- **API Reference**: [docs/API_Reference.md](docs/API_Reference.md) - Markdown format reference
- **OpenAPI Spec**: [docs/openapi.yaml](docs/openapi.yaml) - Machine-readable API specification

## ✅ Testing & Quality

- **Test Coverage**: 100% success rate on all deterministic endpoints
- **Comprehensive Test Suite**: 50 automated tests covering all major functionality
- **Multi-language Support**: Tested with Telugu, Hindi, Gujarati, Malayalam, and English
- **Unicode Compliance**: Full UTF-8 support for complex Indic scripts

## 🏗️ Architecture

- **Clean URLs**: RESTful API design with `api.php/category/action` structure
- **Single Entry Point**: All requests routed through `api.php` 
- **JSON Responses**: Consistent response format with proper error handling
- **Language Agnostic**: Same API works across all supported languages

## 💡 Usage Examples

### Telugu Text Analysis
```bash
# Get logical characters
GET api.php/characters/logical?string=అమెరికా&language=telugu
# Returns: ["అ", "మె", "రి", "కా"]

# Check if palindrome  
GET api.php/analysis/is-palindrome?string=అకా&language=telugu
# Returns: true

# Word strength analysis
GET api.php/analysis/word-strength?string=అమెరికా&language=telugu
# Returns: 15
```

### Cross-Language Comparison
```bash
# Compare Telugu and English
GET api.php/comparison/compare?string=అమెరికా&input2=america&language=telugu
# Returns: -1 (lexicographic comparison result)
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Run tests (`python api_telugu_tester.py`)
4. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
5. Push to the branch (`git push origin feature/AmazingFeature`)
6. Open a Pull Request

## 📄 License

This project is open source. See the source files for more information.

## 🎯 Key Features

- **51 API endpoints** covering comprehensive text processing needs
- **Multi-script support** for complex Indic language processing
- **RESTful design** with clean, intuitive URL structure
- **100% test coverage** ensuring reliability and consistency
- **Full documentation** with interactive examples
- **Unicode compliant** with proper handling of complex characters
- **Production ready** with error handling and response caching
