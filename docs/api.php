<!DOCTYPE html>
<html lang="en">
<?php
$root = 'http://' . $_SERVER['HTTP_HOST'] . '/ananya/';
$apiBase = 'https://ananya.telugupuzzles.com/api.php/';
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indic Language Word Processor APIs - Documentation</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Prism.js for syntax highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="../css/style.css">
    
    <style>
        .api-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
        }
        .api-endpoint {
            background-color: #e3f2fd;
            padding: 0.5rem;
            border-radius: 0.25rem;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        .response-code {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: bold;
            color: white;
        }
        .response-200 { background-color: #28a745; }
        .response-400 { background-color: #dc3545; }
        .sidebar {
            position: sticky;
            top: 90px;
            height: calc(100vh - 110px);
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        .nav-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }
        .category-header {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            margin: 1rem 0 0.5rem 0;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body>
    <?php 
    $root_path = '../';
    $css_path = '../css/';
    include '../includes/header.php'; 
    ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="row page-header">
                <div class="col-12">
                    <h2 class="text-center mb-4">API Documentation</h2>
                    <p class="text-center text-muted">Comprehensive guide to the Ananya Indic Language Text Processing API</p>
                </div>
            </div>
            <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3">
                    <h6 class="text-muted">Quick Navigation</h6>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="#overview">Overview</a>
                        <a class="nav-link" href="#authentication">Authentication</a>
                        <a class="nav-link" href="#response-format">Response Format</a>
                        <div class="category-header">Authentication</div>

                        <a class="nav-link" href="#auth-login">Login</a>
                        <a class="nav-link" href="#auth-user-exists">User Exists</a>

                                                <div class="category-header">Character Analysis</div>

                        <a class="nav-link" href="#characters-add-at">Add Character at Position</a>
                        <a class="nav-link" href="#characters-add-end">Add Character at End</a>
                        <a class="nav-link" href="#characters-base">Base Characters</a>
                        <a class="nav-link" href="#characters-base-consonants">Base Consonants</a>
                        <a class="nav-link" href="#characters-codepoint-length">Code Point Length</a>
                        <a class="nav-link" href="#characters-codepoints">Code Points</a>
                        <a class="nav-link" href="#characters-filler">Filler Characters</a>
                        <a class="nav-link" href="#characters-logical">Logical Characters</a>
                        <a class="nav-link" href="#characters-logical-at">Logical Character At Position</a>
                        <a class="nav-link" href="#characters-random-logical">Random Logical Characters</a>

                                                <div class="category-header">Character Validation</div>

                        <a class="nav-link" href="#validation-contains-all-logical-chars">Contains All Logical Characters</a>
                        <a class="nav-link" href="#validation-contains-char">Contains Character</a>
                        <a class="nav-link" href="#validation-contains-logical-chars">Contains Logical Characters</a>
                        <a class="nav-link" href="#validation-contains-logical-sequence">Contains Logical Sequence</a>
                        <a class="nav-link" href="#validation-contains-space">Contains Space</a>
                        <a class="nav-link" href="#validation-contains-string">Contains String</a>
                        <a class="nav-link" href="#validation-ends-with">Ends With</a>
                        <a class="nav-link" href="#validation-is-vowel">Is Vowel</a>
                        <a class="nav-link" href="#validation-starts-with">Starts With</a>

                                                <div class="category-header">String Comparison</div>

                        <a class="nav-link" href="#comparison-compare-ignore-case">Compare To (Ignore Case)</a>
                        <a class="nav-link" href="#comparison-compare-to">Compare To</a>
                        <a class="nav-link" href="#comparison-equals">Equals</a>
                        <a class="nav-link" href="#comparison-is-intersecting">Is Intersecting</a>
                        <a class="nav-link" href="#comparison-reverse-equals">Reverse Equals</a>

                                                <div class="category-header">Text Operations</div>

                        <a class="nav-link" href="#text-length">Text Length</a>
                        <a class="nav-link" href="#text-randomize">Text Randomize</a>
                        <a class="nav-link" href="#text-replace">Text Replace</a>
                        <a class="nav-link" href="#text-reverse">Text Reverse</a>
                        <a class="nav-link" href="#text-split">Text Split</a>

                                                <div class="category-header">Utility</div>

                        <a class="nav-link" href="#utility-index-of">Index Of</a>
                        <a class="nav-link" href="#utility-language">Language</a>
                        <a class="nav-link" href="#utility-length-alternative">Length Alternative</a>
                        <a class="nav-link" href="#utility-length-no-spaces">Length No Spaces</a>
                        <a class="nav-link" href="#utility-length-no-spaces-commas">Length No Spaces/Commas</a>

                                            <div class="category-header">Word Analysis</div>

                        <a class="nav-link" href="#analysis-can-make-all-words">Can Make All Words</a>
                        <a class="nav-link" href="#analysis-can-make-word">Can Make Word</a>
                        <a class="nav-link" href="#analysis-detect-language">Detect Language</a>
                        <a class="nav-link" href="#analysis-get-match-id-string">Get Match ID String</a>
                        <a class="nav-link" href="#analysis-head-tail-words">Head Tail Words</a>
                        <a class="nav-link" href="#analysis-intersecting-rank">Intersecting Rank</a>
                        <a class="nav-link" href="#analysis-is-anagram">Is Anagram</a>
                        <a class="nav-link" href="#analysis-is-consonant">Is Consonant</a>
                        <a class="nav-link" href="#analysis-is-palindrome">Is Palindrome</a>
                        <a class="nav-link" href="#analysis-ladder-words">Ladder Words</a>
                        <a class="nav-link" href="#analysis-parse-to-logical-characters">Parse to Logical Characters</a>
                        <a class="nav-link" href="#analysis-role">Character Role</a>
                        <a class="nav-link" href="#analysis-split-into-chunks">Split into Chunks</a>
                        <a class="nav-link" href="#analysis-unique-intersecting-chars">Unique Intersecting Characters</a>
                        <a class="nav-link" href="#analysis-unique-intersecting-rank">Unique Intersecting Rank</a>
                        <a class="nav-link" href="#analysis-word-level">Word Level</a>
                        <a class="nav-link" href="#analysis-word-strength">Word Strength</a>
                        <a class="nav-link" href="#analysis-word-weight">Word Weight</a>

                                            </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    
                    <!-- Overview Section -->
                    <section id="overview" class="mb-5">
                        <h1 class="display-4 mb-4">Indic Language Word Processor API</h1>
                        <p class="lead">A comprehensive REST API for processing Telugu and English text, providing advanced linguistic analysis and manipulation capabilities.</p>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary">50+</h3>
                                        <p class="mb-0">Available APIs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-success">2</h3>
                                        <p class="mb-0">Supported Languages</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-info">JSON</h3>
                                        <p class="mb-0">Response Format</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Base URL Section -->
                    <section id="base-url" class="api-section">
                        <h2>Base URL</h2>
                        <div class="api-endpoint">
                            <strong>Production:</strong> https://ananya.telugupuzzles.com/api.php/
                        </div>
                        <div class="api-endpoint">
                            <strong>Local Development:</strong> http://localhost/ananya/api.php/
                        </div>
                        <p class="mt-2">All API endpoints follow the pattern: <code>base_url + category/action</code></p>
                        <p><strong>Categories:</strong> Authentication, Character Analysis, Character Validation, String Comparison, Text Operations, Utility, Word Analysis</p>
                    </section>

                    <!-- Authentication Section -->
                    <section id="authentication" class="api-section">
                        <h2>Authentication</h2>
                        <p>Most APIs are publicly accessible and do not require authentication. User management APIs require valid credentials.</p>
                        <div class="alert alert-info">
                            <strong>Note:</strong> All responses are cached for 2 hours to improve performance.
                        </div>
                    </section>

                    <!-- Response Format Section -->
                    <section id="response-format" class="api-section">
                        <h2>Response Format</h2>
                        <p>All APIs return JSON responses with a consistent structure:</p>
                        
                        <h5>Success Response (200)</h5>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Operation completed successfully",
  "string": "input_string",
    "language": "English",
    "data": "result_data",
    "success": true,
    "result": "result_data",
    "error": null
}</code></pre>

                        <h5>Error Response (400)</h5>
                        <pre><code class="language-json">{
  "response_code": 400,
  "message": "Error description",
  "string": null,
  "language": null,
    "data": null,
    "success": false,
    "result": null,
    "error": "Error description"
}</code></pre>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6>Response Codes</h6>
                                <ul>
                                    <li><span class="response-code response-200">200</span> Success</li>
                                    <li><span class="response-code response-400">400</span> Bad Request</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Supported Languages</h6>
                                <ul>
                                    <li><code>English</code></li>
                                    <li><code>Telugu</code></li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- API Endpoints -->
                    <h1 class="mt-5 mb-4">API Reference</h1>
                    <!-- Analysis Operations -->
                    <h2 class="category-header">Authentication</h2>

                    <div id="auth-login" class="api-section">
                        <h3>Login</h3>
                        <p>Authenticates a user with email and password.</p>
                        
                        <div class="alert alert-info">
                            <strong>Test Mode:</strong> Use <code>test@example.com</code> with password <code>password123</code> to test successful authentication without a database.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/auth/login
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>email</code> (required) - User email address</li>
                                    <li><code>password</code> (required) - Password</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>auth/login?email=user@example.com&password=secret
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Login successful",
  "data": {
    "authenticated": true,
    "user_id": 123
  },
  "success": true,
  "error": null
}</code></pre>

                        <h6>Error Response (401)</h6>
                        <pre><code class="language-json">{
  "response_code": 401,
  "message": "Authentication failed",
  "data": null,
  "success": false,
  "error": "Invalid email or password"
}</code></pre>
                    </div>

                    <div id="auth-user-exists" class="api-section">
                        <h3>User Exists</h3>
                        <p>Checks if a user with the given email exists in the system.</p>
                        
                        <div class="alert alert-info">
                            <strong>Test Mode:</strong> Use <code>test@example.com</code> to test user exists (returns true), or <code>newuser@example.com</code> to test user not exists (returns false).
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/auth/user-exists
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>email</code> (required) - Email address to check</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>auth/user-exists?email=user@example.com
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "User exists",
  "data": true,
  "success": true,
  "error": null
}</code></pre>
                    </div>


                    <h2 class="category-header">Character Analysis</h2>

                    <div id="characters-add-at" class="api-section">
                        <h3>Add Character at Position</h3>
                        <p>Adds a character or string at a specific position in the input string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/add-at
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Position (0-based index)</li>
                                    <li><code>input3</code> (required) - Character/string to add</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/add-at?string=hello&input2=2&input3=X&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Character added at position",
  "string": "hello",
  "language": "English",
    "data": "heXllo",
    "success": true,
    "result": "heXllo",
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-add-end" class="api-section">
                        <h3>Add Character at End</h3>
                        <p>Adds a character or string at the end of the input string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/add-end
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Character/string to add</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/add-end?string=hello&input2=!&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Character added at end",
  "string": "hello",
  "language": "English",
    "data": "hello!",
    "success": true,
    "result": "hello!",
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-base" class="api-section">
                        <h3>Base Characters</h3>
                        <p>Returns the base characters from the input string, useful for anagram analysis.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/base
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/base?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Base characters processed",
  "string": "hello",
  "language": "English",
    "data": ["h", "e", "l", "l", "o"],
    "success": true,
    "result": ["h", "e", "l", "l", "o"],
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-base-consonants" class="api-section">
                        <h3>Base Consonants</h3>
                        <p>Returns the base consonants from a string compared against a second string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/base-consonants
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Second string to compare</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/base-consonants?string=hello&input2=world&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Base consonants calculated",
  "string": "hello",
  "language": "English",
    "data": ["l"],
    "success": true,
    "result": ["l"],
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-codepoint-length" class="api-section">
                        <h3>Code Point Length</h3>
                        <p>Returns the total number of Unicode code points in the string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/codepoint-length
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/codepoint-length?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Code point length calculated",
    "string": "hello",
    "language": "English",
        "data": 5,
    "success": true,
        "result": 5,
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-codepoints" class="api-section">
                        <h3>Code Points</h3>
                        <p>Returns an array of Unicode code points for each logical character in the string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/codepoints
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/codepoints?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Code points processed",
  "string": "hello",
  "language": "English",
    "data": [[104], [101], [108], [108], [111]],
    "success": true,
    "result": [[104], [101], [108], [108], [111]],
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-filler" class="api-section">
                        <h3>Filler Characters</h3>
                        <p>Generates random filler characters of the specified type and count for the given language.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/filler
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>count</code> (optional) - Number of characters to generate (default 3)</li>
                                    <li><code>type</code> (optional) - Type of filler characters (default <code>consonant</code>)</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/filler?count=5&language=english&type=consonant
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Filler characters generated",
    "string": "5 consonant",
    "language": "English",
        "data": ["b", "c", "d", "f", "g"],
    "success": true,
        "result": ["b", "c", "d", "f", "g"],
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-logical" class="api-section">
                        <h3>Logical Characters</h3>
                        <p>Returns an array of logical characters from the input string, properly handling complex scripts.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/logical
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/logical?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Logical characters processed",
    "string": "hello",
    "language": "English",
        "data": ["h", "e", "l", "l", "o"],
    "success": true,
        "result": ["h", "e", "l", "l", "o"],
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-logical-at" class="api-section">
                        <h3>Logical Character At Position</h3>
                        <p>Returns the logical character at a specific position in the string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/logical-at
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (optional) - Position (0-based index, default 0)</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/logical-at?string=hello&input2=1&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Logical character at position retrieved",
  "string": "hello",
  "language": "English",
    "data": "e",
    "success": true,
    "result": "e",
    "error": null
}</code></pre>
                    </div>

                    <div id="characters-random-logical" class="api-section">
                        <h3>Random Logical Characters</h3>
                        <p>Generates random logical characters from the corpus for the selected language.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/characters/random-logical
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>count</code> (optional) - Number of characters to generate (default 5)</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>characters/random-logical?count=3&language=english
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
    "message": "Random logical characters generated",
        "string": null,
  "language": "english",
    "data": ["h", "e", "l"],
    "success": true,
    "result": ["h", "e", "l"],
    "error": null
}</code></pre>
                    </div>


                    <h2 class="category-header">Character Validation</h2>

                    <div id="validation-contains-all-logical-chars" class="api-section">
                        <h3>Contains All Logical Characters</h3>
                        <p>Checks whether all comma-separated logical characters in <code>input2</code> are present in the input string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/contains-all-logical-chars
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Comma-separated logical characters to check</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/contains-all-logical-chars?string=hello&input2=h,e&language=english
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "All logical characters check completed",
  "string": "అనన్య",
  "language": "telugu",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-contains-char" class="api-section">
                        <h3>Contains Character</h3>
                        <p>Checks if the input string contains a specific logical character.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/contains-char
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Character to search for</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/contains-char?string=hello&input2=e&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Character check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-contains-logical-chars" class="api-section">
                        <h3>Contains Logical Characters</h3>
                        <p>Checks whether the string contains at least one logical character from the comma-separated <code>input2</code> list.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/contains-logical-chars
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Comma-separated logical characters to check</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/contains-logical-chars?string=hello&input2=e,l,z
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Logical characters check completed",
  "string": "hello",
  "language": "telugu",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-contains-logical-sequence" class="api-section">
                        <h3>Contains Logical Sequence</h3>
                        <p>Checks if the string contains an exact logical-character sequence provided in <code>input2</code>.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/contains-logical-sequence
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Logical character sequence (not comma-separated)</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/contains-logical-sequence?string=hello&input2=ell
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Logical character sequence check completed",
  "string": "hello",
  "language": "telugu",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-contains-space" class="api-section">
                        <h3>Contains Space</h3>
                        <p>Checks if a string contains any whitespace characters.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/contains-space
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/contains-space?string=hello world&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Space check completed",
  "string": "hello world",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-contains-string" class="api-section">
                        <h3>Contains String</h3>
                        <p>Checks if a string contains another string as a substring.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/contains-string
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Substring to search for</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/contains-string?string=hello&input2=ell&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "String check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-ends-with" class="api-section">
                        <h3>Ends With</h3>
                        <p>Checks if a string ends with a specific suffix.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/ends-with
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Suffix to check</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/ends-with?string=hello&input2=lo&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Suffix check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-is-vowel" class="api-section">
                        <h3>Is Vowel</h3>
                        <p>Checks if the first logical character of <code>string</code> is a vowel in the specified language.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/is-vowel
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input character</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/is-vowel?string=e&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Vowel check completed",
  "string": "e",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="validation-starts-with" class="api-section">
                        <h3>Starts With</h3>
                        <p>Checks if a string starts with a specific prefix.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/validation/starts-with
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Prefix to check</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu, defaults to Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>validation/starts-with?string=hello&input2=he&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Prefix check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>


                    <h2 class="category-header">String Comparison</h2>

                    <div id="comparison-compare-ignore-case" class="api-section">
                        <h3>Compare To (Ignore Case)</h3>
                        <p>Compares two strings in dictionary order while ignoring uppercase/lowercase differences. Returns 0 if equal, a negative number if the first string comes before the second, or a positive number if it comes after.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/comparison/compare-ignore-case
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>comparison/compare-ignore-case?string=Hello&input2=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Case-insensitive comparison completed",
  "string": "Hello",
  "language": "English",
  "data": 0,
  "success": true,
  "result": 0,
  "error": null
}</code></pre>
                    </div>

                    <div id="comparison-compare-to" class="api-section">
                        <h3>Compare To</h3>
                        <p>Compares two strings in dictionary order and returns an ordering number: 0 if equal, a negative number if the first string comes before the second, or a positive number if it comes after.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/comparison/compare-to
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>comparison/compare-to?string=apple&input2=banana&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Comparison completed",
  "string": "apple",
  "language": "English",
  "data": -1,
  "success": true,
  "result": -1,
  "error": null
}</code></pre>
                    </div>

                    <div id="comparison-equals" class="api-section">
                        <h3>Equals</h3>
                        <p>Compares two strings for exact equality.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/comparison/equals
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>comparison/equals?string=hello&input2=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Equality check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="comparison-is-intersecting" class="api-section">
                        <h3>Is Intersecting</h3>
                        <p>Checks if two strings have any common characters.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/comparison/is-intersecting
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>comparison/is-intersecting?string=hello&input2=world&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Intersection check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="comparison-reverse-equals" class="api-section">
                        <h3>Reverse Equals</h3>
                        <p>Checks if the reverse of the first string equals the second string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/comparison/reverse-equals
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>comparison/reverse-equals?string=hello&input2=olleh&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Reverse equality check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>


                    <h2 class="category-header">Text Operations</h2>

                    <div id="text-length" class="api-section">
                        <h3>Text Length</h3>
                        <p>Returns the logical length of a string, which may differ from byte length for non-ASCII characters.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/text/length
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu), defaults to Telugu</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>text/length?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Length calculated",
  "string": "hello",
  "language": "English",
  "data": 5,
  "success": true,
  "result": 5,
  "error": null
}</code></pre>
                    </div>

                    <div id="text-randomize" class="api-section">
                        <h3>Text Randomize</h3>
                        <p>Randomizes the logical characters within the input string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/text/randomize
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu), defaults to Telugu</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>text/randomize?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Text randomized",
  "string": "hello",
  "language": "English",
  "data": ["l", "o", "e", "l", "h"],
  "success": true,
  "result": ["l", "o", "e", "l", "h"],
  "error": null
}</code></pre>
                    </div>

                    <div id="text-replace" class="api-section">
                        <h3>Text Replace</h3>
                        <p>Replaces all occurrences of a substring with another string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/text/replace
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>input2</code> (required) - Text to find</li>
                                    <li><code>input3</code> (optional) - Replacement text</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu), defaults to Telugu</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>text/replace?string=hello&input2=ell&input3=i&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Text replaced",
  "string": "hello",
  "language": "English",
  "data": "hio",
  "success": true,
  "result": "hio",
  "error": null
}</code></pre>
                    </div>

                    <div id="text-reverse" class="api-section">
                        <h3>Text Reverse</h3>
                        <p>Returns the reverse of the input string, preserving logical character order.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/text/reverse
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu), defaults to Telugu</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>text/reverse?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Text reversed",
  "string": "hello",
  "language": "English",
  "data": "olleh",
  "success": true,
  "result": "olleh",
  "error": null
}</code></pre>
                    </div>

                    <div id="text-split" class="api-section">
                        <h3>Text Split</h3>
                        <p>Splits text by a delimiter and returns the parts joined with <code> | </code>.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/text/split
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>delimiter</code> (optional) - Delimiter to split on (default <code>-</code>)</li>
                                    <li><code>input2</code> (optional) - Alternate delimiter parameter</li>
                                    <li><code>language</code> (optional) - Language (English/Telugu), defaults to Telugu</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>text/split?string=hello-world&delimiter=-&language=english
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Text split",
  "string": "hello-world",
  "language": "English",
  "data": "hello | world",
  "success": true,
  "result": "hello | world",
  "error": null
}</code></pre>
                    </div>


                    <h2 class="category-header">Utility</h2>
                    <p>If <code>language</code> is omitted for utility length endpoints, the API auto-detects <code>english</code> or <code>telugu</code>. Use <code>language</code> to explicitly override detection.</p>

                    <div id="utility-index-of" class="api-section">
    <h3>Index Of</h3>
    <p>Finds the index position of a substring within the input string.</p>

    <div class="row">
        <div class="col-md-6">
            <h6>Endpoint</h6>
            <div class="api-endpoint">GET /api.php/utility/index-of</div>
        </div>
        <div class="col-md-6">
            <h6>Parameters</h6>
            <ul>
                <li><code>string</code> (required) - Input string</li>
                <li><code>input2</code> (required) - Substring to search for</li>
                <li><code>language</code> (optional override) - Language (<code>english</code>/<code>telugu</code>)</li>
            </ul>
        </div>
    </div>

    <h6>Example Request</h6>
    <div class="api-endpoint">
        <?= $apiBase ?>utility/index-of?string=hello&input2=ll&language=english
    </div>

    <h6>Example Response</h6>
    <pre><code class="language-json">{
  "response_code": 200,
  "message": "Index found",
    "string": "hello",
    "language": "english",
  "data": 2,
  "success": true,
  "result": 2,
  "error": null
}</code></pre>
</div>

