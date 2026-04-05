(function () {
    const container = document.querySelector('.chat-container');
    if (!container) return;

    const messagesEl = document.getElementById('messages');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('messageInput');
    const imageInput = document.getElementById('imageInput');
    const dropZone = document.getElementById('dropZone');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const sendImageBtn = document.getElementById('sendImageBtn');
    const cancelImageBtn = document.getElementById('cancelImageBtn');

    const sessionId = Number(container.dataset.sessionId || 0);
    const topic = container.dataset.topic || '';

    let selectedFile = null;
    let typingNode = null;

    function nowString() {
        return new Date().toISOString().slice(0, 19).replace('T', ' ');
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addMessage(role, content, createdAt = nowString()) {
        const article = document.createElement('article');
        article.className = `message ${role === 'assistant' ? 'assistant' : 'user'}`;

        const avatar = document.createElement('div');
        avatar.className = 'avatar';
        avatar.textContent = role === 'assistant' ? '🤖' : '👤';

        const wrap = document.createElement('div');
        wrap.className = 'message-bubble-wrap';

        const bubble = document.createElement('p');
        bubble.className = 'message-bubble';
        bubble.textContent = content;

        const meta = document.createElement('small');
        meta.textContent = createdAt;

        wrap.appendChild(bubble);
        wrap.appendChild(meta);
        article.appendChild(avatar);
        article.appendChild(wrap);
        messagesEl.appendChild(article);
        scrollToBottom();
    }

    function showTyping() {
        if (typingNode) return;

        typingNode = document.createElement('article');
        typingNode.className = 'message assistant typing-indicator';
        typingNode.innerHTML = `
            <div class="avatar">🤖</div>
            <div class="message-bubble-wrap">
                <p class="message-bubble">Печатает...
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </p>
            </div>
        `;

        messagesEl.appendChild(typingNode);
        scrollToBottom();
    }

    function hideTyping() {
        if (!typingNode) return;
        typingNode.remove();
        typingNode = null;
    }

    function clearPreview() {
        selectedFile = null;
        imageInput.value = '';
        imagePreview.classList.add('hidden');
        previewImage.removeAttribute('src');
    }

    function selectImage(file) {
        if (!file || !file.type.startsWith('image/')) {
            addMessage('assistant', 'Можно прикрепить только изображение (PNG, WEBP, JPG).');
            return;
        }

        selectedFile = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            previewImage.src = String(event.target?.result || '');
            imagePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    async function sendRequest(url, options) {
        const response = await fetch(url, options);
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.error || 'Ошибка API');
        }
        return payload;
    }

    async function sendTextMessage() {
        const message = input.value.trim();
        if (!message) return;

        addMessage('user', message);
        input.value = '';
        showTyping();

        try {
            const payload = await sendRequest('api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message,
                    session_id: sessionId,
                    topic,
                }),
            });
            addMessage('assistant', payload.reply, payload.created_at);
        } catch (error) {
            addMessage('assistant', `Ошибка: ${error.message}`);
        } finally {
            hideTyping();
        }
    }

    async function sendImageMessage() {
        if (!selectedFile) return;

        const message = input.value.trim();
        addMessage('user', message || '📎 Изображение загружено. Проанализируй, пожалуйста.');
        input.value = '';
        showTyping();

        try {
            const formData = new FormData();
            formData.append('image', selectedFile);
            formData.append('message', message);
            formData.append('session_id', String(sessionId));
            formData.append('topic', topic);

            const payload = await sendRequest('api/chat.php', {
                method: 'POST',
                body: formData,
            });

            addMessage('assistant', payload.reply, payload.created_at);
            clearPreview();
        } catch (error) {
            addMessage('assistant', `Ошибка загрузки изображения: ${error.message}`);
        } finally {
            hideTyping();
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (selectedFile) {
            await sendImageMessage();
            return;
        }
        await sendTextMessage();
    });

    imageInput?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        if (file) selectImage(file);
    });

    sendImageBtn?.addEventListener('click', sendImageMessage);
    cancelImageBtn?.addEventListener('click', clearPreview);

    ['dragenter', 'dragover'].forEach((type) => {
        dropZone?.addEventListener(type, (event) => {
            event.preventDefault();
            dropZone.classList.add('active');
        });
    });

    ['dragleave', 'drop'].forEach((type) => {
        dropZone?.addEventListener(type, (event) => {
            event.preventDefault();
            dropZone.classList.remove('active');
        });
    });

    dropZone?.addEventListener('drop', (event) => {
        const file = event.dataTransfer?.files?.[0];
        if (file) selectImage(file);
    });

    document.addEventListener('paste', (event) => {
        const items = event.clipboardData?.items;
        if (!items) return;

        for (const item of items) {
            if (item.type.startsWith('image/')) {
                const file = item.getAsFile();
                if (file) {
                    selectImage(file);
                    break;
                }
            }
        }
    });

    scrollToBottom();
})();
