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

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .panel {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .puzzle-card {
                page-break-inside: avoid;
                break-inside: avoid;
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
                </select>
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
            <button id="print-btn" class="btn btn-outline-secondary"><i class="fas fa-print me-2"></i>Print / Save PDF</button>
        </div>

        <div id="progress" class="mt-3 small text-muted"></div>
    </div>

    <div id="results" class="panel"></div>
</div>

<script src="js/bulk_puzzles.js"></script>
</body>
</html>
