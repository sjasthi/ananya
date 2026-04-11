document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('theme-file');
    const themeList = document.getElementById('theme-list');
    const outputLanguage = document.getElementById('output-language');
    const llmSelect = document.getElementById('bulk-llm-select');
    const countInput = document.getElementById('word-count');
    const gridInput = document.getElementById('grid-size');
    const generateBtn = document.getElementById('generate-btn');
    const printBtn = document.getElementById('print-btn');
    const printBtnWrap = document.getElementById('print-btn-wrap');
    const progressEl = document.getElementById('progress');
    const resultsEl = document.getElementById('results');

    let latestPuzzleRecords = [];

    function setPrintButtonState(enabled, reason) {
        if (!printBtn) {
            return;
        }

        const disabled = !enabled;
        printBtn.disabled = disabled;
        printBtn.setAttribute('aria-disabled', disabled ? 'true' : 'false');

        const tooltipText = reason || '';
        if (printBtnWrap) {
            printBtnWrap.setAttribute('title', tooltipText);
        }
        printBtn.setAttribute('title', tooltipText);
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function hasTelugu(text) {
        return /[\u0C00-\u0C7F]/.test(String(text || ''));
    }

    function parseClueLine(line) {
        const m = String(line || '').trim().match(/^\-\s*(\d+)\.\s*(.+)$/);
        if (!m) return null;
        return {
            number: parseInt(m[1], 10),
            text: m[2].trim()
        };
    }

    function parseAnswerKeyLine(line) {
        const m = String(line || '').trim().match(/^\-\s*(\d+)\s+(Across|Down):\s*(.*?)\s+at\s*\((\d+)\s*,\s*(\d+)\)\s*$/i);
        if (!m) return null;
        return {
            number: parseInt(m[1], 10),
            direction: m[2].toLowerCase(),
            word: m[3],
            row: parseInt(m[4], 10),
            col: parseInt(m[5], 10)
        };
    }

    function parseWordFindAnswerKeyLine(line) {
        const cleaned = String(line || '').trim().replace(/^\-\s*/, '');
        if (!cleaned) {
            return null;
        }

        const m = cleaned.match(/^(.*?):\s*\((\d+)\s*,\s*(\d+)\)\s*->\s*\((\d+)\s*,\s*(\d+)\)\s*([A-Za-z?]{1,3})\s*$/u);
        if (!m) {
            return {
                text: cleaned,
                startRow: null,
                startCol: null,
                endRow: null,
                endCol: null,
                dir: ''
            };
        }

        return {
            text: cleaned,
            word: m[1].trim(),
            startRow: parseInt(m[2], 10),
            startCol: parseInt(m[3], 10),
            endRow: parseInt(m[4], 10),
            endCol: parseInt(m[5], 10),
            dir: String(m[6] || '').toUpperCase()
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
            if (!tokens.length) return;

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
            .map(line => line.replace(/^\-\s*/, '').trim());

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
            .map(parseWordFindAnswerKeyLine)
            .filter(Boolean);

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

    function renderCrosswordAnswerKeyItems(entries) {
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

    function renderWordFindAnswerKeyItems(entries) {
        if (!entries.length) {
            return '<li>No answer key entries available.</li>';
        }

        return entries
            .map(entry => `<li>${escapeHtml(entry.text || '')}</li>`)
            .join('');
    }

    function buildFinalAnswerKeyMarkup(records) {
        const usable = (records || []).filter(record => {
            if (!record || !record.ok || !record.parsed) {
                return false;
            }

            const entries = Array.isArray(record.parsed.answerKeyEntries) ? record.parsed.answerKeyEntries : [];
            return entries.length > 0;
        });

        if (!usable.length) {
            return '';
        }

        const sections = usable.map(record => {
            const title = `#${record.index + 1} ${record.theme}`;
            const solvedMarkup = record.type === 'crossword'
                ? renderMiniCrosswordSolution(record.parsed)
                : renderMiniWordFindSolution(record.parsed);

            if (record.type === 'crossword') {
                return `
                    <section class="answer-key-page-item">
                        <h3>${escapeHtml(title)}</h3>
                        <div class="answer-key-layout">
                            <div class="answer-key-left">
                                <div class="answer-key-type">Crossword answer key</div>
                                <ul>${renderCrosswordAnswerKeyItems(record.parsed.answerKeyEntries || [])}</ul>
                            </div>
                            <div class="answer-key-right">
                                <div class="solved-mini-title">Solved</div>
                                ${solvedMarkup}
                            </div>
                        </div>
                    </section>
                `;
            }

            return `
                <section class="answer-key-page-item">
                    <h3>${escapeHtml(title)}</h3>
                    <div class="answer-key-layout">
                        <div class="answer-key-left">
                            <div class="answer-key-type">Word find answer key</div>
                            <ul>${renderWordFindAnswerKeyItems(record.parsed.answerKeyEntries || [])}</ul>
                        </div>
                        <div class="answer-key-right">
                            <div class="solved-mini-title">Solved</div>
                            ${solvedMarkup}
                        </div>
                    </div>
                </section>
            `;
        }).join('');

        return `
            <section class="print-answer-key-pages" aria-label="Final answer key pages">
                <h2>Final Answer Key (All Puzzles)</h2>
                ${sections}
            </section>
        `;
    }

    function renderFinalAnswerKeyPages(records) {
        if (!resultsEl) {
            return;
        }

        const existing = resultsEl.querySelector('.print-answer-key-pages');
        if (existing) {
            existing.remove();
        }

        const markup = buildFinalAnswerKeyMarkup(records);
        if (markup) {
            resultsEl.insertAdjacentHTML('beforeend', markup);
        }
    }

    function renderMiniCrosswordSolution(parsed) {
        const grid = parsed && parsed.solutionGrid && Array.isArray(parsed.solutionGrid.rows)
            ? parsed.solutionGrid.rows
            : [];

        if (!grid.length) {
            return '<div class="mini-solved-box">N/A</div>';
        }

        const rowCount = grid.length;
        const colCount = Math.max(0, ...(grid.map(row => row.length)));

        const segments = (parsed.answerKeyEntries || [])
            .map(entry => {
                const dr = entry.direction === 'down' ? 1 : 0;
                const dc = entry.direction === 'across' ? 1 : 0;
                const letters = Array.from(String(entry.word || '').trim());
                const length = Math.max(1, letters.length);

                return {
                    startRow: entry.row,
                    startCol: entry.col,
                    endRow: entry.row + (dr * (length - 1)),
                    endCol: entry.col + (dc * (length - 1))
                };
            })
            .filter(seg => Number.isFinite(seg.startRow) && Number.isFinite(seg.startCol) && Number.isFinite(seg.endRow) && Number.isFinite(seg.endCol));

        let html = '<div class="mini-solved-box"><table class="mini-solved-grid mini-crossword-grid" aria-label="Mini solved crossword"><tbody>';
        grid.forEach(row => {
            html += '<tr>';
            row.forEach(cell => {
                if (cell.blocked) {
                    html += '<td class="blocked"></td>';
                } else {
                    html += `<td>${escapeHtml(cell.letter || '')}</td>`;
                }
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
        html += buildMiniSolutionOverlay(rowCount, colCount, segments, 'crossword');
        html += '</div>';

        return html;
    }

    function renderMiniWordFindSolution(parsed) {
        const rows = parsed && Array.isArray(parsed.gridRows) ? parsed.gridRows : [];
        if (!rows.length) {
            return '<div class="mini-solved-box">N/A</div>';
        }

        const rowCount = rows.length;
        const colCount = Math.max(0, ...(rows.map(row => row.length)));
        const segments = (parsed.answerKeyEntries || [])
            .map(entry => ({
                startRow: entry.startRow,
                startCol: entry.startCol,
                endRow: entry.endRow,
                endCol: entry.endCol
            }))
            .filter(seg => Number.isFinite(seg.startRow) && Number.isFinite(seg.startCol) && Number.isFinite(seg.endRow) && Number.isFinite(seg.endCol));

        let html = '<div class="mini-solved-box"><table class="mini-solved-grid mini-wordfind-grid" aria-label="Mini solved word find"><tbody>';
        rows.forEach(row => {
            html += '<tr>';
            row.forEach(cell => {
                html += `<td>${escapeHtml(cell || '')}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
        html += buildMiniSolutionOverlay(rowCount, colCount, segments, 'wordfind');
        html += '</div>';

        return html;
    }

    function buildMiniSolutionOverlay(rowCount, colCount, segments, variant) {
        if (!rowCount || !colCount || !segments || !segments.length) {
            return '';
        }

        const lines = segments.map(seg => {
            const x1 = (((seg.startCol - 1) + 0.5) / colCount) * 100;
            const y1 = (((seg.startRow - 1) + 0.5) / rowCount) * 100;
            const x2 = (((seg.endCol - 1) + 0.5) / colCount) * 100;
            const y2 = (((seg.endRow - 1) + 0.5) / rowCount) * 100;

            return `<line x1="${x1}%" y1="${y1}%" x2="${x2}%" y2="${y2}%"></line>`;
        }).join('');

        return `<svg class="mini-solved-overlay mini-solved-overlay-${variant}" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">${lines}</svg>`;
    }

    function buildCrosswordMarkup(parsed) {
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
                    gridHtml += `<td class="open" data-row="${rIdx + 1}" data-col="${cIdx + 1}">`;
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
                <details class="crossword-answer-key"><summary>Show answer key</summary><ul>${renderCrosswordAnswerKeyItems(parsed.answerKeyEntries)}</ul></details>
            </div>
        `;
    }

    function buildWordFindMarkup(parsed) {
        let gridHtml = '<table class="wordfind-grid" aria-label="Word find grid"><tbody>';
        parsed.gridRows.forEach((row, rIdx) => {
            gridHtml += '<tr>';
            row.forEach((cell, cIdx) => {
                gridHtml += `<td data-row="${rIdx + 1}" data-col="${cIdx + 1}">${escapeHtml(cell || '')}</td>`;
            });
            gridHtml += '</tr>';
        });
        gridHtml += '</tbody></table>';

        const wordsMarkup = parsed.words.length
            ? parsed.words.map(word => `<li>${escapeHtml(word)}</li>`).join('')
            : '<li>No word list available.</li>';

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
                <div class="wordfind-grid-wrap">${gridHtml}</div>
                <div class="wordfind-words">
                    <h6>Find these words</h6>
                    <ul>${wordsMarkup}</ul>
                </div>
                <details class="wordfind-answer-key"><summary>Show answer key</summary><ul>${renderWordFindAnswerKeyItems(parsed.answerKeyEntries)}</ul></details>
            </div>
        `;
    }

    function clearPuzzleHighlights(container) {
        if (!container) return;
        container.querySelectorAll('.answer-line-overlay').forEach(layer => layer.remove());
    }

    function getGridCellCenter(gridEl, row, col) {
        if (!gridEl || !Number.isFinite(row) || !Number.isFinite(col)) {
            return null;
        }

        const cell = gridEl.querySelector(`td[data-row="${row}"][data-col="${col}"]`);
        if (!cell) {
            return null;
        }

        return {
            x: cell.offsetLeft + (cell.offsetWidth / 2),
            y: cell.offsetTop + (cell.offsetHeight / 2)
        };
    }

    function drawAnswerLines(container, wrapSelector, gridSelector, segments, variantClass) {
        clearPuzzleHighlights(container);

        const wrap = container.querySelector(wrapSelector);
        const grid = container.querySelector(gridSelector);
        if (!wrap || !grid || !segments.length) {
            return;
        }

        const ns = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(ns, 'svg');
        svg.setAttribute('class', `answer-line-overlay ${variantClass}`);
        svg.setAttribute('width', String(grid.offsetWidth));
        svg.setAttribute('height', String(grid.offsetHeight));
        svg.setAttribute('viewBox', `0 0 ${grid.offsetWidth} ${grid.offsetHeight}`);
        svg.style.left = `${grid.offsetLeft}px`;
        svg.style.top = `${grid.offsetTop}px`;

        segments.forEach(seg => {
            const start = getGridCellCenter(grid, seg.startRow, seg.startCol);
            const end = getGridCellCenter(grid, seg.endRow, seg.endCol);
            if (!start || !end) return;

            const line = document.createElementNS(ns, 'line');
            line.setAttribute('x1', String(start.x));
            line.setAttribute('y1', String(start.y));
            line.setAttribute('x2', String(end.x));
            line.setAttribute('y2', String(end.y));
            svg.appendChild(line);
        });

        wrap.appendChild(svg);
    }

    function highlightCrosswordAnswers(container, entries) {
        const segments = entries
            .map(entry => {
                const dr = entry.direction === 'down' ? 1 : 0;
                const dc = entry.direction === 'across' ? 1 : 0;
                const letters = Array.from(String(entry.word || '').trim());
                const length = Math.max(1, letters.length);
                return {
                    startRow: entry.row,
                    startCol: entry.col,
                    endRow: entry.row + (dr * (length - 1)),
                    endCol: entry.col + (dc * (length - 1))
                };
            })
            .filter(seg => Number.isFinite(seg.startRow) && Number.isFinite(seg.startCol) && Number.isFinite(seg.endRow) && Number.isFinite(seg.endCol));

        drawAnswerLines(container, '.crossword-grid-wrap', '.crossword-grid', segments, 'crossword-lines');
    }

    function highlightWordFindAnswers(container, entries) {
        const segments = entries
            .map(entry => ({
                startRow: entry.startRow,
                startCol: entry.startCol,
                endRow: entry.endRow,
                endCol: entry.endCol
            }))
            .filter(seg => Number.isFinite(seg.startRow) && Number.isFinite(seg.startCol) && Number.isFinite(seg.endRow) && Number.isFinite(seg.endCol));

        drawAnswerLines(container, '.wordfind-grid-wrap', '.wordfind-grid', segments, 'wordfind-lines');
    }

    function bindAnswerKeyLogic(cardEl, type, parsed) {
        if (!cardEl) return;

        const selector = type === 'crossword' ? '.crossword-answer-key' : '.wordfind-answer-key';
        const details = cardEl.querySelector(selector);
        if (!details) return;

        details.addEventListener('toggle', function () {
            if (details.open) {
                if (type === 'crossword') {
                    highlightCrosswordAnswers(cardEl, parsed.answerKeyEntries || []);
                } else {
                    highlightWordFindAnswers(cardEl, parsed.answerKeyEntries || []);
                }
            } else {
                clearPuzzleHighlights(cardEl);
            }
        });
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

    function parseThemes(text) {
        const lines = String(text || '').split(/\r?\n/);
        return lines
            .map(s => s.trim().replace(/^\d+\s*\|\s*/, ''))
            .filter(Boolean);
    }

    function parseGrid(value) {
        const m = String(value || '').match(/(\d{1,2})\s*[x×]\s*(\d{1,2})/i);
        if (!m) return { cols: 16, rows: 12, label: '16 x 12' };
        const cols = Math.max(8, Math.min(30, parseInt(m[1], 10)));
        const rows = Math.max(6, Math.min(30, parseInt(m[2], 10)));
        return { cols, rows, label: `${cols} x ${rows}` };
    }

    async function readThemeFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(String(reader.result || ''));
            reader.onerror = reject;
            reader.readAsText(file, 'UTF-8');
        });
    }

    async function fetchPuzzle(theme, count, gridLabel, language, llmValue) {
        const lang = String(language || 'telugu').toLowerCase();
        const parts = String(llmValue || '').split(':');
        const bulkProvider = (parts[0] || '').trim().toLowerCase();
        const bulkModel = parts.slice(1).join(':').trim();
        const langLabels = {
            english: 'English',
            telugu: 'Telugu',
            hindi: 'Hindi',
            gujarati: 'Gujarati',
            malayalam: 'Malayalam'
        };
        const langLabel = langLabels[lang] || 'English';
        const question = `Create a word find with ${count} words about ${theme} in ${langLabel}, grid ${gridLabel}.`;
        const res = await fetch('chat_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                question,
                language: lang,
                llm_provider: bulkProvider,
                llm_model: bulkModel
            })
        });

        if (!res.ok) {
            return { ok: false, error: `HTTP ${res.status}` };
        }

        const raw = await res.text();
        let json;
        try {
            json = JSON.parse(raw);
        } catch (_) {
            return { ok: false, error: 'Non-JSON response' };
        }

        if (json.error) {
            return { ok: false, error: json.error };
        }

        return { ok: true, answer: String(json.answer || ''), provider: json.llm_provider || '', model: json.llm_model || '' };
    }

    function createResultCard(index, theme) {
        const card = document.createElement('article');
        card.className = 'puzzle-card';

        const title = document.createElement('div');
        title.className = 'puzzle-title';
        title.textContent = `#${index + 1} ${theme}`;
        card.appendChild(title);

        const meta = document.createElement('div');
        meta.className = 'puzzle-meta';
        meta.textContent = 'Status: Generating...';
        card.appendChild(meta);

        const content = document.createElement('div');
        const pre = document.createElement('pre');
        pre.className = 'puzzle-output';
        pre.textContent = 'Generating puzzle...';
        content.appendChild(pre);
        card.appendChild(content);

        resultsEl.appendChild(card);
        return { card, meta, content };
    }

    function updateResultCard(slot, theme, result, index) {
        if (result.ok) {
            const provider = result.provider ? String(result.provider).toUpperCase() : 'DEFAULT';
            slot.meta.textContent = `Status: OK | LLM: ${provider}${result.model ? ' / ' + result.model : ''}`;

            const answer = String(result.answer || '');
            const parsedCrossword = looksLikeCrosswordResponse(answer) ? parseCrosswordResponse(answer) : null;
            const parsedWordFind = !parsedCrossword && looksLikeWordFindResponse(answer) ? parseWordFindResponse(answer) : null;

            if (parsedCrossword) {
                slot.content.innerHTML = buildCrosswordMarkup(parsedCrossword);
                bindAnswerKeyLogic(slot.card, 'crossword', parsedCrossword);
                return {
                    ok: true,
                    index,
                    theme,
                    type: 'crossword',
                    parsed: parsedCrossword
                };
            } else if (parsedWordFind) {
                slot.content.innerHTML = buildWordFindMarkup(parsedWordFind);
                bindAnswerKeyLogic(slot.card, 'wordfind', parsedWordFind);
                return {
                    ok: true,
                    index,
                    theme,
                    type: 'wordfind',
                    parsed: parsedWordFind
                };
            } else {
                slot.content.innerHTML = `<pre class="puzzle-output${hasTelugu(answer) ? ' telugu' : ''}">${escapeHtml(answer)}</pre>`;
                return {
                    ok: true,
                    index,
                    theme,
                    type: 'raw',
                    parsed: null
                };
            }
        } else {
            slot.meta.textContent = `Status: Failed | ${result.error}`;
            slot.content.innerHTML = `<pre class="puzzle-output">${escapeHtml(`Failed to generate puzzle for theme: ${theme}\nReason: ${result.error}`)}</pre>`;
            return {
                ok: false,
                index,
                theme,
                type: 'error',
                parsed: null
            };
        }
    }

    async function generateAll(themes, count, gridLabel, language, llmValue) {
        resultsEl.innerHTML = '';
        latestPuzzleRecords = [];
        const total = themes.length;
        let done = 0;

        const slots = themes.map((theme, index) => createResultCard(index, theme));

        // Small bounded concurrency keeps generation fast but avoids overloading the PHP server.
        const concurrency = 3;
        let cursor = 0;

        async function worker() {
            while (cursor < total) {
                const i = cursor;
                cursor++;
                const theme = themes[i];
                progressEl.textContent = `Generating ${done + 1}/${total}: ${theme}`;
                const result = await fetchPuzzle(theme, count, gridLabel, language, llmValue);
                latestPuzzleRecords[i] = updateResultCard(slots[i], theme, result, i);
                done++;
                progressEl.textContent = `Generated ${done}/${total}`;
            }
        }

        const workers = [];
        for (let i = 0; i < Math.min(concurrency, total); i++) {
            workers.push(worker());
        }

        await Promise.all(workers);
        renderFinalAnswerKeyPages(latestPuzzleRecords);
        progressEl.textContent = `Done. Generated ${done} puzzle(s).`;
    }

    fileInput.addEventListener('change', async function () {
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;
        try {
            const text = await readThemeFile(file);
            themeList.value = text;
            progressEl.textContent = 'Loaded themes from file.';
        } catch (err) {
            progressEl.textContent = `Could not read file: ${err.message}`;
        }
    });

    generateBtn.addEventListener('click', async function () {
        const themes = parseThemes(themeList.value);
        if (!themes.length) {
            progressEl.textContent = 'Please provide at least one theme.';
            setPrintButtonState(false, 'Generate all puzzles first. Print is enabled after generation completes.');
            return;
        }

        const llmValue = llmSelect ? String(llmSelect.value || '').trim() : '';
        if (!llmValue) {
            progressEl.textContent = 'No LLM provider is available. Configure at least one API key to enable bulk generation.';
            setPrintButtonState(false, 'Print is disabled because puzzle generation cannot start without an available LLM provider.');
            return;
        }

        const count = Math.max(3, Math.min(20, parseInt(countInput.value || '10', 10) || 10));
        const grid = parseGrid(gridInput.value);
        const language = outputLanguage ? String(outputLanguage.value || 'telugu').toLowerCase() : 'telugu';
        generateBtn.disabled = true;
        setPrintButtonState(false, 'Please wait. Print is enabled after all puzzles finish generating.');
        try {
            await generateAll(themes, count, grid.label, language, llmValue);
            setPrintButtonState(true, 'Print / Save PDF');
        } finally {
            generateBtn.disabled = false;
        }
    });

    printBtn.addEventListener('click', function () {
        if (printBtn.disabled) {
            return;
        }
        window.print();
    });

    setPrintButtonState(false, 'Generate all puzzles first. Print is enabled after generation completes.');
});
