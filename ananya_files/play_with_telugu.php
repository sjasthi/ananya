<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play with Telugu - Ananya Telugu Playground</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts for Telugu Support -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Telugu:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS for Telugu Playground -->
    <style>
        :root {
            --telugu-primary: #2E7D32;
            --telugu-secondary: #4CAF50;
            --telugu-accent: #FF6B35;
            --telugu-light: #E8F5E8;
            --telugu-dark: #1B5E20;
            --telugu-text: #2C2C2C;
            --telugu-border: #C8E6C9;
        }
        
        body {
            font-family: 'Inter', 'Noto Sans Telugu', sans-serif;
            background: linear-gradient(135deg, #E8F5E8 0%, #F1F8E9 100%);
            color: var(--telugu-text);
            min-height: 100vh;
        }
        
        .telugu-header {
            background: linear-gradient(135deg, var(--telugu-primary) 0%, var(--telugu-secondary) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
            box-shadow: 0 4px 20px rgba(46, 125, 50, 0.3);
        }
        
        .telugu-title {
            font-size: 3.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .telugu-subtitle {
            font-size: 1.4rem;
            text-align: center;
            font-weight: 300;
            opacity: 0.95;
        }
        
        .category-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(46, 125, 50, 0.1);
            border: 2px solid var(--telugu-border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--telugu-primary), var(--telugu-accent));
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(46, 125, 50, 0.15);
        }
        
        .category-icon {
            font-size: 3rem;
            color: var(--telugu-secondary);
            margin-bottom: 1rem;
        }
        
        .category-title {
            color: var(--telugu-primary);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .category-description {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .tool-section {
            background: var(--telugu-light);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--telugu-border);
        }
        
        .tool-title {
            color: var(--telugu-dark);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .telugu-input {
            font-family: 'Noto Sans Telugu', sans-serif;
            font-size: 1.2rem;
            border: 2px solid var(--telugu-border);
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .telugu-input:focus {
            border-color: var(--telugu-secondary);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
            outline: none;
        }
        
        .telugu-btn {
            background: linear-gradient(135deg, var(--telugu-primary), var(--telugu-secondary));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3);
        }
        
        .telugu-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.4);
            color: white;
        }
        
        .result-box {
            background: white;
            border: 2px solid var(--telugu-secondary);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            font-family: 'Noto Sans Telugu', sans-serif;
            font-size: 1.2rem;
            min-height: 60px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.1);
        }
        
        .example-box {
            background: #FFF8E1;
            border: 1px solid #FFCC02;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.95rem;
        }
        
        .example-label {
            font-weight: 600;
            color: #F57C00;
            margin-bottom: 0.5rem;
        }
        
        .loading {
            display: none;
            text-align: center;
            color: var(--telugu-secondary);
        }
        
        .error-message {
            color: #d32f2f;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .success-message {
            color: var(--telugu-primary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .telugu-text {
            font-family: 'Noto Sans Telugu', sans-serif;
        }
        
        .nav-pills .nav-link {
            color: var(--telugu-primary);
            border-radius: 25px;
            margin: 0 0.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--telugu-primary), var(--telugu-secondary));
            color: white;
        }
        
        .nav-pills .nav-link:hover:not(.active) {
            background: var(--telugu-light);
            color: var(--telugu-dark);
        }
    </style>
