<?php
require_once __DIR__ . '/includes/llm_handler.php';

// Load local .env similarly to chat_api.php, if vlucas/phpdotenv is available.
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Define default LLM choices that are known to be supported by chat_api.php.
$defaultLlms = [
    ['provider' => 'groq', 'model' => 'llama-3.3-70b-versatile', 'label' => 'Groq - llama-3.3-70b-versatile'],
    ['provider' => 'gemini', 'model' => 'gemini-2.0-flash', 'label' => 'Gemini - gemini-2.0-flash'],
    ['provider' => 'openai', 'model' => 'gpt-4o-mini', 'label' => 'OpenAI - gpt-4o-mini'],
];

// Start with configured models if available.
$llmChoices = function_exists('llm_get_configured_models') ? llm_get_configured_models() : [];

// If no configured models, fall back to the defaults.
if (empty($llmChoices)) {
    $llmChoices = $defaultLlms;
}

// Align the dropdown options with what chat_api.php will honor.
$allowedProviders = ['groq', 'gemini', 'openai'];
$llmChoices = array_values(array_filter($llmChoices, function ($choice) use ($allowedProviders) {
    $provider = strtolower($choice['provider'] ?? '');
    return in_array($provider, $allowedProviders, true);
}));

// If filtering removed all entries (e.g., only unsupported providers were configured),
// fall back to the known-good defaults.
if (empty($llmChoices)) {
    $llmChoices = $defaultLlms;
}
$defaultProvider = strtolower(getenv('LLM_PROVIDER') ?: '');
$defaultModel = trim(getenv('LLM_MODEL') ?: '');
$selectedChoice = '';

foreach ($llmChoices as $choice) {
    $provider = strtolower($choice['provider'] ?? '');
    $model = trim($choice['model'] ?? '');

    if ($defaultModel !== '' && $defaultProvider !== '' && $provider === $defaultProvider && $model === $defaultModel) {
        $selectedChoice = $provider . ':' . $model;
        break;
    }

    if ($selectedChoice === '' && $defaultProvider !== '' && $provider === $defaultProvider) {
        $selectedChoice = $provider . ':' . $model;
    }
}