<div id="utility-language" class="api-section">
                        <h3>Detect Language</h3>
                        <p>Automatically detects whether a string is primarily English, Telugu, or mixed/other.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/utility/language
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>utility/language?string=hello
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Language detected",
  "string": "hello",
  "language": null,
  "data": "English",
  "success": true,
  "result": "English",
  "error": null
}</code></pre>
                    </div>

                    <div id="utility-length-alternative" class="api-section">
                        <h3>Length Alternative</h3>
                        <p>Calculates length excluding spaces and special symbols.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/utility/length-alternative
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional override) - Language (english/telugu). If omitted, auto-detected.</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>utility/length-alternative?string=Hello,%20World!&language=english
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Alternative length calculated",
  "string": "Hello, World!",
  "language": "english",
  "data": 10,
  "success": true,
  "result": 10,
  "error": null
}</code></pre>
                                                <p><strong>Note:</strong> If auto-detection returns mixed/unknown, provide <code>language=english</code> or <code>language=telugu</code>.</p>
                    </div>

                    <div id="utility-length-no-spaces" class="api-section">
                        <h3>Length No Spaces</h3>
                        <p>Returns the length of the string excluding spaces.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/utility/length-no-spaces
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional override) - Language (english/telugu). If omitted, auto-detected.</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                                                        <?= $apiBase ?>utility/length-no-spaces?string=hello%20world&language=english
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Length without spaces calculated",
  "string": "hello world",
  "language": "english",
  "data": 10,
  "success": true,
  "result": 10,
  "error": null
}</code></pre>
                    </div>

                    <div id="utility-length-no-spaces-commas" class="api-section">
                        <h3>Length No Spaces/Commas</h3>
                        <p>Returns the length of the string excluding both spaces and commas.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/utility/length-no-spaces-commas
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional override) - Language (english/telugu). If omitted, auto-detected.</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>utility/length-no-spaces-commas?string=hello,%20world&language=english
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Length without spaces and commas calculated",
  "string": "hello, world",
  "language": "english",
  "data": 10,
  "success": true,
  "result": 10,
  "error": null
}</code></pre>
                    </div>

                    <!-- Additional APIs Notice -->
                    <div class="alert alert-info mt-5">
                        <h5>Additional APIs Available</h5>
                        <p>This documentation covers the most commonly used APIs. For a complete list of all 50+ available endpoints, please refer to:</p>
                        <ul>
                            <li><a href="openapi.yaml">OpenAPI Specification</a></li>
                            <li><a href="swagger.php">Interactive API Explorer</a></li>
                            <li><a href="API_Reference.md">Complete API Reference</a></li>
                        </ul>
                    </div>


                    <h2 class="category-header">Word Analysis</h2>

                    <div id="analysis-can-make-all-words" class="api-section">
                        <h3>Can Make All Words</h3>
                        <p>Checks if the string contains enough characters to make all words in the input list.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/can-make-all-words
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Source string</li>
                                    <li><code>input2</code> (required) - Comma-separated list of words</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/can-make-all-words?string=hello&input2=he,lo&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "All words formation check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-can-make-word" class="api-section">
                        <h3>Can Make Word</h3>
                        <p>Checks if one string can be formed using characters from another string.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/can-make-word
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Source string</li>
                                    <li><code>input2</code> (required) - Target word to make</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/can-make-word?string=hello&input2=he&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Word formation check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-detect-language" class="api-section">
                        <h3>Detect Language (Legacy Alias)</h3>
                        <p>Detects language for input text. This is a legacy alias for <code>/utility/language</code>.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/detect-language
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (optional) - Language hint (defaults internally)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/detect-language?string=hello
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Language detected",
  "string": "hello",
  "language": null,
  "data": "English",
  "success": true,
  "result": "English",
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-get-match-id-string" class="api-section">
                        <h3>Get Match ID String</h3>
                        <p>Generates a position-based match ID string comparing two texts character by character.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/get-match-id-string
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/get-match-id-string?string=abc&input2=azc&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Match ID string generated",
  "string": "abc",
  "language": "English",
  "data": "115",
  "success": true,
  "result": "115",
  "error": null
}</code></pre>
                        <div class="alert alert-info">
                            <strong>Match ID Legend:</strong>
                            <ul>
                                <li><code>1</code> - Exact match at position</li>
                                <li><code>2</code> - Character exists elsewhere in first string</li>
                                <li><code>5</code> - Character doesn't exist in first string</li>
                            </ul>
                        </div>
                    </div>

                    <div id="analysis-head-tail-words" class="api-section">
                        <h3>Head Tail Words</h3>
                        <p>Checks if two words are head-and-tail words (the last logical character of the first word matches the first logical character of the second word).</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/head-tail-words
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First word</li>
                                    <li><code>input2</code> (required) - Second word</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/head-tail-words?string=hello&input2=orange&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Head and tail words check completed",
  "string": "hello",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-intersecting-rank" class="api-section">
                        <h3>Intersecting Rank</h3>
                        <p>Calculates the intersecting rank between two strings based on common characters.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/intersecting-rank
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/intersecting-rank?string=hello&input2=world&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Intersecting rank calculated",
  "string": "hello",
  "language": "English",
  "data": 3,
  "success": true,
  "result": 3,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-is-anagram" class="api-section">
                        <h3>Is Anagram</h3>
                        <p>Checks if two strings are anagrams of each other (contain the same characters in different order).</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/is-anagram
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/is-anagram?string=listen&input2=silent&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Anagram check completed",
  "string": "listen",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-is-consonant" class="api-section">
                        <h3>Is Consonant</h3>
                        <p>Checks if a character is a consonant in the specified language.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/is-consonant
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input character</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/is-consonant?string=b&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Consonant check completed",
  "string": "b",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-is-palindrome" class="api-section">
                        <h3>Is Palindrome</h3>
                        <p>Checks if a string reads the same forwards and backwards (palindrome).</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/is-palindrome
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/is-palindrome?string=racecar&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Palindrome check completed",
  "string": "racecar",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-ladder-words" class="api-section">
                        <h3>Ladder Words</h3>
                        <p>Checks if two words are ladder words (they differ by exactly one logical character).</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/ladder-words
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First word</li>
                                    <li><code>input2</code> (required) - Second word</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/ladder-words?string=cat&input2=bat&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Ladder words check completed",
  "string": "cat",
  "language": "English",
  "data": true,
  "success": true,
  "result": true,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-parse-to-logical-characters" class="api-section">
                        <h3>Parse to Logical Characters (Alternative)</h3>
                        <p>Alternative method to parse text into logical characters array.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/parse-to-logical-characters
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input text</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/parse-to-logical-characters?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Text parsed to logical characters (alternative)",
  "string": "hello",
  "language": "English",
  "data": ["h", "e", "l", "l", "o"],
  "success": true,
  "result": ["h", "e", "l", "l", "o"],
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-role" class="api-section">
                        <h3>Character Role</h3>
                        <p>Determines the linguistic role of a character (consonant, vowel, etc.).</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/role
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input character</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/role?string=a&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Character role analyzed",
  "string": "a",
  "language": "English",
  "data": "vowel",
  "success": true,
  "result": "vowel",
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-split-into-chunks" class="api-section">
                        <h3>Split into Chunks</h3>
                        <p>Splits the input string into 15 equal chunks of logical characters.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/split-into-chunks
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/split-into-chunks?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Text split into chunks",
  "string": "hello",
  "language": "English",
  "data": ["h", "e", "l", "l", "o", "", "", "", "", "", "", "", "", "", ""],
  "success": true,
  "result": ["h", "e", "l", "l", "o", "", "", "", "", "", "", "", "", "", ""],
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-unique-intersecting-chars" class="api-section">
                        <h3>Unique Intersecting Characters</h3>
                        <p>Returns the unique characters that intersect between two strings.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/unique-intersecting-chars
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/unique-intersecting-chars?string=hello&input2=world&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Unique intersecting logical characters calculated",
  "string": "hello",
  "language": "English",
  "data": ["l", "o"],
  "success": true,
  "result": ["l", "o"],
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-unique-intersecting-rank" class="api-section">
                        <h3>Unique Intersecting Rank</h3>
                        <p>Calculates the unique intersecting rank between two strings.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/unique-intersecting-rank
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - First string</li>
                                    <li><code>input2</code> (required) - Second string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/unique-intersecting-rank?string=hello&input2=world&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Unique intersecting rank calculated",
  "string": "hello",
  "language": "English",
  "data": 2,
  "success": true,
  "result": 2,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-word-level" class="api-section">
                        <h3>Word Level</h3>
                        <p>Determines the complexity level of a word based on linguistic analysis.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/word-level
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/word-level?string=hello&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Word level calculated",
  "string": "hello",
  "language": "English",
  "data": 2,
  "success": true,
  "result": 2,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-word-strength" class="api-section">
                        <h3>Word Strength</h3>
                        <p>Calculates a strength score for the word based on its complexity and characteristics.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/word-strength
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/word-strength?string=America&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Word strength calculated",
  "string": "America",
  "language": "English",
  "data": 42,
  "success": true,
  "result": 42,
  "error": null
}</code></pre>
                    </div>

                    <div id="analysis-word-weight" class="api-section">
                        <h3>Word Weight</h3>
                        <p>Calculates the weight of a word based on various linguistic factors.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Endpoint</h6>
                                <div class="api-endpoint">
                                    GET /api.php/analysis/word-weight
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Parameters</h6>
                                <ul>
                                    <li><code>string</code> (required) - Input string</li>
                                    <li><code>language</code> (required) - Language (English/Telugu)</li>
                                </ul>
                            </div>
                        </div>

                        <h6>Example Request</h6>
                        <div class="api-endpoint">
                            <?= $apiBase ?>analysis/word-weight?string=computer&language=English
                        </div>

                        <h6>Example Response</h6>
                        <pre><code class="language-json">{
  "response_code": 200,
  "message": "Word weight calculated",
  "string": "computer",
  "language": "English",
  "data": 98,
  "success": true,
  "result": 98,
  "error": null
}</code></pre>
                    </div>


                    <!-- Footer -->
                    <footer class="mt-5 pt-4 border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Resources</h5>
                                <ul class="list-unstyled">
                                    <li><a href="openapi.yaml">OpenAPI Spec</a></li>
                                    <li><a href="postman_collection.json">Postman Collection</a></li>
                                    <li><a href="thunder_collection.json">Thunder Client Collection</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Support</h5>
                                <ul class="list-unstyled">
                                    <li><a href="mailto:support@telugupuzzles.com">Email Support</a></li>
                                    <li><a href="https://github.com/telugupuzzles/ananya">GitHub Issues</a></li>
                                </ul>
                            </div>
                        </div>
                        <hr>
                        <p class="text-muted text-center">&copy; 2025 Telugu Puzzles. All rights reserved.</p>
                    </footer>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Prism.js for syntax highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    
    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const html = document.documentElement;
            const themeToggle = document.getElementById('themeToggle');
            
            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                themeToggle.textContent = 'Dark Mode';
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                themeToggle.textContent = 'Light Mode';
            }
        }

        // Prevent sidebar scroll from chaining to main content
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                // Ensure sidebar starts at the top
                sidebar.scrollTop = 0;
                
                sidebar.addEventListener('wheel', function(e) {
                    const isScrollingDown = e.deltaY > 0;
                    const isAtBottom = sidebar.scrollHeight - sidebar.scrollTop <= sidebar.clientHeight + 1;
                    const isAtTop = sidebar.scrollTop <= 1;
                    
                    // Check if sidebar can scroll in the attempted direction
                    const canScrollDown = !isAtBottom && isScrollingDown;
                    const canScrollUp = !isAtTop && !isScrollingDown;
                    
                    // If sidebar can scroll, stop event from bubbling to prevent main page scroll
                    if (canScrollDown || canScrollUp) {
                        e.stopPropagation();
                    }
                    
                    // Always prevent default at boundaries to stop scroll chaining
                    if ((isAtTop && !isScrollingDown) || (isAtBottom && isScrollingDown)) {
                        e.preventDefault();
                    }
                }, { passive: false });
            }
        });

        // Smooth scrolling for navigation links (offset for fixed navbar)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const navbarHeight = document.querySelector('.navbar-ananya')?.offsetHeight || 76;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 15;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Highlight current section in navigation
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('[id]');
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= sectionTop - 60) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
        </div>
    </div>
</body>
</html>
