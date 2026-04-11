<?php
require_once __DIR__ . '/includes/llm_handler.php';
llm_bootstrap_env_once(__DIR__);

$bulkLlms = [
    ['provider' => 'groq', 'model' => 'llama-3.3-70b-versatile', 'label' => 'Groq - llama-3.3-70b-versatile'],
    ['provider' => 'gemini', 'model' => 'gemini-2.0-flash', 'label' => 'Gemini - gemini-2.0-flash'],
    ['provider' => 'openai', 'model' => 'gpt-4o-mini', 'label' => 'OpenAI - gpt-4o-mini'],
];

$providerAvailability = [];
foreach (['groq', 'gemini', 'openai'] as $providerName) {
    $providerAvailability[$providerName] = function_exists('llm_provider_has_api_key')
        ? llm_provider_has_api_key($providerName)
        : true;
}

$bulkSelectedChoice = '';
foreach ($bulkLlms as $choice) {
    $provider = strtolower($choice['provider']);
    if ($providerAvailability[$provider] ?? false) {
        $bulkSelectedChoice = $provider . ':' . $choice['model'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Puzzle Generator - Ananya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f6f8fb;
        }

        .page-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .panel {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .required-indicator {
            color: #dc3545;
            font-weight: 700;
        }

        .puzzle-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .puzzle-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .puzzle-meta {
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 8px;
        }

        .puzzle-output {
            margin: 0;
            white-space: pre;
            overflow-x: auto;
            font-family: "Noto Sans Mono", Consolas, "Courier New", monospace;
            font-size: 0.9rem;
            line-height: 1.25;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
        }

        .puzzle-output.telugu {
            font-family: "Noto Sans Telugu", "Noto Sans Mono", Consolas, "Courier New", monospace;
        }

        .crossword-render,
        .wordfind-render {
            width: 100%;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 12px;
        }

        .crossword-header h5,
        .wordfind-header h5 {
            margin: 0 0 6px;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .crossword-meta,
        .wordfind-meta {
            font-size: 0.85rem;
            color: #4b5563;
            line-height: 1.35;
        }

        .crossword-grid-wrap,
        .wordfind-grid-wrap {
            position: relative;
            overflow: auto;
            margin: 10px 0 12px;
        }

        .crossword-grid,
        .wordfind-grid {
            border-collapse: collapse;
            margin: 0;
        }

        .crossword-grid td,
        .wordfind-grid td {
            width: 30px;
            height: 30px;
            border: 1px solid #1f2937;
            position: relative;
            text-align: center;
            vertical-align: middle;
            font-size: 0.9rem;
            font-weight: 700;
            color: #111827;
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

        .crossword-clues h6,
        .wordfind-words h6 {
            margin-bottom: 4px;
            font-weight: 700;
        }

        .crossword-clues .clue-list,
        .wordfind-words ul {
            margin: 0;
            padding-left: 1.1rem;
            font-size: 0.86rem;
            line-height: 1.35;
        }

        .crossword-clues .clue-list {
            list-style: none;
            padding-left: 0;
        }

        .crossword-clues .clue-num {
            font-weight: 700;
        }

        .wordfind-words ul {
            columns: 2;
            column-gap: 18px;
        }

        .crossword-answer-key,
        .wordfind-answer-key {
            margin-top: 10px;
            font-size: 0.84rem;
            border-top: 1px dashed #cfd4da;
            padding-top: 8px;
        }

        .crossword-answer-key summary,
        .wordfind-answer-key summary {
            cursor: pointer;
            font-weight: 600;
            color: #374151;
        }

        .crossword-answer-key ul,
        .wordfind-answer-key ul {
            margin: 8px 0 0;
            padding-left: 1.1rem;
        }

        .answer-line-overlay {
            position: absolute;
            pointer-events: none;
            z-index: 3;
            overflow: visible;
        }

        .answer-line-overlay line {
            fill: none;
            stroke-linecap: round;
        }

        .answer-line-overlay.crossword-lines line {
            stroke: #005fcc;
            stroke-width: 5;
        }

        .answer-line-overlay.wordfind-lines line {
            stroke: #8a3d00;
            stroke-width: 5;
            stroke-dasharray: 6 3;
        }

        .print-answer-key-pages {
            display: none;
        }

        .answer-key-page-item {
            border-top: 1px solid #d1d5db;
            padding-top: 12px;
            margin-top: 12px;
        }

        .answer-key-page-item h3 {
            margin: 0 0 6px;
            font-size: 18px;
            font-weight: 700;
        }

        .answer-key-type {
            color: #4b5563;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .answer-key-page-item ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        .answer-key-layout {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .answer-key-left {
            flex: 1 1 auto;
            min-width: 0;
        }

        .answer-key-right {
            flex: 0 0 auto;
            width: 2in;
        }

        .solved-mini-title {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 4px;
            text-align: center;
        }

        .mini-solved-box {
            width: 2in;
            height: 2in;
            border: 1px solid #9ca3af;
            background: #fff;
            overflow: hidden;
            position: relative;
        }

        .mini-solved-grid {
            width: 100%;
            height: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .mini-solved-grid td {
            border: 1px solid #d1d5db;
            padding: 0;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
            line-height: 1;
            font-weight: 600;
        }

        .mini-crossword-grid td.blocked {
            background: #111827;
            border-color: #111827;
            color: transparent;
        }

        .mini-solved-overlay {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }

        .mini-solved-overlay line {
            fill: none;
            stroke-linecap: round;
        }

        .mini-solved-overlay-crossword line {
            stroke: #0b5ed7;
            stroke-width: 1.4;
        }

        .mini-solved-overlay-wordfind line {
            stroke: #9a3412;
            stroke-width: 1.4;
            stroke-dasharray: 2 1;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .crossword-answer-key,
            .wordfind-answer-key,
            .answer-line-overlay {
                display: none !important;
            }

            .panel {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            .puzzle-card {
                page-break-inside: avoid;
                break-inside: avoid;
                border: none;
                padding: 0;
            }

            .crossword-render,
            .wordfind-render,
            .crossword-grid-wrap,
            .wordfind-grid-wrap {
                border: none;
                box-shadow: none;
            }

            .print-answer-key-pages {
                display: block;
                page-break-before: always;
                break-before: page;
                margin-top: 18px;
            }

            .answer-key-page-item {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .answer-key-layout {
                gap: 10px;
            }
        }
    </style>
</head>
<body>
<div class="main-content no-print">
<?php
$root_path = '';
$css_path = 'css/';
include 'includes/header.php';
?>
</div>

<div class="page-wrap">
    <div class="panel no-print">
        <h3 class="mb-3"><i class="fas fa-layer-group me-2"></i>Bulk Word-Search Generator</h3>
        <p class="text-muted mb-3">Upload a text file with one theme per line, or paste themes manually. The tool generates puzzles with defaults: 10 words, 16 x 12 grid.</p>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="theme-file" class="form-label">Theme Text File <span class="text-muted">(.txt)</span></label>
                <input id="theme-file" type="file" class="form-control" accept=".txt,text/plain">
            </div>
            <div class="col-md-2">
                <label for="output-language" class="form-label">Output Language <span class="required-indicator">*</span></label>
                <select id="output-language" class="form-select" required>
                    <option value="telugu" selected>Telugu</option>
                    <option value="english">English</option>
                    <option value="hindi">Hindi</option>
                    <option value="gujarati">Gujarati</option>
                    <option value="malayalam">Malayalam</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="bulk-llm-select" class="form-label">LLM <span class="required-indicator">*</span></label>
                <select id="bulk-llm-select" class="form-select" required>
                    <?php foreach ($bulkLlms as $choice): ?>
                        <?php
                            $provider = strtolower($choice['provider']);
                            $model = trim((string)$choice['model']);
                            $value = $provider . ':' . $model;
                            $isEnabled = $providerAvailability[$provider] ?? false;
                            $disabledReason = strtoupper($provider) . ' API key is not configured.';
                        ?>
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo (!$isEnabled ? 'disabled' : ''); ?>
                            title="<?php echo htmlspecialchars($isEnabled ? '' : $disabledReason, ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo (($value === $bulkSelectedChoice && $isEnabled) ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($choice['label'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php echo $isEnabled ? '' : ' (Unavailable)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Unavailable providers are disabled because their API keys are not configured.</div>
            </div>
            <div class="col-md-3">
                <label for="word-count" class="form-label">Word Count <span class="required-indicator">*</span></label>
                <input id="word-count" type="number" class="form-control" min="3" max="20" value="10">
            </div>
            <div class="col-md-3">
                <label for="grid-size" class="form-label">Grid Size <span class="required-indicator">*</span></label>
                <input id="grid-size" type="text" class="form-control" value="16 x 12" placeholder="16 x 12">
            </div>
        </div>

        <div class="mt-3">
            <label for="theme-list" class="form-label">Themes (one per line)</label>
            <textarea id="theme-list" class="form-control" rows="8" placeholder="Dances of India&#10;Festivals of India&#10;Fruits"></textarea>
            <div class="form-text">Blank lines are ignored. You can paste 100+ themes.</div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button id="generate-btn" class="btn btn-primary"><i class="fas fa-bolt me-2"></i>Generate Puzzles</button>
            <span id="print-btn-wrap" class="d-inline-block" title="Generate all puzzles first. Print is enabled after generation completes.">
                <button id="print-btn" class="btn btn-outline-secondary" disabled aria-disabled="true"><i class="fas fa-print me-2"></i>Print / Save PDF</button>
            </span>
        </div>

        <div id="progress" class="mt-3 small text-muted"></div>
    </div>

    <div id="results" class="panel"></div>
</div>

<script src="js/bulk_puzzles.js"></script>
</body>
</html>