if ($selectedChoice === '' && !empty($llmChoices[0])) {
    $selectedChoice = strtolower($llmChoices[0]['provider']) . ':' . $llmChoices[0]['model'];
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Text Analyzer - Ananya</title>

        <!-- CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/searchpanes/2.2.0/css/searchPanes.bootstrap5.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Markdown rendering for chat responses -->
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
        
        <style>
            .parser-container {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .input-section {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .results-section {
                background: white;
                border-radius: 10px;
                padding: 2rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            #parsing-input {
                font-family: 'Noto Sans Telugu', sans-serif;
                font-size: 1.1rem;
                line-height: 1.6;
            }
            
            .process-btn {
                background: linear-gradient(45deg, #2563eb, #7c3aed);
                border: none;
                color: white;
                padding: 12px 40px;
                border-radius: 25px;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            
            .process-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
                color: white;
            }
            
            .process-btn:disabled {
                opacity: 0.7;
                transform: none;
            }
            
            .numerical-results-section h5 {
                color: white;
                text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            
            .dt-buttons {
                margin: 1rem 0;
                text-align: center;
            }
            
            .dt-buttons .btn {
                margin: 0.25rem;
                font-size: 0.875rem;
                border-radius: 8px;
                transition: all 0.2s ease;
            }
            
            .dt-buttons .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }

            .language-select {
                display: block;
                width: auto;
                max-width: 100%;
            }

            .required-indicator {
                color: #dc3545;
                font-weight: 700;
            }

            #chat-window {
                min-height: 320px;
                height: 320px;
                max-height: 78vh;
                overflow: auto;
                padding: 12px;
                background: #ffffff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                transition: height 0.2s ease;
            }

            .chat-bubble {
                padding: 10px 12px;
                border-radius: 12px;
                max-width: 75%;
                line-height: 1.4;
                word-break: break-word;
            }

            .chat-bubble.user {
                background: #2563eb;
                color: #ffffff;
                border-bottom-right-radius: 4px;
            }

            .chat-bubble.assistant {
                background: #f1f3f5;
                color: #111827;
                border-bottom-left-radius: 4px;
            }

            /* Puzzle bubbles expand to fill the chat window so the grid doesn't need to scroll. */
            .chat-bubble.assistant:has(.puzzle-output) {
                max-width: 100%;
            }

            .chat-bubble.assistant:has(.crossword-render) {
                max-width: 100%;
            }

            .chat-bubble.assistant .puzzle-output {
                margin: 0;
                font-family: "Noto Sans Mono", Consolas, "Courier New", monospace;
                font-size: 0.92rem;
                line-height: 1.25;
                letter-spacing: 0;
                white-space: pre;
                overflow-x: auto;
            }

            .chat-bubble.assistant .puzzle-output.puzzle-output-telugu {
                font-family: "Noto Sans Telugu", "Noto Sans Mono", Consolas, "Courier New", monospace;
            }

            .crossword-render {
                width: 100%;
                background: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 10px;
                padding: 12px;
            }

            .crossword-header h5 {
                margin: 0 0 6px;
                font-size: 1.05rem;
                font-weight: 700;
            }

            .crossword-meta {
                font-size: 0.85rem;
                color: #4b5563;
                line-height: 1.35;
            }

            .crossword-toolbar {
                margin: 10px 0;
            }

            .crossword-grid-wrap {
                overflow: auto;
                margin-bottom: 12px;
            }

            .crossword-grid {
                border-collapse: collapse;
                margin: 0;
            }

            .crossword-grid td {
                width: 30px;
                height: 30px;
                border: 1px solid #1f2937;
                position: relative;
            }

            .crossword-grid td.blocked {
                background: #111827;
                border-color: #111827;
            }

            .crossword-grid td.open {
                background: #ffffff;
            }

            .crossword-grid .cell-number {
                position: absolute;
                top: 1px;
                left: 2px;
                font-size: 9px;
                font-weight: 700;
                color: #111827;
                line-height: 1;
            }

            .crossword-clues h6 {
                margin-bottom: 4px;
                font-weight: 700;
            }

            .crossword-clues ol {
                margin-bottom: 0;
                padding-left: 1.1rem;
                font-size: 0.86rem;
                line-height: 1.35;
            }

            .crossword-clues .clue-list {
                list-style: none;
                margin-bottom: 0;
                padding-left: 0;
                font-size: 0.86rem;
                line-height: 1.35;
            }

            .crossword-clues .clue-list li {
                margin-bottom: 2px;
            }

            .crossword-clues .clue-num {
                font-weight: 700;
            }

            .crossword-answer-key {
                margin-top: 10px;
                font-size: 0.84rem;
                border-top: 1px dashed #cfd4da;
                padding-top: 8px;
            }

            .crossword-answer-key summary {
                cursor: pointer;
                font-weight: 600;
                color: #374151;
            }

            .crossword-answer-key ul {
                margin: 8px 0 0;
                padding-left: 1.1rem;
            }

            .chat-bubble.assistant:has(.wordfind-render) {
                max-width: 100%;
            }

            .wordfind-render {
                width: 100%;
                background: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 10px;
                padding: 12px;
            }

            .wordfind-header h5 {
                margin: 0 0 6px;
                font-size: 1.05rem;
                font-weight: 700;
            }

            .wordfind-meta {
                font-size: 0.85rem;
                color: #4b5563;
                line-height: 1.35;
            }

            .wordfind-toolbar {
                margin: 10px 0;
            }

            .wordfind-grid-wrap {
                overflow: auto;
                margin-bottom: 12px;
            }

            .wordfind-grid {
                border-collapse: collapse;
                margin: 0;
            }

            .wordfind-grid td {
                width: 30px;
                height: 30px;
                border: 1px solid #1f2937;
                text-align: center;
                vertical-align: middle;
                font-size: 0.9rem;
                font-weight: 700;
                color: #111827;
            }

            .wordfind-words h6 {
                margin-bottom: 4px;
                font-weight: 700;
            }

            .wordfind-words ul {
                margin: 0;
                padding-left: 1.1rem;
                columns: 2;
                column-gap: 18px;
                font-size: 0.86rem;
                line-height: 1.35;
            }

            .wordfind-answer-key {
                margin-top: 10px;
                font-size: 0.84rem;
                border-top: 1px dashed #cfd4da;
                padding-top: 8px;
            }

            .wordfind-answer-key summary {
                cursor: pointer;
                font-weight: 600;
                color: #374151;
            }

            .wordfind-answer-key ul {
                margin: 8px 0 0;
                padding-left: 1.1rem;
            }

            .source-badge {
                font-size: 0.75rem;
                margin-top: 6px;
                padding: 2px 8px;
                border-radius: 999px;
                display: inline-block;
            }

            .source-badge.mcp {
                background: #e8f5e9;
                color: #2e7d32;
            }

            .source-badge.fallback {
                background: #fff3e0;
                color: #ef6c00;
            }

            .llm-meta {
                margin-top: 4px;
                font-size: 0.75rem;
                color: #6b7280;
            }

            .chat-typing span {
                display: inline-block;
                width: 6px;
                height: 6px;
                margin-right: 4px;
                border-radius: 50%;
                background: #9aa0a6;
                animation: typing-bounce 1.2s infinite ease-in-out;
            }

            .chat-typing span:nth-child(2) {
                animation-delay: 0.2s;
            }

            .chat-typing span:nth-child(3) {
                animation-delay: 0.4s;
            }

            @keyframes typing-bounce {
                0%, 80%, 100% { transform: scale(0.6); opacity: 0.6; }
                40% { transform: scale(1); opacity: 1; }
            }
            
        </style>
        
        <!-- Include Ananya Header Component -->
        <?php 
        $root_path = '';
        $css_path = 'css/';
        include 'includes/header.php'; 
        ?>
        
        <!-- JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="js/analyzer.js?v=<?php echo time(); ?>"></script>
        
        <!-- Force browser to clear cache -->
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    </head>

    <body>
        <div class="main-content">
            <div class="parser-container">
                <div class="row page-header">
                    <div class="col-12">
                        <h2 class="text-center mb-4">Chat with Ananya</h2>
                        <p class="text-center text-muted">Interact with the AI assistant in Telugu or English</p>
                    </div>
                </div>
                
                <div class="input-section">
                    <div class="input-group mt-2 mb-3">
                        <input id="chat-input" class="form-control" placeholder="Describe the puzzle you want..." />
                        <button id="chat-send" class="btn btn-primary process-btn">
                            <i class="fas fa-paper-plane me-2"></i>Send
                        </button>
                    </div>

                    <small class="form-text text-muted mt-2 d-block mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Tip: Ask for puzzle generation with a theme and size, like "Create a word find with 12 words about animals" or "Create a crossword puzzle with a dog theme".
                    </small>

                    <div class="mb-3">
                        <a class="btn btn-outline-secondary btn-sm" href="bulk_puzzles.php">
                            <i class="fas fa-layer-group me-1"></i>Bulk Puzzle Generator
                        </a>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="language-select" class="form-label">
                                <i class="fas fa-language me-2"></i>Output Language
                                <span class="required-indicator ms-1" aria-hidden="true">*</span>
                                <span class="visually-hidden">required</span>
                            </label>
                            <select id="language-select" class="form-select language-select" required aria-required="true">
                                <option value="" selected disabled>Select output language</option>
                                <option value="english">English</option>
                                <option value="telugu">Telugu (తెలుగు)</option>
                                <option value="hindi">Hindi (हिन्दी)</option>
                                <option value="gujarati">Gujarati (ગુજરાતી)</option>
                                <option value="malayalam">Malayalam (മലയാളം)</option>
                            </select>
                            <div id="language-feedback" class="invalid-feedback">
                                Output language is required. Please select a language.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="llm-select" class="form-label">
                                <i class="fas fa-robot me-2"></i>LLM
                            </label>
                            <select id="llm-select" class="form-select language-select">
                                <?php foreach ($llmChoices as $choice): ?>
                                    <?php
                                        $provider = strtolower($choice['provider'] ?? '');
                                        $model = $choice['model'] ?? '';
                                        $value = $provider . ':' . $model;
                                        $label = $choice['label'] ?? ($provider . ' - ' . $model);
                                    ?>
                                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($value === $selectedChoice ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div id="chat-window"></div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script src="js/chat.js"></script>
</html>