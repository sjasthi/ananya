<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play with Telugu - Ananya Telugu Playground</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --telugu-primary: #2e7d32;
            --telugu-secondary: #4caf50;
            --telugu-accent: #ff6b35;
            --telugu-light: #e8f5e8;
            --telugu-dark: #1b5e20;
            --telugu-text: #2c2c2c;
            --telugu-border: #c8e6c9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
            color: var(--telugu-text);
            min-height: 100vh;
        }

        .telugu-header {
            background: linear-gradient(135deg, var(--telugu-primary) 0%, var(--telugu-secondary) 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(46, 125, 50, 0.25);
        }

        .telugu-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .telugu-subtitle {
            font-size: 1.1rem;
            text-align: center;
            opacity: 0.95;
            margin-bottom: 0;
        }

        .playground-card {
            background: white;
            border-radius: 20px;
            border: 2px solid var(--telugu-border);
            box-shadow: 0 8px 32px rgba(46, 125, 50, 0.12);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .playground-card-header {
            background: linear-gradient(90deg, var(--telugu-primary), var(--telugu-accent));
            color: white;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .playground-card-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--telugu-dark);
        }

        .form-control,
        .form-select {
            border: 2px solid var(--telugu-border);
            border-radius: 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--telugu-secondary);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.18);
        }

        .action-btn {
            background: linear-gradient(135deg, var(--telugu-primary), var(--telugu-secondary));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.7rem 1.25rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.25);
        }

        .action-btn:hover {
            color: white;
            transform: translateY(-1px);
        }

        .endpoint-chip {
            font-family: Consolas, 'Courier New', monospace;
            background: #f4f8f4;
            border: 1px solid var(--telugu-border);
            border-radius: 10px;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            color: #2f5f35;
        }

        .helper-note {
            font-size: 0.9rem;
            color: #546e54;
        }

        .result-panel {
            background: #ffffff;
            border: 2px solid var(--telugu-border);
            border-radius: 14px;
            padding: 1rem;
            min-height: 220px;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: Consolas, 'Courier New', monospace;
            margin-bottom: 2.5rem;
        }

        .result-success {
            border-color: #4caf50;
            background: #f8fff8;
        }

        .result-error {
            border-color: #dc3545;
            background: #fff7f7;
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
            <h1 class="telugu-title"><i class="fas fa-gamepad"></i> Telugu Playground</h1>
            <p class="telugu-subtitle">Clean API explorer aligned with docs/api.php categories and operations.</p>
        </div>
    </div>

    <div class="container">
        <div class="playground-card">
            <div class="playground-card-header">
                <i class="fas fa-filter me-2"></i>Select Category and Operation
            </div>
            <div class="playground-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="languageSelect" class="form-label">Language</label>
                        <select id="languageSelect" class="form-select">
                            <option value="telugu" selected>telugu</option>
                            <option value="english">english</option>
                            <option value="hindi">hindi</option>
                            <option value="gujarati">gujarati</option>
                            <option value="malayalam">malayalam</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="categorySelect" class="form-label">Category</label>
                        <select id="categorySelect" class="form-select"></select>
                    </div>
                    <div class="col-md-4">
                        <label for="operationSelect" class="form-label">Operation</label>
                        <select id="operationSelect" class="form-select"></select>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <div id="endpointChip" class="endpoint-chip">Endpoint: -</div>
                        <div id="operationNote" class="helper-note mt-2"></div>
                    </div>
                </div>

                <div id="paramsContainer" class="row g-3 mt-2"></div>

                <div class="d-flex gap-2 mt-3">
                    <button id="runOperationBtn" class="btn action-btn">
                        <i class="fas fa-play me-1"></i>Run Operation
                    </button>
                    <button id="clearBtn" class="btn btn-outline-secondary">
                        <i class="fas fa-eraser me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>

        <div class="playground-card">
            <div class="playground-card-header">
                <i class="fas fa-terminal me-2"></i>Result
            </div>
            <div class="playground-card-body">
                <div id="resultPanel" class="result-panel">Select an operation, enter parameters, and click Run Operation.</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = 'api.php';

        const OPERATION_CATALOG = {
            character_analysis: {
                label: 'Character Analysis',
                operations: [
                    { key: 'add-at', label: 'Add Character at Position', endpoint: 'characters/add-at', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Position', required: true, type: 'number' }, { name: 'input3', label: 'Character to Insert', required: true }], note: 'Adds a character at a specific logical position.' },
                    { key: 'add-end', label: 'Add Character at End', endpoint: 'characters/add-end', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Character to Append', required: true }] },
                    { key: 'base', label: 'Base Characters', endpoint: 'characters/base', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'base-consonants', label: 'Base Consonants', endpoint: 'characters/base-consonants', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Filter/Argument', required: false }] },
                    { key: 'codepoint-length', label: 'Code Point Length', endpoint: 'characters/codepoint-length', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'codepoints', label: 'Code Points', endpoint: 'characters/codepoints', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'filler', label: 'Filler Characters', endpoint: 'characters/filler', params: [{ name: 'count', label: 'Count', required: false, type: 'number', defaultValue: '3' }, { name: 'type', label: 'Type', required: false, control: 'select', options: ['consonant', 'vowel'], defaultValue: 'consonant' }] },
                    { key: 'logical', label: 'Logical Characters', endpoint: 'characters/logical', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'logical-at', label: 'Logical Character At Position', endpoint: 'characters/logical-at', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Position', required: true, type: 'number', defaultValue: '0' }] },
                    { key: 'random-logical', label: 'Random Logical Characters', endpoint: 'characters/random-logical', params: [{ name: 'count', label: 'Count', required: false, type: 'number', defaultValue: '5' }] }
                ]
            },
            character_validation: {
                label: 'Character Validation',
                operations: [
                    { key: 'contains-all-logical-chars', label: 'Contains All Logical Characters', endpoint: 'validation/contains-all-logical-chars', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Logical Chars (comma-separated)', required: true }] },
                    { key: 'contains-char', label: 'Contains Character', endpoint: 'validation/contains-char', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Character', required: true }] },
                    { key: 'contains-logical-chars', label: 'Contains Logical Characters', endpoint: 'validation/contains-logical-chars', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Logical Chars (comma-separated)', required: true }] },
                    { key: 'contains-logical-sequence', label: 'Contains Logical Sequence', endpoint: 'validation/contains-logical-sequence', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Sequence', required: true }] },
                    { key: 'contains-space', label: 'Contains Space', endpoint: 'validation/contains-space', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'contains-string', label: 'Contains String', endpoint: 'validation/contains-string', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Substring', required: true }] },
                    { key: 'ends-with', label: 'Ends With', endpoint: 'validation/ends-with', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Suffix', required: true }] },
                    { key: 'is-vowel', label: 'Is Vowel', endpoint: 'validation/is-vowel', params: [{ name: 'string', label: 'Character/Text', required: true }] },
                    { key: 'starts-with', label: 'Starts With', endpoint: 'validation/starts-with', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Prefix', required: true }] }
                ]
            },
            string_comparison: {
                label: 'String Comparison',
                operations: [
                    { key: 'compare-ignore-case', label: 'Compare To (Ignore Case)', endpoint: 'comparison/compare-ignore-case', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'compare-to', label: 'Compare To', endpoint: 'comparison/compare-to', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'equals', label: 'Equals', endpoint: 'comparison/equals', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'is-intersecting', label: 'Is Intersecting', endpoint: 'comparison/is-intersecting', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'reverse-equals', label: 'Reverse Equals', endpoint: 'comparison/reverse-equals', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] }
                ]
            },
            text_operations: {
                label: 'Text Operations',
                operations: [
                    { key: 'length', label: 'Text Length', endpoint: 'text/length', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'randomize', label: 'Text Randomize', endpoint: 'text/randomize', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'replace', label: 'Text Replace', endpoint: 'text/replace', params: [{ name: 'string', label: 'Text', required: true }, { name: 'search', label: 'Search', required: true }, { name: 'replace', label: 'Replace With', required: false }] },
                    { key: 'reverse', label: 'Text Reverse', endpoint: 'text/reverse', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'split', label: 'Text Split', endpoint: 'text/split', params: [{ name: 'string', label: 'Text', required: true }, { name: 'delimiter', label: 'Delimiter', required: false, defaultValue: '-' }] }
                ]
            },
            utility: {
                label: 'Utility',
                operations: [
                    { key: 'index-of', label: 'Index Of', endpoint: 'utility/index-of', params: [{ name: 'string', label: 'Text', required: true }, { name: 'input2', label: 'Find', required: true }] },
                    { key: 'language', label: 'Language', endpoint: 'utility/language', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'length-alternative', label: 'Length Alternative', endpoint: 'utility/length-alternative', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'length-no-spaces', label: 'Length No Spaces', endpoint: 'utility/length-no-spaces', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'length-no-spaces-commas', label: 'Length No Spaces/Commas', endpoint: 'utility/length-no-spaces-commas', params: [{ name: 'string', label: 'Text', required: true }] }
                ]
            },
            word_analysis: {
                label: 'Word Analysis',
                operations: [
                    { key: 'can-make-all-words', label: 'Can Make All Words', endpoint: 'analysis/can-make-all-words', params: [{ name: 'string', label: 'Source', required: true }, { name: 'input2', label: 'Targets', required: true }] },
                    { key: 'can-make-word', label: 'Can Make Word', endpoint: 'analysis/can-make-word', params: [{ name: 'string', label: 'Source', required: true }, { name: 'input2', label: 'Target', required: true }] },
                    { key: 'detect-language', label: 'Detect Language', endpoint: 'analysis/detect-language', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'get-match-id-string', label: 'Get Match ID String', endpoint: 'analysis/get-match-id-string', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'head-tail-words', label: 'Head Tail Words', endpoint: 'analysis/head-tail-words', params: [{ name: 'string', label: 'Word 1', required: true }, { name: 'input2', label: 'Word 2', required: true }] },
                    { key: 'intersecting-rank', label: 'Intersecting Rank', endpoint: 'analysis/intersecting-rank', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'is-anagram', label: 'Is Anagram', endpoint: 'analysis/is-anagram', params: [{ name: 'string', label: 'Word 1', required: true }, { name: 'input2', label: 'Word 2', required: true }] },
                    { key: 'is-consonant', label: 'Is Consonant', endpoint: 'analysis/is-consonant', params: [{ name: 'string', label: 'Character/Text', required: true }] },
                    { key: 'is-palindrome', label: 'Is Palindrome', endpoint: 'analysis/is-palindrome', params: [{ name: 'string', label: 'Word/Text', required: true }] },
                    { key: 'ladder-words', label: 'Ladder Words', endpoint: 'analysis/ladder-words', params: [{ name: 'string', label: 'Word 1', required: true }, { name: 'input2', label: 'Word 2', required: true }] },
                    { key: 'parse-to-logical-characters', label: 'Parse to Logical Characters', endpoint: 'analysis/parse-to-logical-characters', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'role', label: 'Character Role', endpoint: 'analysis/role', params: [{ name: 'string', label: 'Character/Text', required: true }] },
                    { key: 'split-into-chunks', label: 'Split into Chunks', endpoint: 'analysis/split-into-chunks', params: [{ name: 'string', label: 'Text', required: true }] },
                    { key: 'unique-intersecting-chars', label: 'Unique Intersecting Characters', endpoint: 'analysis/unique-intersecting-chars', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'unique-intersecting-rank', label: 'Unique Intersecting Rank', endpoint: 'analysis/unique-intersecting-rank', params: [{ name: 'string', label: 'String 1', required: true }, { name: 'input2', label: 'String 2', required: true }] },
                    { key: 'word-level', label: 'Word Level', endpoint: 'analysis/word-level', params: [{ name: 'string', label: 'Word', required: true }] },
                    { key: 'word-strength', label: 'Word Strength', endpoint: 'analysis/word-strength', params: [{ name: 'string', label: 'Word', required: true }] },
                    { key: 'word-weight', label: 'Word Weight', endpoint: 'analysis/word-weight', params: [{ name: 'string', label: 'Word', required: true }] }
                ]
            }
        };

        const categorySelect = document.getElementById('categorySelect');
        const operationSelect = document.getElementById('operationSelect');
        const languageSelect = document.getElementById('languageSelect');
        const paramsContainer = document.getElementById('paramsContainer');
        const endpointChip = document.getElementById('endpointChip');
        const operationNote = document.getElementById('operationNote');
        const runOperationBtn = document.getElementById('runOperationBtn');
        const clearBtn = document.getElementById('clearBtn');
        const resultPanel = document.getElementById('resultPanel');

        function getCurrentCategory() {
            return OPERATION_CATALOG[categorySelect.value];
        }

        function getCurrentOperation() {
            const category = getCurrentCategory();
            if (!category) {
                return null;
            }
            return category.operations.find(op => op.key === operationSelect.value) || null;
        }

        function populateCategories() {
            categorySelect.innerHTML = '';
            for (const [key, category] of Object.entries(OPERATION_CATALOG)) {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = category.label;
                categorySelect.appendChild(option);
            }
        }

        function populateOperations() {
            const category = getCurrentCategory();
            operationSelect.innerHTML = '';

            if (!category) {
                return;
            }

            category.operations.forEach(operation => {
                const option = document.createElement('option');
                option.value = operation.key;
                option.textContent = operation.label;
                operationSelect.appendChild(option);
            });

            renderOperationForm();
        }

        function buildField(param) {
            const col = document.createElement('div');
            col.className = 'col-md-6';

            const label = document.createElement('label');
            label.className = 'form-label';
            label.setAttribute('for', `param-${param.name}`);
            label.textContent = param.required ? `${param.label} *` : param.label;
            col.appendChild(label);

            let control;
            if (param.control === 'select') {
                control = document.createElement('select');
                control.className = 'form-select';
                (param.options || []).forEach(value => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = value;
                    control.appendChild(option);
                });
            } else {
                control = document.createElement('input');
                control.className = 'form-control';
                control.type = param.type || 'text';
                control.placeholder = param.placeholder || '';
            }

            control.id = `param-${param.name}`;
            control.dataset.paramName = param.name;
            control.dataset.required = param.required ? '1' : '0';
            if (param.defaultValue !== undefined) {
                control.value = String(param.defaultValue);
            }

            col.appendChild(control);
            return col;
        }

        function renderOperationForm() {
            const operation = getCurrentOperation();
            paramsContainer.innerHTML = '';
            runOperationBtn.title = '';
            runOperationBtn.disabled = false;

            if (!operation) {
                endpointChip.textContent = 'Endpoint: -';
                operationNote.textContent = '';
                return;
            }

            endpointChip.textContent = `Endpoint: ${operation.endpoint}`;
            operationNote.textContent = operation.note || '';

            operation.params.forEach(param => {
                paramsContainer.appendChild(buildField(param));
            });
        }

        function collectParams(operation) {
            const query = new URLSearchParams();
            const controls = paramsContainer.querySelectorAll('[data-param-name]');

            controls.forEach(control => {
                const key = control.dataset.paramName;
                const value = control.value.trim();
                const required = control.dataset.required === '1';

                if (required && !value) {
                    throw new Error(`Missing required parameter: ${key}`);
                }

                if (value !== '') {
                    query.set(key, value);
                }
            });

            query.set('language', languageSelect.value);
            return query;
        }

        function stringifyResult(data) {
            if (Array.isArray(data)) {
                return data.join(', ');
            }
            if (data && typeof data === 'object') {
                return JSON.stringify(data, null, 2);
            }
            if (data === null || data === undefined) {
                return '';
            }
            return String(data);
        }

        function getApiBaseUrl() {
            const basePath = window.location.pathname.replace(/\/[^/]*$/, '/');
            return `${window.location.origin}${basePath}${API_BASE}`;
        }

        async function runSelectedOperation() {
            const operation = getCurrentOperation();
            if (!operation) {
                return;
            }

            runOperationBtn.disabled = true;
            runOperationBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Running...';
            resultPanel.classList.remove('result-success', 'result-error');
            resultPanel.textContent = 'Executing request...';

            try {
                const query = collectParams(operation);
                query.set('_t', String(Date.now()));

                const url = `${getApiBaseUrl()}/${operation.endpoint}?${query.toString()}`;
                const response = await fetch(url, {
                    method: 'GET',
                    cache: 'no-cache',
                    headers: { 'Cache-Control': 'no-cache' }
                });

                const payload = await response.json();
                const statusLine = `HTTP ${response.status} ${response.ok ? 'OK' : 'ERROR'}`;
                const messageLine = payload.message ? `Message: ${payload.message}` : '';
                const resultData = payload.data !== undefined ? payload.data : payload.result;
                const resultLine = `Result:\n${stringifyResult(resultData)}`;

                resultPanel.textContent = [statusLine, messageLine, '', resultLine].filter(Boolean).join('\n');

                if (response.ok && payload.success !== false) {
                    resultPanel.classList.add('result-success');
                } else {
                    resultPanel.classList.add('result-error');
                }
            } catch (error) {
                resultPanel.classList.add('result-error');
                resultPanel.textContent = `Error: ${error.message}`;
            } finally {
                runOperationBtn.disabled = false;
                runOperationBtn.innerHTML = '<i class="fas fa-play me-1"></i>Run Operation';
            }
        }

        function clearForm() {
            renderOperationForm();
            resultPanel.classList.remove('result-success', 'result-error');
            resultPanel.textContent = 'Select an operation, enter parameters, and click Run Operation.';
        }

        categorySelect.addEventListener('change', populateOperations);
        operationSelect.addEventListener('change', renderOperationForm);
        languageSelect.addEventListener('change', populateOperations);
        runOperationBtn.addEventListener('click', runSelectedOperation);
        clearBtn.addEventListener('click', clearForm);

        document.addEventListener('DOMContentLoaded', () => {
            populateCategories();
            populateOperations();
        });
    </script>
</body>
</html>