// Chat frontend: sends user questions to chat_api.php (which proxies to MCP server)
// Supports Markdown rendering for assistant responses via marked.js + DOMPurify
document.addEventListener('DOMContentLoaded', function () {
    const sendBtn = document.getElementById('chat-send');
    const input = document.getElementById('chat-input');
    const windowEl = document.getElementById('chat-window');
    const langSelect = document.getElementById('language-select');

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

    function appendMessage(who, text, source) {
        const row = document.createElement('div');
        row.className = 'mb-2 d-flex ' + (who === 'user' ? 'justify-content-end' : 'justify-content-start');

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + who;

        if (who === 'user') {
            bubble.textContent = text;
        } else {
            bubble.innerHTML = renderMarkdown(text);
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

        appendMessage('user', q);
        input.value = '';
        input.focus();
        sendBtn.disabled = true;
        appendTypingIndicator();

        try {
            const res = await fetch('chat_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    question: q,
                    language: langSelect ? langSelect.value : 'english'
                })
            });

            removeTypingIndicator();

            if (!res.ok) {
                appendMessage('assistant', `Server error (HTTP ${res.status}). Please try again.`);
                sendBtn.disabled = false;
                return;
            }

            const json = await res.json();

            if (json.error) {
                appendMessage('assistant', '**Error:** ' + json.error);
            } else {
                const answer = json.answer || JSON.stringify(json, null, 2);
                const source = json.source || 'mcp';
                appendMessage('assistant', answer, source);
            }
        } catch (err) {
            removeTypingIndicator();
            appendMessage('assistant', '**Connection failed.** Is the server running?\n\n`' + err.message + '`');
        }

        sendBtn.disabled = false;
    }

    sendBtn.addEventListener('click', sendQuestion);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendQuestion();
        }
    });

    // Focus input on load
    input.focus();
});
