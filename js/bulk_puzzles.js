document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('theme-file');
    const themeList = document.getElementById('theme-list');
    const outputLanguage = document.getElementById('output-language');
    const countInput = document.getElementById('word-count');
    const gridInput = document.getElementById('grid-size');
    const generateBtn = document.getElementById('generate-btn');
    const printBtn = document.getElementById('print-btn');
    const progressEl = document.getElementById('progress');
    const resultsEl = document.getElementById('results');

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

    function parseThemes(text) {
        const lines = String(text || '').split(/\r?\n/);
        return lines.map(s => s.trim()).filter(Boolean);
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

    async function fetchPuzzle(theme, count, gridLabel, language) {
        const lang = String(language || 'telugu').toLowerCase();
        const langLabel = lang === 'english' ? 'English' : 'Telugu';
        const question = `Give me a ${langLabel} puzzle on ${theme} (default count = ${count}, grid size ${gridLabel})`;
        const res = await fetch('chat_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                question,
                language: lang,
                llm_provider: '',
                llm_model: ''
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

        const pre = document.createElement('pre');
        pre.className = 'puzzle-output';
        pre.textContent = 'Generating puzzle...';
        card.appendChild(pre);

        resultsEl.appendChild(card);
        return { meta, pre };
    }

    function updateResultCard(slot, theme, result) {
        if (result.ok) {
            const provider = result.provider ? String(result.provider).toUpperCase() : 'DEFAULT';
            slot.meta.textContent = `Status: OK | LLM: ${provider}${result.model ? ' / ' + result.model : ''}`;
            slot.pre.className = 'puzzle-output' + (hasTelugu(result.answer) ? ' telugu' : '');
            slot.pre.innerHTML = escapeHtml(result.answer);
        } else {
            slot.meta.textContent = `Status: Failed | ${result.error}`;
            slot.pre.className = 'puzzle-output';
            slot.pre.innerHTML = escapeHtml(`Failed to generate puzzle for theme: ${theme}\nReason: ${result.error}`);
        }
    }

    async function generateAll(themes, count, gridLabel, language) {
        resultsEl.innerHTML = '';
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
                const result = await fetchPuzzle(theme, count, gridLabel, language);
                updateResultCard(slots[i], theme, result);
                done++;
                progressEl.textContent = `Generated ${done}/${total}`;
            }
        }

        const workers = [];
        for (let i = 0; i < Math.min(concurrency, total); i++) {
            workers.push(worker());
        }

        await Promise.all(workers);
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
            return;
        }

        const count = Math.max(3, Math.min(20, parseInt(countInput.value || '10', 10) || 10));
        const grid = parseGrid(gridInput.value);
        const language = outputLanguage ? String(outputLanguage.value || 'telugu').toLowerCase() : 'telugu';
        generateBtn.disabled = true;
        try {
            await generateAll(themes, count, grid.label, language);
        } finally {
            generateBtn.disabled = false;
        }
    });

    printBtn.addEventListener('click', function () {
        window.print();
    });
});
