<?php
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

            #chat-window {
                height: 320px;
                overflow: auto;
                padding: 12px;
                background: #ffffff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
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
                    <div class="mb-3">
                        <label for="language-select" class="form-label">
                            <i class="fas fa-language me-2"></i>Language
                        </label>
                        <select id="language-select" class="form-select language-select">
                            <option value="english" selected>English</option>
                            <option value="telugu">Telugu (తెలుగు)</option>
                        </select>
                    </div>

                    <div class="input-group mt-2">
                        <input id="chat-input" class="form-control" placeholder="How can I help you today?" />
                        <button id="chat-send" class="btn btn-primary process-btn">
                            <i class="fas fa-paper-plane me-2"></i>Send
                        </button>
                    </div>

                    <small class="form-text text-muted mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        You can ask questions, seek information, or have a conversation in either English or Telugu. Just type your message and click "Send" to see the response.
                    </small>

                    <div class="mt-3">
                        <div id="chat-window"></div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script src="js/chat.js"></script>
</html>