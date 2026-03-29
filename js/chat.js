// Chat frontend: sends user questions to chat_api.php (which proxies to MCP server)
// Supports Markdown rendering for assistant responses via marked.js + DOMPurify
document.addEventListener('DOMContentLoaded', function () {
    const sendBtn = document.getElementById('chat-send');
    const input = document.getElementById('chat-input');
    const windowEl = document.getElementById('chat-window');
    const langSelect = document.getElementById('language-select');
    const languageFeedback = document.getElementById('language-feedback');
    const llmSelect = document.getElementById('llm-select');

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
        const t = String(text).toLowerCase();
        return (t.includes('word find puzzle') || t.includes('crossword puzzle')) && t.includes('answer key:');
    }

    function containsTeluguScript(text) {
        return /[\u0C00-\u0C7F]/.test(String(text || ''));
    }

    function appendMessage(who, text, source, llmProvider, llmModel) {
        const row = document.createElement('div');
        row.className = 'mb-2 d-flex ' + (who === 'user' ? 'justify-content-end' : 'justify-content-start');

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + who;

        if (who === 'user') {
            bubble.textContent = text;
        } else {
            if (looksLikePuzzleResponse(text)) {
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
