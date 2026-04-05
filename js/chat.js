(function () {
    const container = document.querySelector('.chat-container');
    if (!container) return;

    const messagesEl = document.getElementById('messages');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('messageInput');
    const sessionId = Number(container.dataset.sessionId || 0);

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addMessage(role, content, createdAt) {
        const article = document.createElement('article');
        article.className = `message ${role === 'assistant' ? 'assistant' : 'user'}`;

        const avatar = document.createElement('div');
        avatar.className = 'avatar';
        avatar.textContent = role === 'assistant' ? '🤖' : '👤';

        const body = document.createElement('div');
        const text = document.createElement('p');
        text.textContent = content;
        const meta = document.createElement('small');
        meta.textContent = createdAt;

        body.appendChild(text);
        body.appendChild(meta);
        article.appendChild(avatar);
        article.appendChild(body);
        messagesEl.appendChild(article);
        scrollToBottom();
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        const timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');
        addMessage('user', message, timestamp);
        input.value = '';

        try {
            const response = await fetch('api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message,
                    session_id: sessionId,
                }),
            });

            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.error || 'API error');
            }
            addMessage('assistant', payload.reply, payload.created_at);
        } catch (error) {
            addMessage('assistant', `Ошибка: ${error.message}`, timestamp);
        }
    });

    scrollToBottom();
})();
