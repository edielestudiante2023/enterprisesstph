<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1c2437">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Otto - Portal Cliente</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --white-primary: #ffffff;
            --white-secondary: #f8f9fa;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --shadow-deep: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-medium: 0 5px 20px rgba(0, 0, 0, 0.15);
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
            --chat-user-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --chat-assistant-bg: #ffffff;
            --chat-tool-bg: #f0f4f8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-dark);
            height: 100vh;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Navbar */
        .navbar-custom {
            background: #fffafa;
            box-shadow: var(--shadow-deep);
            padding: 12px 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 2px solid var(--gold-primary);
            flex-shrink: 0;
        }

        .header-logos-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .header-logos-custom img {
            max-height: 50px;
            margin-right: 15px;
            transition: var(--transition);
        }

        .nav-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .nav-title i {
            color: var(--gold-primary);
            font-size: 1.3rem;
        }

        .btn-back {
            background: var(--primary-dark);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-back:hover {
            background: var(--gold-primary);
            color: white;
            transform: translateY(-1px);
        }

        /* Chat container */
        .chat-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            padding: 0 15px;
            overflow: hidden;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0;
            scroll-behavior: smooth;
        }

        .chat-messages::-webkit-scrollbar { width: 6px; }
        .chat-messages::-webkit-scrollbar-track { background: transparent; }
        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.15);
            border-radius: 3px;
        }

        /* Mensajes */
        .message {
            display: flex;
            margin-bottom: 16px;
            animation: fadeInUp 0.3s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.user { justify-content: flex-end; }
        .message.assistant { justify-content: flex-start; }

        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            margin-top: 4px;
        }

        .message.user .message-avatar {
            background: var(--primary-dark);
            color: white;
            margin-left: 10px;
            order: 2;
        }

        .message.assistant .message-avatar {
            background: white;
            margin-right: 10px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .message.assistant .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .message-bubble {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 16px;
            line-height: 1.5;
            font-size: 0.92rem;
            position: relative;
        }

        .message.user .message-bubble {
            background: var(--chat-user-bg);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.assistant .message-bubble {
            background: var(--chat-assistant-bg);
            color: var(--primary-dark);
            border-bottom-left-radius: 4px;
            box-shadow: var(--shadow-light);
        }

        /* Markdown dentro del bubble */
        .message-bubble p { margin-bottom: 8px; }
        .message-bubble p:last-child { margin-bottom: 0; }
        .message-bubble ul, .message-bubble ol { margin: 8px 0; padding-left: 20px; }
        .message-bubble li { margin-bottom: 4px; }
        .message-bubble code {
            background: rgba(0,0,0,0.08);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85em;
            font-family: 'Consolas', 'Monaco', monospace;
        }
        .message.user .message-bubble code { background: rgba(255,255,255,0.2); }
        .message-bubble pre {
            background: #1e1e2e;
            color: #cdd6f4;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 8px 0;
            font-size: 0.82em;
        }
        .message-bubble pre code { background: none; padding: 0; color: inherit; }
        .message-bubble table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 0.85em;
        }
        .message-bubble table th,
        .message-bubble table td {
            border: 1px solid #dee2e6;
            padding: 6px 10px;
            text-align: left;
        }
        .message-bubble table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .message.user .message-bubble table th,
        .message.user .message-bubble table td { border-color: rgba(255,255,255,0.3); }
        .message.user .message-bubble table th { background: rgba(255,255,255,0.15); }
        .message-bubble strong { font-weight: 600; }
        .message-bubble h3 { font-size: 1em; font-weight: 700; margin: 10px 0 6px; }
        .message-bubble h4 { font-size: 0.95em; font-weight: 600; margin: 8px 0 4px; }

        /* Tool badge */
        .tools-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(189, 151, 81, 0.12);
            color: #8a6d2b;
            font-size: 0.72rem;
            padding: 3px 8px;
            border-radius: 10px;
            margin-top: 6px;
            font-weight: 500;
        }
        .tools-badge i { font-size: 0.68rem; }

        /* Typing indicator */
        .typing-indicator { display: none; margin-bottom: 16px; }
        .typing-indicator.active { display: flex; }

        .typing-dots {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            background: var(--chat-assistant-bg);
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            box-shadow: var(--shadow-light);
        }

        .typing-dots span {
            width: 8px;
            height: 8px;
            background: #adb5bd;
            border-radius: 50%;
            animation: typingBounce 1.4s infinite ease-in-out;
        }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* Input area */
        .chat-input-area {
            padding: 16px 0 20px;
            flex-shrink: 0;
        }

        .input-container {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            background: white;
            border-radius: 24px;
            padding: 8px 8px 8px 20px;
            box-shadow: var(--shadow-medium);
            border: 2px solid transparent;
            transition: var(--transition);
        }

        .input-container:focus-within { border-color: var(--gold-primary); }

        .input-container textarea {
            flex: 1;
            border: none;
            outline: none;
            resize: none;
            font-size: 0.92rem;
            font-family: inherit;
            line-height: 1.5;
            max-height: 120px;
            padding: 8px 0;
            background: transparent;
        }

        .input-container textarea::placeholder { color: #adb5bd; }

        .btn-send {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .btn-send:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 15px rgba(189, 151, 81, 0.4);
        }

        .btn-send:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        /* Welcome message */
        .welcome-message {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .welcome-message .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .welcome-message h3 {
            color: var(--primary-dark);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .welcome-message p {
            font-size: 0.9rem;
            max-width: 500px;
            margin: 0 auto 20px;
        }

        .suggestion-chips {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
        }

        .suggestion-chip {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.82rem;
            cursor: pointer;
            transition: var(--transition);
            color: var(--secondary-dark);
        }

        .suggestion-chip:hover {
            background: var(--primary-dark);
            color: white;
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-logos-custom img { max-height: 35px; }
            .message-bubble { max-width: 85%; }
            .nav-title { font-size: 0.95rem; }
            .btn-back span { display: none; }
        }

        @media (max-width: 480px) {
            .header-logos-custom img { max-height: 28px; }
            .message-bubble { max-width: 90%; font-size: 0.88rem; }
            .welcome-message { padding: 20px 10px; }
            .suggestion-chips { gap: 6px; }
            .suggestion-chip { font-size: 0.78rem; padding: 6px 12px; }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="container">
            <div class="header-logos-custom">
                <div style="display:flex; align-items:center; gap: 10px;">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid" style="max-height:40px;">
                </div>
                <div class="nav-title">
                    <img src="<?= base_url('otto/otto.png') ?>" alt="Otto" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                    <span>Otto · <?= esc($usuario['nombre_copropiedad'] ?: $usuario['nombre']) ?></span>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <button onclick="finalizarConversacion()" style="background:#c0392b;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-stop-circle"></i>
                        <span>Finalizar</span>
                    </button>
                    <a href="<?= base_url('client/panel') ?>" class="btn-back">
                        <i class="fas fa-arrow-left me-1"></i>
                        <span>Panel</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Chat -->
    <div class="chat-wrapper">
        <!-- Mensajes -->
        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message" id="welcomeMessage">
                <div class="icon-circle">
                    <img src="<?= base_url('otto/otto.png') ?>" alt="Otto" style="width:70px;height:70px;object-fit:cover;border-radius:50%;">
                </div>
                <h3>Hola, <?= esc($usuario['nombre']) ?></h3>
                <p>Soy Otto, tu asistente SST. Puedo consultarte el estado de seguridad y salud en el trabajo de <strong><?= esc($usuario['nombre_copropiedad'] ?: 'tu copropiedad') ?></strong>. ¿En qué te ayudo hoy?</p>
                <div class="suggestion-chips">
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Pendientes abiertos</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Estado de las inspecciones</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Capacitaciones del año</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Mantenimientos por vencer</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Últimas visitas del consultor</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Resumen del plan de trabajo</div>
                </div>
            </div>
        </div>

        <!-- Typing indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <div class="message-avatar" style="background:white; width:36px; height:36px; border-radius:50%; overflow:hidden; margin-right:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <img src="<?= base_url('otto/otto.png') ?>" alt="Otto" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
            <div class="input-container">
                <textarea id="messageInput" rows="1" placeholder="Escribe tu consulta..." onkeydown="handleKeyDown(event)"></textarea>
                <button class="btn-send" id="btnSend" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // =====================================================================
        // Estado
        // =====================================================================
        const conversationHistory = [];
        let isProcessing = false;
        const BASE_URL = '<?= base_url() ?>';

        // =====================================================================
        // Envío de mensajes
        // =====================================================================
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (!message || isProcessing) return;

            const welcome = document.getElementById('welcomeMessage');
            if (welcome) welcome.style.display = 'none';

            appendMessage('user', message);
            conversationHistory.push({ role: 'user', content: message });

            input.value = '';
            autoResizeTextarea(input);
            setProcessing(true);

            fetch(BASE_URL + 'client-chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    history: conversationHistory.slice(-20)
                })
            })
            .then(r => r.json())
            .then(data => {
                setProcessing(false);
                if (data.success) {
                    const tools = data.tools_used || [];
                    appendMessage('assistant', data.response, tools);
                    conversationHistory.push({ role: 'assistant', content: data.response });
                } else {
                    appendMessage('assistant', 'Error: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(err => {
                setProcessing(false);
                appendMessage('assistant', 'Error de conexión: ' + err.message);
            });
        }

        function sendSuggestion(el) {
            document.getElementById('messageInput').value = el.textContent;
            sendMessage();
        }

        // =====================================================================
        // Renderizado de mensajes
        // =====================================================================
        function appendMessage(role, content, toolsUsed = []) {
            const container = document.getElementById('chatMessages');
            const div = document.createElement('div');
            div.className = 'message ' + role;

            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            if (role === 'user') {
                avatar.innerHTML = '<i class="fas fa-user"></i>';
            } else {
                avatar.innerHTML = '<img src="' + BASE_URL + 'otto/otto.png" alt="Otto">';
            }

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';

            if (role === 'assistant') {
                bubble.innerHTML = marked.parse(content || '');
            } else {
                bubble.textContent = content;
            }

            if (toolsUsed && toolsUsed.length > 0) {
                const badge = document.createElement('div');
                badge.className = 'tools-badge';
                const toolNames = [...new Set(toolsUsed.map(t => t.tool))];
                badge.innerHTML = '<i class="fas fa-search"></i> ' + toolNames.join(', ');
                bubble.appendChild(badge);
            }

            div.appendChild(avatar);
            div.appendChild(bubble);
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }

        // =====================================================================
        // UI helpers
        // =====================================================================
        function setProcessing(state) {
            isProcessing = state;
            document.getElementById('btnSend').disabled = state;
            document.getElementById('typingIndicator').classList.toggle('active', state);
            if (state) {
                document.getElementById('chatMessages').scrollTop =
                    document.getElementById('chatMessages').scrollHeight;
            }
        }

        function handleKeyDown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() { autoResizeTextarea(this); });

        function autoResizeTextarea(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        }

        document.getElementById('messageInput').focus();

        // =====================================================================
        // INACTIVIDAD / cierre de sesión
        // =====================================================================
        const INACTIVITY_MS  = 10 * 60 * 1000;
        let inactivityTimer  = null;
        let sessionEmailSent = false;

        function sendSessionEmail() {
            if (sessionEmailSent || conversationHistory.length === 0) return;
            sessionEmailSent = true;
            const payload = JSON.stringify({ history: conversationHistory });
            if (navigator.sendBeacon) {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(BASE_URL + 'client-chat/end-session', blob);
            } else {
                fetch(BASE_URL + 'client-chat/end-session', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: payload,
                    keepalive: true
                });
            }
        }

        function resetInactivityTimer() {
            sessionEmailSent = false;
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(sendSessionEmail, INACTIVITY_MS);
        }

        ['keydown', 'mousedown', 'touchstart', 'click'].forEach(function(evt) {
            document.addEventListener(evt, resetInactivityTimer, { passive: true });
        });

        window.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') sendSessionEmail();
        });
        window.addEventListener('beforeunload', sendSessionEmail);

        resetInactivityTimer();

        // =====================================================================
        // TECLADO VIRTUAL móvil
        // =====================================================================
        if (window.visualViewport) {
            function adjustForKeyboard() {
                const vh = window.visualViewport.height;
                document.body.style.height = vh + 'px';
                const msgs = document.getElementById('chatMessages');
                if (msgs) msgs.scrollTop = msgs.scrollHeight;
            }
            window.visualViewport.addEventListener('resize', adjustForKeyboard);
            window.visualViewport.addEventListener('scroll', adjustForKeyboard);
        }

        // =====================================================================
        // Finalizar conversación
        // =====================================================================
        async function finalizarConversacion() {
            if (conversationHistory.length === 0) {
                window.location.href = BASE_URL + 'client/panel';
                return;
            }

            if (!confirm('¿Finalizar la conversación con Otto?')) return;

            sendSessionEmail();
            window.location.href = BASE_URL + 'client/panel';
        }

        // =====================================================================
        // Auto-enviar pregunta desde query param ?q=...
        // =====================================================================
        (function() {
            const params = new URLSearchParams(window.location.search);
            const q = params.get('q');
            if (q && q.trim()) {
                document.getElementById('messageInput').value = q.trim();
                setTimeout(sendMessage, 300);
                // Clean URL without reload
                history.replaceState(null, '', window.location.pathname);
            }
        })();
    </script>
</body>

</html>