</head>
<body>
    <?php 
    $root_path = '';
    $css_path = 'css/';
    include 'includes/header.php'; 
    ?>

    <div class="telugu-header">
        <div class="container">
            <h1 class="telugu-title">
                <i class="fas fa-gamepad"></i>
                Telugu Playground
            </h1>
            <p class="telugu-subtitle">
                Explore the Beauty of Telugu Language • Interactive Tools for Language Enthusiasts
            </p>
        </div>
    </div>

    <div class="container">
        <!-- Navigation Tabs -->
        <ul class="nav nav-pills nav-fill mb-4" id="categoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="text-tab" data-bs-toggle="pill" data-bs-target="#text-operations" type="button" role="tab">
                    <i class="fas fa-text-width"></i> Text Operations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="character-tab" data-bs-toggle="pill" data-bs-target="#character-analysis" type="button" role="tab">
                    <i class="fas fa-microscope"></i> Character Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="word-tab" data-bs-toggle="pill" data-bs-target="#word-analysis" type="button" role="tab">
                    <i class="fas fa-brain"></i> Word Analysis
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="comparison-tab" data-bs-toggle="pill" data-bs-target="#string-comparison" type="button" role="tab">
                    <i class="fas fa-balance-scale"></i> String Comparison
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="categoryTabsContent">
            
            <!-- Text Operations Tab -->
            <div class="tab-pane fade show active" id="text-operations" role="tabpanel">
                <div class="category-card">
                    <div class="text-center">
                        <i class="fas fa-text-width category-icon"></i>
                        <h2 class="category-title">Text Operations</h2>
                        <p class="category-description">
                            Basic text manipulation tools to explore Telugu text processing. 
                            Try different operations with your own Telugu words and phrases!
                        </p>
                    </div>

                    <div class="row">
                        <!-- Text Length -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-ruler-horizontal"></i>
                                    Text Length
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="lengthInput" 
                                           placeholder="Enter Telugu text here... (అక్షరాలు టైప్ చేయండి)">
                                </div>
                                <button class="btn telugu-btn" onclick="getTextLength()">
                                    <i class="fas fa-calculator"></i> Calculate Length
                                </button>
                                <div class="result-box" id="lengthResult">
                                    <span class="text-muted">Result will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Example:</div>
                                    Try typing: <span class="telugu-text fw-bold">తెలుగు భాష</span> (Telugu Language)
                                </div>
                            </div>
                        </div>

                        <!-- Text Reverse -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-undo"></i>
                                    Reverse Text
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="reverseInput" 
                                           placeholder="Enter text to reverse...">
                                </div>
                                <button class="btn telugu-btn" onclick="reverseText()">
                                    <i class="fas fa-exchange-alt"></i> Reverse Text
                                </button>
                                <div class="result-box" id="reverseResult">
                                    <span class="text-muted">Reversed text will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Example:</div>
                                    Try: <span class="telugu-text fw-bold">అనన్య</span> → <span class="telugu-text fw-bold">యన్నఅ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Text Replace -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-edit"></i>
                                    Replace Text
                                </h4>
                                <div class="mb-2">
                                    <input type="text" class="form-control telugu-input mb-2" id="replaceText" 
                                           placeholder="Original text...">
                                    <input type="text" class="form-control telugu-input mb-2" id="searchText" 
                                           placeholder="Text to find...">
                                    <input type="text" class="form-control telugu-input mb-2" id="replaceWith" 
                                           placeholder="Replace with...">
                                </div>
                                <button class="btn telugu-btn" onclick="replaceText()">
                                    <i class="fas fa-search-plus"></i> Replace
                                </button>
                                <div class="result-box" id="replaceResult">
                                    <span class="text-muted">Replaced text will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Example:</div>
                                    Text: <span class="telugu-text fw-bold">నమస్కారం అందరికీ</span><br>
                                    Find: <span class="fw-bold">అందరికీ</span>, Replace: <span class="fw-bold">మిత్రులకు</span>
                                </div>
                            </div>
                        </div>

                        <!-- Randomize Text -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-random"></i>
                                    Randomize Text
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="randomizeInput" 
                                           placeholder="Enter text to scramble...">
                                </div>
                                <button class="btn telugu-btn" onclick="randomizeText()">
                                    <i class="fas fa-dice"></i> Scramble Text
                                </button>
                                <div class="result-box" id="randomizeResult">
                                    <span class="text-muted">Scrambled text will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Fun Fact:</div>
                                    This scrambles the characters in your text - great for creating puzzles!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Character Analysis Tab -->
            <div class="tab-pane fade" id="character-analysis" role="tabpanel">
                <div class="category-card">
                    <div class="text-center">
                        <i class="fas fa-microscope category-icon"></i>
                        <h2 class="category-title">Character Analysis</h2>
                        <p class="category-description">
                            Dive deep into Telugu characters and Unicode. Understand how Telugu text is structured 
                            and explore the fascinating world of Indic scripts!
                        </p>
                    </div>

                    <div class="row">
                        <!-- Logical Characters -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-atom"></i>
                                    Logical Characters
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="logicalInput" 
                                           placeholder="Enter Telugu text for analysis...">
                                </div>
                                <button class="btn telugu-btn" onclick="getLogicalChars()">
                                    <i class="fas fa-search"></i> Analyze Characters
                                </button>
                                <div class="result-box" id="logicalResult">
                                    <span class="text-muted">Logical character breakdown will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">What are logical characters?</div>
                                    Telugu combines base characters with vowel signs. For example: <span class="telugu-text fw-bold">కా</span> = <span class="telugu-text">క</span> + <span class="telugu-text">ా</span>
                                </div>
                            </div>
                        </div>

                        <!-- Unicode Code Points -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-code"></i>
                                    Unicode Code Points
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="unicodeInput" 
                                           placeholder="Enter text to see Unicode values...">
                                </div>
                                <button class="btn telugu-btn" onclick="getUnicodePoints()">
                                    <i class="fas fa-hashtag"></i> Get Unicode Points
                                </button>
                                <div class="result-box" id="unicodeResult">
                                    <span class="text-muted">Unicode values will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Did you know?</div>
                                    Each character has a unique Unicode number. <span class="telugu-text fw-bold">అ</span> = U+0C05
                                </div>
                            </div>
                        </div>

                        <!-- Character Validation -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-check-circle"></i>
                                    Vowel or Consonant?
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="charTypeInput" 
                                           placeholder="Enter a Telugu character...">
                                </div>
                                <button class="btn telugu-btn" onclick="checkCharacterType()">
                                    <i class="fas fa-question-circle"></i> Check Character
                                </button>
                                <div class="result-box" id="charTypeResult">
                                    <span class="text-muted">Character type will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Try these:</div>
                                    Vowels: <span class="telugu-text fw-bold">అ ఆ ఇ ఈ</span> | Consonants: <span class="telugu-text fw-bold">క చ ట త ప</span>
                                </div>
                            </div>
                        </div>

                        <!-- Base Characters -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-layer-group"></i>
                                    Base Characters
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="baseCharsInput" 
                                           placeholder="Enter Telugu text...">
                                </div>
                                <button class="btn telugu-btn" onclick="getBaseCharacters()">
                                    <i class="fas fa-filter"></i> Extract Base Characters
                                </button>
                                <div class="result-box" id="baseCharsResult">
                                    <span class="text-muted">Base characters will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Example:</div>
                                    <span class="telugu-text fw-bold">కృష్ణ</span> → Base characters: <span class="telugu-text fw-bold">క్ ష్ ణ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Word Analysis Tab -->
            <div class="tab-pane fade" id="word-analysis" role="tabpanel">
                <div class="category-card">
                    <div class="text-center">
                        <i class="fas fa-brain category-icon"></i>
                        <h2 class="category-title">Word Analysis</h2>
                        <p class="category-description">
                            Advanced linguistic analysis tools for Telugu words. Perfect for puzzle makers, 
                            poets, and language enthusiasts who want to understand word patterns and complexity!
                        </p>
                    </div>

                    <div class="row">
                        <!-- Anagram Checker -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-puzzle-piece"></i>
                                    Anagram Checker
                                </h4>
                                <div class="mb-2">
                                    <input type="text" class="form-control telugu-input mb-2" id="anagramWord1" 
                                           placeholder="First word...">
                                    <input type="text" class="form-control telugu-input" id="anagramWord2" 
                                           placeholder="Second word...">
                                </div>
                                <button class="btn telugu-btn" onclick="checkAnagram()">
                                    <i class="fas fa-search"></i> Check Anagram
                                </button>
                                <div class="result-box" id="anagramResult">
                                    <span class="text-muted">Anagram result will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">What's an anagram?</div>
                                    Words made from the same letters: "listen" ↔ "silent"
                                </div>
                            </div>
                        </div>

                        <!-- Palindrome Checker -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-sync-alt"></i>
                                    Palindrome Checker
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="palindromeInput" 
                                           placeholder="Enter word to check...">
                                </div>
                                <button class="btn telugu-btn" onclick="checkPalindrome()">
                                    <i class="fas fa-arrows-alt-h"></i> Check Palindrome
                                </button>
                                <div class="result-box" id="palindromeResult">
                                    <span class="text-muted">Palindrome result will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Palindrome examples:</div>
                                    Words that read the same forwards and backwards: "mom", "racecar"
                                </div>
                            </div>
                        </div>

                        <!-- Word Strength -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-dumbbell"></i>
                                    Word Strength
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="strengthInput" 
                                           placeholder="Enter Telugu word...">
                                </div>
                                <button class="btn telugu-btn" onclick="getWordStrength()">
                                    <i class="fas fa-chart-bar"></i> Calculate Strength
                                </button>
                                <div class="result-box" id="strengthResult">
                                    <span class="text-muted">Word strength will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">What is word strength?</div>
                                    A measure of linguistic complexity based on character combinations
                                </div>
                            </div>
                        </div>

                        <!-- Language Detection -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-globe"></i>
                                    Language Detection
                                </h4>
                                <div class="mb-3">
                                    <input type="text" class="form-control telugu-input" id="languageInput" 
                                           placeholder="Enter text in any language...">
                                </div>
                                <button class="btn telugu-btn" onclick="detectLanguage()">
                                    <i class="fas fa-eye"></i> Detect Language
                                </button>
                                <div class="result-box" id="languageResult">
                                    <span class="text-muted">Detected language will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Try different scripts:</div>
                                    Telugu: <span class="telugu-text fw-bold">తెలుగు</span> | English: <span class="fw-bold">English</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- String Comparison Tab -->
            <div class="tab-pane fade" id="string-comparison" role="tabpanel">
                <div class="category-card">
                    <div class="text-center">
                        <i class="fas fa-balance-scale category-icon"></i>
                        <h2 class="category-title">String Comparison</h2>
                        <p class="category-description">
                            Compare and analyze relationships between Telugu words and phrases. 
                            Perfect for understanding similarities and differences in text!
                        </p>
                    </div>

                    <div class="row">
                        <!-- String Equality -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-equals"></i>
                                    String Equality
                                </h4>
                                <div class="mb-2">
                                    <input type="text" class="form-control telugu-input mb-2" id="equalStr1" 
                                           placeholder="First string...">
                                    <input type="text" class="form-control telugu-input" id="equalStr2" 
                                           placeholder="Second string...">
                                </div>
                                <button class="btn telugu-btn" onclick="checkEquality()">
                                    <i class="fas fa-check"></i> Check Equality
                                </button>
                                <div class="result-box" id="equalityResult">
                                    <span class="text-muted">Equality result will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Note:</div>
                                    Checks if two strings are exactly the same, character by character
                                </div>
                            </div>
                        </div>

                        <!-- String Comparison -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-sort-alpha-down"></i>
                                    Alphabetical Compare
                                </h4>
                                <div class="mb-2">
                                    <input type="text" class="form-control telugu-input mb-2" id="compareStr1" 
                                           placeholder="First string...">
                                    <input type="text" class="form-control telugu-input" id="compareStr2" 
                                           placeholder="Second string...">
                                </div>
                                <button class="btn telugu-btn" onclick="compareStrings()">
                                    <i class="fas fa-sort"></i> Compare
                                </button>
                                <div class="result-box" id="compareResult">
                                    <span class="text-muted">Comparison result will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">How it works:</div>
                                    Tells you which string comes first alphabetically
                                </div>
                            </div>
                        </div>

                        <!-- Starts With / Ends With -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-arrow-right"></i>
                                    Starts/Ends With
                                </h4>
                                <div class="mb-2">
                                    <input type="text" class="form-control telugu-input mb-2" id="patternText" 
                                           placeholder="Full text...">
                                    <input type="text" class="form-control telugu-input mb-2" id="patternSearch" 
                                           placeholder="Pattern to find...">
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn telugu-btn" onclick="checkPattern('starts')">
                                            <i class="fas fa-play"></i> Starts With
                                        </button>
                                        <button class="btn telugu-btn" onclick="checkPattern('ends')">
                                            <i class="fas fa-stop"></i> Ends With
                                        </button>
                                    </div>
                                </div>
                                <div class="result-box" id="patternResult">
                                    <span class="text-muted">Pattern result will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Example:</div>
                                    Does <span class="telugu-text fw-bold">తెలుగు భాష</span> start with <span class="telugu-text fw-bold">తెలుగు</span>? Yes!
                                </div>
                            </div>
                        </div>

                        <!-- Contains Check -->
                        <div class="col-md-6 mb-4">
                            <div class="tool-section">
                                <h4 class="tool-title">
                                    <i class="fas fa-search"></i>
                                    Contains Check
                                </h4>
                                <div class="mb-2">
                                    <input type="text" class="form-control telugu-input mb-2" id="containsText" 
                                           placeholder="Text to search in...">
                                    <input type="text" class="form-control telugu-input" id="containsPattern" 
                                           placeholder="Text to find...">
                                </div>
                                <button class="btn telugu-btn" onclick="checkContains()">
                                    <i class="fas fa-search"></i> Check Contains
                                </button>
                                <div class="result-box" id="containsResult">
                                    <span class="text-muted">Contains result will appear here...</span>
                                </div>
                                <div class="example-box">
                                    <div class="example-label">Useful for:</div>
                                    Finding if a word or phrase exists within a larger text
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Telugu Playground JavaScript -->
    <script>
        // API Base URL
        const API_BASE = 'api.php';
        
        // Utility function to make API calls
        async function callAPI(endpoint, params = {}) {
            try {
                // Try multiple URL formats to ensure compatibility
                const baseUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/');
                
                // Primary: Clean URL format
                let apiUrl = baseUrl + API_BASE + '/' + endpoint;
                
                // Build query parameters
                const queryParams = new URLSearchParams();
                Object.keys(params).forEach(key => {
                    if (params[key] !== '') {
                        queryParams.append(key, params[key]);
                    }
                });
                
                const queryString = queryParams.toString();
                const finalUrl = apiUrl + (queryString ? '?' + queryString : '');
                
                console.log('Making API call to:', finalUrl); // Debug log
                console.log('Parameters being sent:', params); // Debug params
                
                // Add cache-busting to ensure fresh requests
                const cacheBustUrl = finalUrl + (finalUrl.includes('?') ? '&' : '?') + '_t=' + Date.now();
                console.log('Cache-busted URL:', cacheBustUrl);
                
                let response = await fetch(cacheBustUrl, {
                    method: 'GET',
                    cache: 'no-cache',
                    headers: {
                        'Cache-Control': 'no-cache'
                    }
                });
                
                if (!response.ok) {
                    // Try to get the response body for better error messages
                    let errorMessage = `HTTP error! status: ${response.status}, statusText: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        if (errorData.error || errorData.message) {
                            errorMessage += ` - ${errorData.error || errorData.message}`;
                        }
                        console.log('API Error Response:', errorData);
                    } catch (e) {
                        console.log('Could not parse error response as JSON');
                        console.log('Raw response text:', await response.text());
                    }
                    throw new Error(errorMessage);
                }
                
                const data = await response.json();
                console.log('API Success Response:', data); // Debug successful response
                return data;
            } catch (error) {
                console.error('API Error:', error);
                return { success: false, error: 'Network error occurred: ' + error.message };
            }
        }
        
        // Show loading state
        function showLoading(elementId) {
            document.getElementById(elementId).innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
        
        // Show result
        function showResult(elementId, result, isError = false) {
            const element = document.getElementById(elementId);
            if (isError) {
                element.innerHTML = `<i class="fas fa-exclamation-triangle text-danger"></i> <strong>Error:</strong> ${result}`;
                element.style.borderColor = '#dc3545';
                element.style.backgroundColor = '#f8d7da';
            } else {
                element.innerHTML = result;
                element.style.borderColor = '#28a745';
                element.style.backgroundColor = 'white';
            }
        }
        
        // Debug function to test API connectivity
        async function testAPI() {
            console.log('Testing API connectivity...');
            const result = await callAPI('text/length', { string: 'test' });
            console.log('API Test Result:', result);
            return result;
        }
        
        // Text Operations Functions
        async function getTextLength() {
            const input = document.getElementById('lengthInput').value;
            if (!input.trim()) {
                showResult('lengthResult', 'Please enter some text', true);
                return;
            }
            
            showLoading('lengthResult');
            const result = await callAPI('text/length', { string: input });
            
            if (result.success) {
                showResult('lengthResult', `<i class="fas fa-ruler-horizontal text-success"></i> Length: <strong>${result.result}</strong> characters`);
            } else {
                showResult('lengthResult', result.error || 'Error calculating length', true);
            }
        }
        
        async function reverseText() {
            const input = document.getElementById('reverseInput').value;
            if (!input.trim()) {
                showResult('reverseResult', 'Please enter some text', true);
                return;
            }
            
            showLoading('reverseResult');
            const result = await callAPI('text/reverse', { string: input });
            
            if (result.success) {
                showResult('reverseResult', `<i class="fas fa-undo text-success"></i> <span class="telugu-text">${result.result}</span>`);
            } else {
                showResult('reverseResult', result.error || 'Error reversing text', true);
            }
        }
        
        async function replaceText() {
            const text = document.getElementById('replaceText').value;
            const search = document.getElementById('searchText').value;
            const replace = document.getElementById('replaceWith').value;
            
            if (!text.trim() || !search.trim()) {
                showResult('replaceResult', 'Please enter text and search term', true);
                return;
            }
            
            showLoading('replaceResult');
            const result = await callAPI('text/replace', { 
                string: text, 
                search: search, 
                replace: replace 
            });
            
            if (result.success) {
                showResult('replaceResult', `<i class="fas fa-edit text-success"></i> <span class="telugu-text">${result.result}</span>`);
            } else {
                showResult('replaceResult', result.error || 'Error replacing text', true);
            }
        }
        
        async function randomizeText() {
            const input = document.getElementById('randomizeInput').value;
            if (!input.trim()) {
                showResult('randomizeResult', 'Please enter some text', true);
                return;
            }
            
            showLoading('randomizeResult');
            const result = await callAPI('text/randomize', { string: input });
            
            if (result.success) {
                showResult('randomizeResult', `<i class="fas fa-random text-success"></i> <span class="telugu-text">${result.result}</span>`);
            } else {
                showResult('randomizeResult', result.error || 'Error randomizing text', true);
            }
        }
        
        // Character Analysis Functions
        async function getLogicalChars() {
            const input = document.getElementById('logicalInput').value;
            if (!input.trim()) {
                showResult('logicalResult', 'Please enter some Telugu text', true);
                return;
            }
            
            showLoading('logicalResult');
            const result = await callAPI('characters/logical', { string: input, language: 'telugu' });
            
            if (result.success) {
                showResult('logicalResult', `<i class="fas fa-atom text-success"></i> <span class="telugu-text">${result.result}</span>`);
            } else {
                showResult('logicalResult', result.error || 'Error analyzing characters', true);
            }
        }
        
        async function getUnicodePoints() {
            const input = document.getElementById('unicodeInput').value;
            if (!input.trim()) {
                showResult('unicodeResult', 'Please enter some text', true);
                return;
            }
            
            showLoading('unicodeResult');
            const result = await callAPI('characters/codepoints', { string: input, language: 'telugu' });
            
            if (result.success) {
                showResult('unicodeResult', `<i class="fas fa-code text-success"></i> ${result.result}`);
            } else {
                showResult('unicodeResult', result.error || 'Error getting Unicode points', true);
            }
        }
        
        async function checkCharacterType() {
            const input = document.getElementById('charTypeInput').value;
            if (!input.trim()) {
                showResult('charTypeResult', 'Please enter a character', true);
                return;
            }
            
            showLoading('charTypeResult');
            
            // Check both vowel and consonant
            const vowelResult = await callAPI('validation/is-vowel', { string: input, language: 'telugu' });
            const consonantResult = await callAPI('validation/is-consonant', { string: input, language: 'telugu' });
            
            if (vowelResult.success && consonantResult.success) {
                const isVowel = vowelResult.result === 'true' || vowelResult.result === '1' || vowelResult.result === true;
                const isConsonant = consonantResult.result === 'true' || consonantResult.result === '1' || consonantResult.result === true;
                
                if (isVowel) {
                    showResult('charTypeResult', `<i class="fas fa-check-circle text-success"></i> <span class="telugu-text">${input}</span> is a <strong>Vowel (స్వరం)</strong>`);
                } else if (isConsonant) {
                    showResult('charTypeResult', `<i class="fas fa-check-circle text-success"></i> <span class="telugu-text">${input}</span> is a <strong>Consonant (వ్యంజనం)</strong>`);
                } else {
                    showResult('charTypeResult', `<i class="fas fa-question-circle text-warning"></i> <span class="telugu-text">${input}</span> is <strong>Neither vowel nor consonant</strong>`);
                }
            } else {
                showResult('charTypeResult', 'Error checking character type', true);
            }
        }
        
        async function getBaseCharacters() {
            const input = document.getElementById('baseCharsInput').value;
            if (!input.trim()) {
                showResult('baseCharsResult', 'Please enter some Telugu text', true);
                return;
            }
            
            showLoading('baseCharsResult');
            const result = await callAPI('characters/base', { string: input, language: 'telugu' });
            
            if (result.success) {
                showResult('baseCharsResult', `<i class="fas fa-layer-group text-success"></i> <span class="telugu-text">${result.result}</span>`);
            } else {
                showResult('baseCharsResult', result.error || 'Error extracting base characters', true);
            }
        }
        
        // Word Analysis Functions
        async function checkAnagram() {
            const word1 = document.getElementById('anagramWord1').value;
            const word2 = document.getElementById('anagramWord2').value;
            
            if (!word1.trim() || !word2.trim()) {
                showResult('anagramResult', 'Please enter both words', true);
                return;
            }
            
            showLoading('anagramResult');
            const result = await callAPI('analysis/is-anagram', { string: word1, input2: word2, language: 'telugu' });
            
            if (result.success) {
                const isAnagram = result.result === 'true' || result.result === '1' || result.result === true;
                if (isAnagram) {
                    showResult('anagramResult', `<i class="fas fa-check-circle text-success"></i> Yes! These words are <strong> anagrams </strong> of each other`);
                } else {
                    showResult('anagramResult', `<i class="fas fa-times-circle text-warning"></i> No, these words are <strong>not anagrams</strong>`);
                }
            } else {
                showResult('anagramResult', result.error || 'Error checking anagram', true);
            }
        }
        
        async function checkPalindrome() {
            const input = document.getElementById('palindromeInput').value;
            if (!input.trim()) {
                showResult('palindromeResult', 'Please enter a word', true);
                return;
            }
            
            showLoading('palindromeResult');
            const result = await callAPI('analysis/is-palindrome', { string: input, language: 'telugu' });
            
            if (result.success) {
                const isPalindrome = result.result === 'true' || result.result === '1' || result.result === true;
                if (isPalindrome) {
                    showResult('palindromeResult', `<i class="fas fa-check-circle text-success"></i> Yes! <span class="telugu-text">${input}</span> is a <strong> palindrome </strong>`);
                } else {
                    showResult('palindromeResult', `<i class="fas fa-times-circle text-warning"></i> No, <span class="telugu-text">${input}</span> is <strong>not a palindrome</strong>`);
                }
            } else {
                showResult('palindromeResult', result.error || 'Error checking palindrome', true);
            }
        }
        
        async function getWordStrength() {
            const input = document.getElementById('strengthInput').value;
            if (!input.trim()) {
                showResult('strengthResult', 'Please enter a Telugu word', true);
                return;
            }
            
            showLoading('strengthResult');
            const result = await callAPI('analysis/word-strength', { string: input, language: 'telugu' });
            
            if (result.success) {
                showResult('strengthResult', `<i class="fas fa-dumbbell text-success"></i> Word strength: <strong>${result.result}</strong>`);
            } else {
                showResult('strengthResult', result.error || 'Error calculating word strength', true);
            }
        }
        
        async function detectLanguage() {
            const input = document.getElementById('languageInput').value;
            if (!input.trim()) {
                showResult('languageResult', 'Please enter some text', true);
                return;
            }
            
            showLoading('languageResult');
            const result = await callAPI('analysis/detect-language', { string: input, language: 'telugu' });
            
            if (result.success) {
                showResult('languageResult', `<i class="fas fa-globe text-success"></i> Detected language: <strong>${result.result}</strong>`);
            } else {
                showResult('languageResult', result.error || 'Error detecting language', true);
            }
        }
        
        // String Comparison Functions
        async function checkEquality() {
            const str1 = document.getElementById('equalStr1').value;
            const str2 = document.getElementById('equalStr2').value;
            
            if (!str1.trim() || !str2.trim()) {
                showResult('equalityResult', 'Please enter both strings', true);
                return;
            }
            
            showLoading('equalityResult');
            const result = await callAPI('comparison/equals', { str1: str1, str2: str2 });
            
            if (result.success) {
                const isEqual = result.result === 'true' || result.result === '1';
                if (isEqual) {
                    showResult('equalityResult', `<i class="fas fa-check-circle text-success"></i> The strings are <strong>equal</strong>`);
                } else {
                    showResult('equalityResult', `<i class="fas fa-times-circle text-warning"></i> The strings are <strong>not equal</strong>`);
                }
            } else {
                showResult('equalityResult', result.error || 'Error comparing strings', true);
            }
        }
        
        async function compareStrings() {
            const str1 = document.getElementById('compareStr1').value;
            const str2 = document.getElementById('compareStr2').value;
            
            if (!str1.trim() || !str2.trim()) {
                showResult('compareResult', 'Please enter both strings', true);
                return;
            }
            
            showLoading('compareResult');
            const result = await callAPI('comparison/compare', { str1: str1, str2: str2 });
            
            if (result.success) {
                const compareValue = parseInt(result.result);
                if (compareValue < 0) {
                    showResult('compareResult', `<i class="fas fa-arrow-up text-primary"></i> "<span class="telugu-text">${str1}</span>" comes <strong>before</strong> "<span class="telugu-text">${str2}</span>" alphabetically`);
                } else if (compareValue > 0) {
                    showResult('compareResult', `<i class="fas fa-arrow-down text-primary"></i> "<span class="telugu-text">${str1}</span>" comes <strong>after</strong> "<span class="telugu-text">${str2}</span>" alphabetically`);
                } else {
                    showResult('compareResult', `<i class="fas fa-equals text-success"></i> The strings are <strong>equal</strong>`);
                }
            } else {
                showResult('compareResult', result.error || 'Error comparing strings', true);
            }
        }
        
        async function checkPattern(type) {
            const text = document.getElementById('patternText').value;
            const pattern = document.getElementById('patternSearch').value;
            
            if (!text.trim() || !pattern.trim()) {
                showResult('patternResult', 'Please enter both text and pattern', true);
                return;
            }
            
            showLoading('patternResult');
            const endpoint = type === 'starts' ? 'comparison/starts-with' : 'comparison/ends-with';
            const params = type === 'starts' ? 
                { string: text, prefix: pattern } : 
                { string: text, suffix: pattern };
            
            const result = await callAPI(endpoint, params);
            
            if (result.success) {
                const matches = result.result === 'true' || result.result === '1';
                const action = type === 'starts' ? 'start with' : 'end with';
                const icon = type === 'starts' ? 'fa-play' : 'fa-stop';
                
                if (matches) {
                    showResult('patternResult', `<i class="fas ${icon} text-success"></i> Yes! "<span class="telugu-text">${text}</span>" does ${action} "<span class="telugu-text">${pattern}</span>"`);
                } else {
                    showResult('patternResult', `<i class="fas fa-times text-warning"></i> No, "<span class="telugu-text">${text}</span>" does not ${action} "<span class="telugu-text">${pattern}</span>"`);
                }
            } else {
                showResult('patternResult', result.error || 'Error checking pattern', true);
            }
        }
        
        async function checkContains() {
            const text = document.getElementById('containsText').value;
            const pattern = document.getElementById('containsPattern').value;
            
            if (!text.trim() || !pattern.trim()) {
                showResult('containsResult', 'Please enter both text and search term', true);
                return;
            }
            
            showLoading('containsResult');
            const result = await callAPI('validation/contains-string', { 
                string: text, 
                substring: pattern 
            });
            
            if (result.success) {
                const contains = result.result === 'true' || result.result === '1';
                if (contains) {
                    showResult('containsResult', `<i class="fas fa-check-circle text-success"></i> Yes! "<span class="telugu-text">${text}</span>" contains "<span class="telugu-text">${pattern}</span>"`);
                } else {
                    showResult('containsResult', `<i class="fas fa-times-circle text-warning"></i> No, "<span class="telugu-text">${text}</span>" does not contain "<span class="telugu-text">${pattern}</span>"`);
                }
            } else {
                showResult('containsResult', result.error || 'Error checking contains', true);
            }
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add some welcome animation or initialization if needed
            console.log('Telugu Playground loaded successfully!');
            
            // Test API connectivity on page load
            testAPI().then(result => {
                if (result.success) {
                    console.log('✅ API connectivity test passed!');
                } else {
                    console.error('❌ API connectivity test failed:', result.error);
                }
            });
        });
        
        // Global function for manual testing from browser console
        window.debugTeluguAPI = testAPI;
    </script>
</body>
</html>