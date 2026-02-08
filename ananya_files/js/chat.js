// Simple chat frontend: sends user questions to chat_api.php and displays responses
document.addEventListener('DOMContentLoaded', function () {
    const sendBtn = document.getElementById('chat-send');
    const input = document.getElementById('chat-input');
    const windowEl = document.getElementById('chat-window');
    const langSelect = document.getElementById('language-select');

    function appendMessage(who, text) {
        const row = document.createElement('div');
        row.className = 'mb-2 d-flex ' + (who === 'user' ? 'justify-content-end' : 'justify-content-start');
        const bubble = document.createElement('div');
        bubble.className = 'p-2 rounded ' + (who === 'user' ? 'bg-primary text-white' : 'bg-light text-dark');
        bubble.style.maxWidth = '75%';
        bubble.innerText = text;
        row.appendChild(bubble);
        windowEl.appendChild(row);
        windowEl.scrollTop = windowEl.scrollHeight;
    }

    async function sendQuestion() {
        const q = input.value.trim();
        if(!q) return;
        appendMessage('user', q);
        input.value = '';
        appendMessage('assistant', '…thinking');

        try {
            const res = await fetch('chat_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({question: q, language: langSelect ? langSelect.value : 'english'})
            });
            const json = await res.json();
            // remove last 'thinking' message
            const last = windowEl.lastElementChild;
            if(last) last.remove();
            if(json.error) {
                appendMessage('assistant', 'Error: ' + json.error);
            } else {
                appendMessage('assistant', json.answer || JSON.stringify(json));
            }
        } catch (err) {
            const last = windowEl.lastElementChild;
            if(last) last.remove();
            appendMessage('assistant', 'Request failed: ' + err.message);
        }
    }

    sendBtn.addEventListener('click', sendQuestion);
    input.addEventListener('keydown', function (e) {
        if(e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendQuestion();
        }
    });
});
