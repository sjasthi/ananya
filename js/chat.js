// Chat frontend: sends user questions to chat_api.php (which proxies to MCP server)
// Supports Markdown rendering for assistant responses via marked.js + DOMPurify
document.addEventListener('DOMContentLoaded', function () {
    const sendBtn = document.getElementById('chat-send');
    const input = document.getElementById('chat-input');
    const windowEl = document.getElementById('chat-window');
    const langSelect = document.getElementById('language-select');
    const languageFeedback = document.getElementById('language-feedback');
    const llmSelect = document.getElementById('llm-select');

    function maybeExpandChatWindow() {
        if (!windowEl) return;

        const minHeight = 320;
        const maxHeight = Math.floor(window.innerHeight * 0.78);
        const desired = Math.min(maxHeight, Math.max(minHeight, windowEl.scrollHeight + 16));
        windowEl.style.height = desired + 'px';
    }

    function hasOutputLanguage() {
        return !!(langSelect && String(langSelect.value || '').trim() !== '');
    }

    function setLanguageValidationState(showInvalid) {
        if (!langSelect) return;
        langSelect.classList.toggle('is-invalid', !!showInvalid);
        if (languageFeedback) {
            languageFeedback.style.display = showInvalid ? 'block' : '';
        }
    }

    function updateSendAvailability() {
        if (!sendBtn) return;
        sendBtn.disabled = !(input && input.value.trim() !== '');
    }

    function setSelectWidth(selectEl) {
        if (!selectEl) return;
        const options = Array.from(selectEl.options || []);
        if (!options.length) return;

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const style = window.getComputedStyle(selectEl);
        ctx.font = `${style.fontStyle} ${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;

        let maxWidth = 0;
        options.forEach(opt => {
            const w = ctx.measureText(opt.text).width;
            if (w > maxWidth) maxWidth = w;
        });

        // 1.5x longest option + padding for arrow and inner spacing
        const padding = 48;
        const targetWidth = Math.ceil(maxWidth * 1.5 + padding);
        selectEl.style.width = `${targetWidth}px`;
    }

    // Configure marked for safe rendering
    if (typeof marked !== 'undefined') {
        marked.setOptions({ breaks: true, gfm: true });
    }

    function sanitizeHTML(html) {
        if (typeof DOMPurify !== 'undefined') {
            return DOMPurify.sanitize(html);
        }
        // Basic fallback: strip script tags
        return html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
    }

    function renderMarkdown(text) {
        if (typeof marked !== 'undefined') {
            return sanitizeHTML(marked.parse(text));
        }
        // Fallback: escape HTML and convert newlines
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function looksLikePuzzleResponse(text) {
        if (!text) return false;
        const t = String(text);
        const tl = t.toLowerCase();

        // English puzzle detection
        const isEnglishPuzzle =
            (tl.includes('word find puzzle') || tl.includes('crossword puzzle')) &&
            tl.includes('answer key:');

        // Telugu puzzle detection:
        // - Common heading examples: "పద శోధన పజిల్" (word search puzzle)
        // - Answer key headings: "జవాబు సూచిక:" / variants without colon or with synonyms
        const hasTeluguPuzzleHeading =
            t.includes('పద శోధన పజిల్') ||
            t.includes('పదశోధన పజిల్') ||
            t.includes('పద శోధన');

        const hasTeluguAnswerKeyHeading =
            t.includes('జవాబు సూచిక:') ||
            t.includes('జవాబు సూచిక') ||
            t.includes('సమాధాన సూచిక');

        const isTeluguPuzzle = hasTeluguPuzzleHeading && hasTeluguAnswerKeyHeading;

        return isEnglishPuzzle || isTeluguPuzzle;
    }

    function looksLikeCrosswordResponse(text) {
        if (!text) return false;
        const tl = String(text).toLowerCase();
        return tl.includes('crossword puzzle')
            && tl.includes('across:')
            && tl.includes('down:')
            && tl.includes('answer key:')
            && tl.includes('solution grid:');
    }

    function looksLikeWordFindResponse(text) {
        if (!text) return false;
        const t = String(text);
        const tl = t.toLowerCase();

        const isEnglish = tl.includes('word find puzzle')
            && tl.includes('find these words:')
            && tl.includes('answer key:');

        const isTelugu = (t.includes('పద శోధన పజిల్') || t.includes('పదశోధన పజిల్'))
            && t.includes('ఈ పదాలను కనుగొనండి')
            && (t.includes('జవాబు సూచిక') || t.includes('సమాధాన సూచిక'));

        return isEnglish || isTelugu;
    }

    function containsTeluguScript(text) {
        return /[\u0C00-\u0C7F]/.test(String(text || ''));
    }

    function parseClueLine(line) {
        const m = String(line || '').trim().match(/^-\s*(\d+)\.\s*(.+)$/);
        if (!m) return null;
        return {
            number: parseInt(m[1], 10),
            text: m[2].trim()
        };
    }

    function parseAnswerKeyLine(line) {
        const m = String(line || '').trim().match(/^-\s*(\d+)\s+(Across|Down):\s*(.*?)\s+at\s*\((\d+)\s*,\s*(\d+)\)\s*$/i);
        if (!m) return null;
        return {
            number: parseInt(m[1], 10),
            direction: m[2].toLowerCase(),
            word: m[3],
            row: parseInt(m[4], 10),
            col: parseInt(m[5], 10)
        };
    }

    function tokenizeGridLine(line) {
        const tokens = [];
        let i = 0;
        const str = String(line || '');

        while (i < str.length) {
            if (str[i] === '[') {
                const close = str.indexOf(']', i + 1);
                if (close === -1) {
                    break;
                }
                tokens.push(str.slice(i, close + 1));
                i = close + 1;
                if (str[i] === ' ') i += 1;
                continue;
            }

            if (str.slice(i, i + 3) === '   ') {
                tokens.push('   ');
                i += 3;
                if (str[i] === ' ') i += 1;
                continue;
            }

            i += 1;
        }

        return tokens;
    }

    function parseSolutionGrid(lines) {
        const rows = [];

        lines.forEach(line => {
            const tokens = tokenizeGridLine(line);
            if (!tokens.length) {
                return;
            }

            const row = tokens.map(token => {
                if (token === '   ') {
                    return { blocked: true, letter: '' };
                }

                return {
                    blocked: false,
                    letter: token.slice(1, -1).trim()
                };
            });

            rows.push(row);
        });

        if (!rows.length) {
            return { rows: [], cols: 0 };
        }

        const cols = rows.reduce((max, row) => Math.max(max, row.length), 0);
        rows.forEach(row => {
            while (row.length < cols) {
                row.push({ blocked: true, letter: '' });
            }
        });

        return { rows, cols };
    }

    function parseCrosswordResponse(text) {
        const lines = String(text || '').replace(/\r/g, '').split('\n');
        const normalized = lines.map(line => line.trim().toLowerCase());

        const idxAcross = normalized.indexOf('across:');
        const idxDown = normalized.indexOf('down:');
        const idxAnswerKey = normalized.indexOf('answer key:');
        const idxSolution = normalized.indexOf('solution grid:');

        if (idxAcross === -1 || idxDown === -1 || idxAnswerKey === -1 || idxSolution === -1) {
            return null;
        }

        const themeLine = lines.find(line => line.trim().toLowerCase().startsWith('theme:')) || '';
        const wordsLine = lines.find(line => line.trim().toLowerCase().startsWith('words:')) || '';
        const gridLine = lines.find(line => line.trim().toLowerCase().startsWith('grid:')) || '';

        const acrossClues = lines
            .slice(idxAcross + 1, idxDown)
            .map(parseClueLine)
            .filter(Boolean)
            .sort((a, b) => a.number - b.number);

        const downClues = lines
            .slice(idxDown + 1, idxAnswerKey)
            .map(parseClueLine)
            .filter(Boolean)
            .sort((a, b) => a.number - b.number);

        const answerKeyEntries = lines
            .slice(idxAnswerKey + 1, idxSolution)
            .map(parseAnswerKeyLine)
            .filter(Boolean)
            .sort((a, b) => a.number - b.number);

        const solutionTail = lines.slice(idxSolution + 1);
        let stopIndex = solutionTail.findIndex(line => {
            const v = line.trim().toLowerCase();
            return v.startsWith('generation mode:') || v.startsWith('llm consulted');
        });

        if (stopIndex === -1) {
            stopIndex = solutionTail.length;
        }

        const solutionGridLines = solutionTail.slice(0, stopIndex).filter(line => line.trim() !== '');
        const solutionGrid = parseSolutionGrid(solutionGridLines);

        if (!solutionGrid.rows.length) {
            return null;
        }

        return {
            title: 'Crossword Puzzle',
            themeLine,
            wordsLine,
            gridLine,
            acrossClues,
            downClues,
            answerKeyEntries,
            solutionGrid
        };
    }

    function parseWordFindResponse(text) {
        const lines = String(text || '').replace(/\r/g, '').split('\n');
        const normalized = lines.map(line => line.trim().toLowerCase());

        const idxFind = normalized.findIndex(v =>
            v === 'find these words:' ||
            v === 'ఈ పదాలను కనుగొనండి:' ||
            v === 'ఈ పదాలను కనుగొనండి'
        );

        const idxAnswerKey = normalized.findIndex(v =>
            v === 'answer key:' ||
            v === 'జవాబు సూచిక:' ||
            v === 'జవాబు సూచిక' ||
            v === 'సమాధాన సూచిక:' ||
            v === 'సమాధాన సూచిక'
        );

        const idxGridMeta = normalized.findIndex(v => v.startsWith('grid:') || v.startsWith('గ్రిడ్:'));

        if (idxFind === -1 || idxAnswerKey === -1 || idxGridMeta === -1 || idxGridMeta >= idxFind) {
            return null;
        }

        const titleLine = lines.find(line => {
            const t = line.trim().toLowerCase();
            return t === 'word find puzzle' || line.includes('పద శోధన పజిల్') || line.includes('పదశోధన పజిల్');
        }) || 'Word Find Puzzle';

        const themeLine = lines.find(line => {
            const t = line.trim().toLowerCase();
            return t.startsWith('theme:') || line.trim().startsWith('థీమ్:');
        }) || '';

        const wordsLine = lines.find(line => {
            const t = line.trim().toLowerCase();
            return t.startsWith('words:') || line.trim().startsWith('పదాలు:');
        }) || '';

        const gridLine = lines[idxGridMeta] || '';

        const gridRows = lines
            .slice(idxGridMeta + 1, idxFind)
            .map(line => line.trim())
            .filter(line => line !== '')
            .map(line => line.split(/\s+/u).filter(Boolean));

        if (!gridRows.length) {
            return null;
        }

        const cols = gridRows.reduce((max, row) => Math.max(max, row.length), 0);
        gridRows.forEach(row => {
            while (row.length < cols) {
                row.push('');
            }
        });

        const words = lines
            .slice(idxFind + 1, idxAnswerKey)
            .map(line => line.trim())
            .filter(line => /^-\s*/.test(line))
            .map(line => line.replace(/^-\s*/, '').trim());

        const answerTail = lines.slice(idxAnswerKey + 1);
        let answerStop = answerTail.findIndex(line => {
            const v = line.trim().toLowerCase();
            return v.startsWith('generation mode:') || v.startsWith('llm consulted');
        });
        if (answerStop === -1) {
            answerStop = answerTail.length;
        }

        const answerKeyEntries = answerTail
            .slice(0, answerStop)
            .map(line => line.trim())
            .filter(line => /^-\s*/.test(line))
            .map(line => line.replace(/^-\s*/, '').trim());

        return {
            title: titleLine,
            themeLine,
            wordsLine,
            gridLine,
            gridRows,
            words,
            answerKeyEntries
        };
    }

    function renderClueItems(clues) {
        if (!clues.length) {
            return '<li>No clues available.</li>';
        }

        return clues
            .map(clue => `<li><span class="clue-num">${clue.number}.</span> ${escapeHtml(clue.text)}</li>`)
            .join('');
    }

    function renderAnswerKeyItems(entries) {
        if (!entries.length) {
            return '<li>No answer key entries available.</li>';
        }

        return entries
            .map(entry => {
                const direction = entry.direction === 'across' ? 'Across' : 'Down';
                return `<li>${entry.number} ${direction}: ${escapeHtml(entry.word)} at (${entry.row}, ${entry.col})</li>`;
            })
            .join('');
    }

    function buildCrosswordMarkup(parsed, includeToolbar, includeAnswerKey) {
        const startMap = {};
        parsed.answerKeyEntries.forEach(entry => {
            startMap[`${entry.row}:${entry.col}`] = entry.number;
        });

        let gridHtml = '<table class="crossword-grid" aria-label="Crossword grid"><tbody>';
        parsed.solutionGrid.rows.forEach((row, rIdx) => {
            gridHtml += '<tr>';
            row.forEach((cell, cIdx) => {
                if (cell.blocked) {
                    gridHtml += '<td class="blocked" aria-hidden="true"></td>';
                } else {
                    const key = `${rIdx + 1}:${cIdx + 1}`;
                    const clueNum = startMap[key] || '';
                    gridHtml += '<td class="open">';
                    if (clueNum) {
                        gridHtml += `<span class="cell-number">${clueNum}</span>`;
                    }
                    gridHtml += '</td>';
                }
            });
            gridHtml += '</tr>';
        });
        gridHtml += '</tbody></table>';

        return `
            <div class="crossword-render">
                <div class="crossword-header">
                    <h5>${escapeHtml(parsed.title)}</h5>
                    <div class="crossword-meta">
                        ${parsed.themeLine ? `<div>${escapeHtml(parsed.themeLine)}</div>` : ''}
                        ${parsed.wordsLine ? `<div>${escapeHtml(parsed.wordsLine)}</div>` : ''}
                        ${parsed.gridLine ? `<div>${escapeHtml(parsed.gridLine)}</div>` : ''}
                    </div>
                </div>
                ${includeToolbar ? '<div class="crossword-toolbar"><button type="button" class="btn btn-sm btn-outline-primary crossword-print-btn"><i class="fas fa-print me-1"></i>Print / Save PDF</button></div>' : ''}
                <div class="crossword-grid-wrap">${gridHtml}</div>
                <div class="crossword-clues row g-3">
                    <div class="col-md-6">
                        <h6>Across</h6>
                        <ul class="clue-list">${renderClueItems(parsed.acrossClues)}</ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Down</h6>
                        <ul class="clue-list">${renderClueItems(parsed.downClues)}</ul>
                    </div>
                </div>
                ${includeAnswerKey ? `<details class="crossword-answer-key"><summary>Show answer key</summary><ul>${renderAnswerKeyItems(parsed.answerKeyEntries)}</ul></details>` : ''}
            </div>
        `;
    }

    function buildWordFindMarkup(parsed, includeToolbar, includeAnswerKey) {
        let gridHtml = '<table class="wordfind-grid" aria-label="Word find grid"><tbody>';
        parsed.gridRows.forEach(row => {
            gridHtml += '<tr>';
            row.forEach(cell => {
                gridHtml += `<td>${escapeHtml(cell || '')}</td>`;
            });
            gridHtml += '</tr>';
        });
        gridHtml += '</tbody></table>';

        const wordsMarkup = parsed.words.length
            ? parsed.words.map(word => `<li>${escapeHtml(word)}</li>`).join('')
            : '<li>No word list available.</li>';

        const answerKeyMarkup = parsed.answerKeyEntries.length
            ? parsed.answerKeyEntries.map(item => `<li>${escapeHtml(item)}</li>`).join('')
            : '<li>No answer key entries available.</li>';

        return `
            <div class="wordfind-render">
                <div class="wordfind-header">
                    <h5>${escapeHtml(parsed.title)}</h5>
                    <div class="wordfind-meta">
                        ${parsed.themeLine ? `<div>${escapeHtml(parsed.themeLine)}</div>` : ''}
                        ${parsed.wordsLine ? `<div>${escapeHtml(parsed.wordsLine)}</div>` : ''}
                        ${parsed.gridLine ? `<div>${escapeHtml(parsed.gridLine)}</div>` : ''}
                    </div>
                </div>
                ${includeToolbar ? '<div class="wordfind-toolbar"><button type="button" class="btn btn-sm btn-outline-primary wordfind-print-btn"><i class="fas fa-print me-1"></i>Print / Save PDF</button></div>' : ''}
                <div class="wordfind-grid-wrap">${gridHtml}</div>
                <div class="wordfind-words">
                    <h6>Find these words</h6>
                    <ul>${wordsMarkup}</ul>
                </div>
                ${includeAnswerKey ? `<details class="wordfind-answer-key"><summary>Show answer key</summary><ul>${answerKeyMarkup}</ul></details>` : ''}
            </div>
        `;
    }

    function printDocumentFromMarkup(title, content, style) {
        const frame = document.createElement('iframe');
        frame.style.position = 'fixed';
        frame.style.right = '0';
        frame.style.bottom = '0';
        frame.style.width = '0';
        frame.style.height = '0';
        frame.style.border = '0';
        frame.setAttribute('aria-hidden', 'true');
        document.body.appendChild(frame);

        const doc = frame.contentWindow && frame.contentWindow.document;
        if (!doc) {
            frame.remove();
            return;
        }

        doc.open();
        doc.write(`<!doctype html><html><head><meta charset="utf-8"><title>${escapeHtml(title)}</title>${style}</head><body>${content}</body></html>`);
        doc.close();

        setTimeout(() => {
            if (frame.contentWindow) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            }
            setTimeout(() => frame.remove(), 2000);
        }, 300);
    }

    function printCrossword(parsed) {
        const content = buildCrosswordMarkup(parsed, false, false);
        const style = `
            <style>
                body { font-family: Arial, sans-serif; margin: 24px; color: #111827; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                h5 { margin: 0 0 8px; font-size: 24px; }
                .crossword-meta { font-size: 13px; color: #4b5563; margin-bottom: 10px; }
                .crossword-grid { border-collapse: collapse; margin: 12px 0 16px; }
                .crossword-grid td { width: 28px; height: 28px; border: 1px solid #1f2937; position: relative; }
                .crossword-grid td.blocked { background: #111827; border-color: #111827; }
                .crossword-grid td.open { background: #fff; }
                .cell-number { position: absolute; top: 1px; left: 2px; font-size: 9px; font-weight: 700; color: #111827; }
                .crossword-clues { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 8px; }
                .crossword-clues h6 { margin: 0 0 6px; font-size: 14px; }
                .crossword-clues ul { list-style: none; margin: 0; padding-left: 0; font-size: 12px; line-height: 1.4; }
                .crossword-clues li { margin-bottom: 2px; }
                .clue-num { font-weight: 700; }
                .crossword-grid td.blocked::before { content: '\\25A0'; color: #111827; font-size: 18px; line-height: 26px; display: block; text-align: center; }
                @media print {
                    body { margin: 12mm; }
                }
            </style>
        `;

        printDocumentFromMarkup('Crossword Puzzle', content, style);
    }

    function printWordFind(parsed) {
        const content = buildWordFindMarkup(parsed, false, false);
        const style = `
            <style>
                body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
                h5 { margin: 0 0 8px; font-size: 24px; }
                .wordfind-meta { font-size: 13px; color: #4b5563; margin-bottom: 10px; }
                .wordfind-grid { border-collapse: collapse; margin: 10px 0 14px; }
                .wordfind-grid td { width: 30px; height: 30px; border: 1px solid #1f2937; text-align: center; font-weight: 700; font-size: 14px; }
                .wordfind-words h6 { margin: 6px 0; font-size: 14px; }
                .wordfind-words ul { margin: 0; padding-left: 18px; column-count: 2; font-size: 12px; }
                @media print {
                    body { margin: 12mm; }
                }
            </style>
        `;

        printDocumentFromMarkup('Word Find Puzzle', content, style);
    }

    function appendMessage(who, text, source, llmProvider, llmModel) {
        const row = document.createElement('div');
        row.className = 'mb-2 d-flex ' + (who === 'user' ? 'justify-content-end' : 'justify-content-start');

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + who;
        let isLargePuzzle = false;

        if (who === 'user') {
            bubble.textContent = text;
        } else {
            if (looksLikeCrosswordResponse(text)) {
                const parsed = parseCrosswordResponse(text);
                if (parsed) {
                    bubble.innerHTML = buildCrosswordMarkup(parsed, true, true);
                    isLargePuzzle = true;
                    const printBtn = bubble.querySelector('.crossword-print-btn');
                    if (printBtn) {
                        printBtn.addEventListener('click', function () {
                            printCrossword(parsed);
                        });
                    }
                } else {
                    const teluguClass = containsTeluguScript(text) ? ' puzzle-output-telugu' : '';
                    bubble.innerHTML = '<pre class="puzzle-output' + teluguClass + '">' + escapeHtml(text) + '</pre>';
                }
            } else if (looksLikeWordFindResponse(text)) {
                const parsedWordFind = parseWordFindResponse(text);
                if (parsedWordFind) {
                    bubble.innerHTML = buildWordFindMarkup(parsedWordFind, true, true);
                    isLargePuzzle = true;
                    const printBtn = bubble.querySelector('.wordfind-print-btn');
                    if (printBtn) {
                        printBtn.addEventListener('click', function () {
                            printWordFind(parsedWordFind);
                        });
                    }
                } else {
                    const teluguClass = containsTeluguScript(text) ? ' puzzle-output-telugu' : '';
                    bubble.innerHTML = '<pre class="puzzle-output' + teluguClass + '">' + escapeHtml(text) + '</pre>';
                }
            } else if (looksLikePuzzleResponse(text)) {
                const teluguClass = containsTeluguScript(text) ? ' puzzle-output-telugu' : '';
                bubble.innerHTML = '<pre class="puzzle-output' + teluguClass + '">' + escapeHtml(text) + '</pre>';
            } else {
                bubble.innerHTML = renderMarkdown(text);
            }
        }

        row.appendChild(bubble);

        // Add source badge for assistant messages
        if (who === 'assistant' && source) {
            const wrapper = document.createElement('div');
            wrapper.className = 'd-flex flex-column ' + (who === 'user' ? 'align-items-end' : 'align-items-start');
            wrapper.appendChild(bubble);

            const badge = document.createElement('span');
            const isMCP = source !== 'fallback';
            badge.className = 'source-badge ' + (isMCP ? 'mcp' : 'fallback');
            badge.textContent = isMCP ? '✓ MCP Tools' : '⚡ Direct LLM';
            wrapper.appendChild(badge);

            if (llmProvider || llmModel) {
                const meta = document.createElement('div');
                meta.className = 'llm-meta';
                const providerText = llmProvider ? String(llmProvider).toUpperCase() : 'UNKNOWN';
                const modelText = llmModel ? String(llmModel) : 'default';
                meta.textContent = `LLM: ${providerText} / ${modelText}`;
                wrapper.appendChild(meta);
            }

            row.innerHTML = '';
            row.appendChild(wrapper);
        }

        windowEl.appendChild(row);
        windowEl.scrollTop = windowEl.scrollHeight;

        if (isLargePuzzle) {
            requestAnimationFrame(maybeExpandChatWindow);
        }

        return row;
    }

    function appendTypingIndicator() {
        const row = document.createElement('div');
        row.className = 'mb-2 d-flex justify-content-start';
        row.id = 'typing-indicator';

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble assistant chat-typing';
        bubble.innerHTML = '<span></span><span></span><span></span>';

        row.appendChild(bubble);
        windowEl.appendChild(row);
        windowEl.scrollTop = windowEl.scrollHeight;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) indicator.remove();
    }

    async function sendQuestion() {
        const q = input.value.trim();
        if (!q) return;
        if (!hasOutputLanguage()) {
            setLanguageValidationState(true);
            if (langSelect) langSelect.focus();
            return;
        }

        setLanguageValidationState(false);

        appendMessage('user', q);
        input.value = '';
        input.focus();
        sendBtn.disabled = true;
        appendTypingIndicator();

        try {
            let llmProvider = '';
            let llmModel = '';
            if (llmSelect && llmSelect.value) {
                const parts = llmSelect.value.split(':');
                llmProvider = (parts[0] || '').trim().toLowerCase();
                llmModel = parts.slice(1).join(':').trim();
            }

            const res = await fetch('chat_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    question: q,
                    language: langSelect ? langSelect.value : 'english',
                    llm_provider: llmProvider,
                    llm_model: llmModel
                })
            });

            removeTypingIndicator();

            if (!res.ok) {
                appendMessage('assistant', `Server error (HTTP ${res.status}). Please try again.`);
                updateSendAvailability();
                return;
            }

            const raw = await res.text();
            let json = null;
            try {
                json = JSON.parse(raw);
            } catch (parseErr) {
                const snippet = (raw || '').slice(0, 220).replace(/\s+/g, ' ').trim();
                appendMessage(
                    'assistant',
                    '**Server returned non-JSON output.** Please check PHP warnings/errors.\n\n' +
                    (snippet ? ('`' + snippet + '`') : '`(empty response)`')
                );
                updateSendAvailability();
                return;
            }

            if (json.error) {
                appendMessage('assistant', '**Error:** ' + json.error);
            } else {
                const answer = json.answer || JSON.stringify(json, null, 2);
                const source = json.source || 'mcp';
                appendMessage('assistant', answer, source, json.llm_provider || '', json.llm_model || '');
            }
        } catch (err) {
            removeTypingIndicator();
            appendMessage('assistant', '**Connection failed.** Is the server running?\n\n`' + err.message + '`');
        }

        updateSendAvailability();
    }

    sendBtn.addEventListener('click', sendQuestion);
    if (langSelect) {
        langSelect.addEventListener('change', function () {
            setLanguageValidationState(!hasOutputLanguage());
        });
    }
    input.addEventListener('input', updateSendAvailability);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendQuestion();
        }
    });

    setSelectWidth(langSelect);
    setSelectWidth(llmSelect);
    updateSendAvailability();
    input.focus();
});
