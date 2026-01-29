<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean API Endpoints - Ananya</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Prism.js for syntax highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    
    <style>
        .endpoint-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #2563eb;
        }
        .endpoint-url {
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            border: 1px solid #e9ecef;
            margin: 0.5rem 0;
        }
        .old-url {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .new-url {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
        }
        .category-header {
            background: linear-gradient(45deg, #2563eb, #7c3aed);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin: 2rem 0 1rem 0;
        }
        .migration-info {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin: 2rem 0;
        }
    </style>
</head>

<body>
    <?php 
    $root_path = '';
    $css_path = 'css/';
    include 'includes/header.php'; 
    ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="row page-header">
                <div class="col-12">
                    <h2 class="text-center mb-4">Clean API Endpoints</h2>
                    <p class="text-center text-muted">Professional, RESTful API URLs without implementation details</p>
                </div>
            </div>

            <!-- Migration Info -->
            <div class="migration-info text-center">
                <h3><i class="fas fa-rocket me-2"></i>API Endpoints Upgraded!</h3>
                <p class="mb-0">Your API now features clean, professional URLs that hide implementation details and follow REST best practices.</p>
            </div>

            <!-- Text Operations -->
            <div class="category-header">
                <h4><i class="fas fa-font me-2"></i>Text Operations</h4>
            </div>

            <div class="endpoint-card">
                <h5>Get Text Length</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/getLength.php?string=hello
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/text/length?string=hello
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Reverse Text</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/reverse.php?string=hello
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/text/reverse?string=hello
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Replace Text</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/replace.php?string=hello&search=l&replace=x
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/text/replace?string=hello&search=l&replace=x
                </div>
            </div>

            <!-- Character Analysis -->
            <div class="category-header">
                <h4><i class="fas fa-search me-2"></i>Character Analysis</h4>
            </div>

            <div class="endpoint-card">
                <h5>Get Code Points</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/getCodePoints.php?string=అ
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/characters/codepoints?string=అ
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Get Logical Characters</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/getLogicalChars.php?string=అనన్య
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/characters/logical?string=అనన్య
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Character at Position</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/logicalCharAt.php?string=అనన్య&index=0
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/characters/logical-at?string=అనన్య&index=0
                </div>
            </div>

            <!-- Character Validation -->
            <div class="category-header">
                <h4><i class="fas fa-check-circle me-2"></i>Character Validation</h4>
            </div>

            <div class="endpoint-card">
                <h5>Is Character Consonant</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/isCharConsonant.php?char=క
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/validation/is-consonant?char=క
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Contains Character</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/containsChar.php?string=అనన్య&char=న
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/validation/contains-char?string=అనన్య&char=న
                </div>
            </div>

            <!-- Word Analysis -->
            <div class="category-header">
                <h4><i class="fas fa-chart-line me-2"></i>Word Analysis</h4>
            </div>

            <div class="endpoint-card">
                <h5>Is Anagram</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/isAnagram.php?word1=listen&word2=silent
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/analysis/is-anagram?word1=listen&word2=silent
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Is Palindrome</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/isPalindrome.php?string=racecar
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/analysis/is-palindrome?string=racecar
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Word Strength</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/getWordStrength.php?word=అనన్య
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/analysis/word-strength?word=అనన్య
                </div>
            </div>

            <!-- String Comparison -->
            <div class="category-header">
                <h4><i class="fas fa-balance-scale me-2"></i>String Comparison</h4>
            </div>

            <div class="endpoint-card">
                <h5>Compare Strings</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/compareTo.php?str1=hello&str2=world
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/comparison/compare?str1=hello&str2=world
                </div>
            </div>

            <div class="endpoint-card">
                <h5>Starts With</h5>
                <div class="old-url endpoint-url">
                    <strong>Old:</strong> /api/startsWith.php?string=hello&prefix=he
                </div>
                <div class="new-url endpoint-url">
                    <strong>New:</strong> /api/comparison/starts-with?string=hello&prefix=he
                </div>
            </div>

            <!-- Key Benefits -->
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5>Enhanced Security</h5>
                        <p>No technology stack disclosure, reducing attack surface.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <i class="fas fa-code fa-3x text-success mb-3"></i>
                        <h5>Professional URLs</h5>
                        <p>Clean, RESTful endpoints following industry standards.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <i class="fas fa-arrows-alt fa-3x text-info mb-3"></i>
                        <h5>Future Flexibility</h5>
                        <p>Easy to migrate backend technology without URL changes.</p>
                    </div>
                </div>
            </div>

            <!-- Backward Compatibility Notice -->
            <div class="alert alert-info mt-4">
                <h5><i class="fas fa-info-circle me-2"></i>Backward Compatibility</h5>
                <p class="mb-0">
                    <strong>Good news!</strong> Your old API endpoints still work! The system automatically handles both old and new URL formats, 
                    so existing applications won't break during the transition.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Prism.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>