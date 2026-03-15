<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1c2437">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Otto - Asistente SST</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link rel="manifest" href="<?= base_url('manifest_chat.json') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192.png?v=2') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Marked.js para renderizar Markdown -->
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

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

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

        .message.user {
            justify-content: flex-end;
        }

        .message.assistant {
            justify-content: flex-start;
        }

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
        .message.user .message-bubble code {
            background: rgba(255,255,255,0.2);
        }
        .message-bubble pre {
            background: #1e1e2e;
            color: #cdd6f4;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 8px 0;
            font-size: 0.82em;
        }
        .message-bubble pre code {
            background: none;
            padding: 0;
            color: inherit;
        }
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
        .message.user .message-bubble table td {
            border-color: rgba(255,255,255,0.3);
        }
        .message.user .message-bubble table th {
            background: rgba(255,255,255,0.15);
        }
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

        .tools-badge i {
            font-size: 0.68rem;
        }

        /* Typing indicator */
        .typing-indicator {
            display: none;
            margin-bottom: 16px;
        }

        .typing-indicator.active {
            display: flex;
        }

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

        .input-container:focus-within {
            border-color: var(--gold-primary);
        }

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

        .input-container textarea::placeholder {
            color: #adb5bd;
        }

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

        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

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
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
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

        /* Confirm buttons */
        .confirm-buttons {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
        }

        .btn-confirm {
            background: #198754;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 16px;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-confirm:hover { background: #157347; }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 16px;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-cancel:hover { background: #565e64; }

        .btn-delete-challenge {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 16px;
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-delete-challenge:hover { background: #bb2d3b; }

        /* Schema panel */
        .schema-toggle {
            position: fixed;
            bottom: 100px;
            right: 20px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-dark);
            color: var(--gold-primary);
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: var(--shadow-medium);
            z-index: 100;
            transition: var(--transition);
        }

        .schema-toggle:hover {
            transform: scale(1.1);
        }

        .schema-panel {
            position: fixed;
            right: -400px;
            top: 0;
            bottom: 0;
            width: 380px;
            background: white;
            box-shadow: -5px 0 30px rgba(0,0,0,0.15);
            z-index: 1001;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .schema-panel.open {
            right: 0;
        }

        .schema-panel-header {
            padding: 20px;
            background: var(--primary-dark);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schema-panel-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .schema-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        .schema-table-item {
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            transition: var(--transition);
        }

        .schema-table-item:hover {
            background: #f0f4f8;
        }

        .schema-table-item .table-name {
            font-weight: 500;
            color: var(--primary-dark);
        }

        .schema-table-item .row-count {
            color: #6c757d;
            font-size: 0.75rem;
        }

        .schema-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1000;
        }

        .schema-overlay.open {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-logos-custom img { max-height: 35px; }
            .message-bubble { max-width: 85%; }
            .schema-panel { width: 100%; right: -100%; }
            .schema-toggle { bottom: 90px; right: 12px; }
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
                    Otto
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <button onclick="finalizarConversacion()" style="background:#c0392b;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-stop-circle"></i>
                        <span>Finalizar</span>
                    </button>
                    <a href="<?= base_url('consultant/dashboard') ?>" class="btn-back">
                        <i class="fas fa-arrow-left me-1"></i>
                        <span>Dashboard</span>
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
                <div class="icon-circle" style="background:white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <img src="<?= base_url('otto/otto.png') ?>" alt="Otto" style="width:70px;height:70px;object-fit:cover;border-radius:50%;">
                </div>
                <h3>Hola, soy Otto</h3>
                <p>Tu asistente virtual de SST. Tengo acceso a todas las tablas del sistema. Puedo consultar datos, actualizar registros y ayudarte con lo que necesites.</p>
                <div class="suggestion-chips">
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Listar clientes activos</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Pendientes abiertos por cliente</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Contratos por vencer</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Resumen de inspecciones</div>
                    <div class="suggestion-chip" onclick="sendSuggestion(this)">Tablas disponibles</div>
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

    <!-- Schema toggle button -->
    <button class="schema-toggle" onclick="toggleSchema()" title="Ver tablas">
        <i class="fas fa-database"></i>
    </button>

    <!-- Schema panel -->
    <div class="schema-overlay" id="schemaOverlay" onclick="toggleSchema()"></div>
    <div class="schema-panel" id="schemaPanel">
        <div class="schema-panel-header">
            <h5><i class="fas fa-database me-2"></i>Tablas</h5>
            <button onclick="toggleSchema()" style="background:none; border:none; color:white; font-size:1.2rem; cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="schema-panel-body" id="schemaPanelBody">
            <div class="text-center text-muted py-4">
                <i class="fas fa-spinner fa-spin"></i> Cargando tablas...
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

            // Ocultar welcome
            const welcome = document.getElementById('welcomeMessage');
            if (welcome) welcome.style.display = 'none';

            // Agregar mensaje del usuario
            appendMessage('user', message);
            conversationHistory.push({ role: 'user', content: message });

            input.value = '';
            autoResizeTextarea(input);
            setProcessing(true);

            fetch(BASE_URL + 'chat/send', {
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
                    const hasSimpleWrite = tools.some(t =>
                        (t.tool === 'execute_update' || t.tool === 'execute_insert') && t.status === 'ok'
                    );
                    const hasDelete = tools.some(t => t.tool === 'execute_delete' && t.status === 'ok');
                    const confirmType = hasDelete ? 'arithmetic' : (hasSimpleWrite ? 'simple' : null);
                    appendMessage('assistant', data.response, tools, confirmType);
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
        function appendMessage(role, content, toolsUsed = [], confirmType = null) {
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

            // Tools badge
            if (toolsUsed && toolsUsed.length > 0) {
                const badge = document.createElement('div');
                badge.className = 'tools-badge';
                const toolNames = [...new Set(toolsUsed.map(t => t.tool))];
                badge.innerHTML = '<i class="fas fa-wrench"></i> ' + toolNames.join(', ');
                bubble.appendChild(badge);
            }

            // Botones de confirmación
            if (confirmType === 'simple') {
                const confirmDiv = document.createElement('div');
                confirmDiv.className = 'confirm-buttons';
                confirmDiv.innerHTML = `
                    <button class="btn-confirm" onclick="confirmOperation(true, this)">
                        <i class="fas fa-check me-1"></i>Confirmar
                    </button>
                    <button class="btn-cancel" onclick="confirmOperation(false, this)">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                `;
                bubble.appendChild(confirmDiv);
            } else if (confirmType === 'arithmetic') {
                const confirmDiv = document.createElement('div');
                confirmDiv.className = 'confirm-buttons';
                confirmDiv.innerHTML = `
                    <button class="btn-delete-challenge" onclick="startDeleteChallenge(this)">
                        <i class="fas fa-exclamation-triangle me-1"></i>Eliminar (requiere verificación)
                    </button>
                    <button class="btn-cancel" onclick="cancelDelete(this)">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                `;
                bubble.appendChild(confirmDiv);
            }

            div.appendChild(avatar);
            div.appendChild(bubble);
            container.appendChild(div);

            container.scrollTop = container.scrollHeight;
        }

        // =====================================================================
        // Confirmación SIMPLE (UPDATE/INSERT)
        // =====================================================================
        function confirmOperation(confirm, btnEl) {
            const buttonsDiv = btnEl.closest('.confirm-buttons');
            buttonsDiv.innerHTML = confirm
                ? '<span style="color:#198754;font-size:0.85rem;"><i class="fas fa-spinner fa-spin me-1"></i>Ejecutando...</span>'
                : '<span style="color:#6c757d;font-size:0.85rem;"><i class="fas fa-ban me-1"></i>Cancelado</span>';

            fetch(BASE_URL + 'chat/confirm', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ confirm: confirm })
            })
            .then(r => r.json())
            .then(data => {
                const msg = data.success ? (data.message || 'OK') : (data.error || 'Error');
                const color = data.success && confirm ? '#198754' : (data.success ? '#6c757d' : '#dc3545');
                const icon = data.success && confirm ? 'check-circle' : (data.success ? 'ban' : 'exclamation-circle');
                buttonsDiv.innerHTML = `<span style="color:${color};font-size:0.85rem;"><i class="fas fa-${icon} me-1"></i>${msg}</span>`;
            })
            .catch(err => {
                buttonsDiv.innerHTML = `<span style="color:#dc3545;font-size:0.85rem;">Error: ${err.message}</span>`;
            });
        }

        // =====================================================================
        // Confirmación DOBLE ARITMÉTICA (DELETE)
        // =====================================================================
        function startDeleteChallenge(btnEl) {
            const buttonsDiv = btnEl.closest('.confirm-buttons');
            buttonsDiv.innerHTML = '<span style="color:#f39c12;font-size:0.85rem;"><i class="fas fa-spinner fa-spin me-1"></i>Generando reto...</span>';

            fetch(BASE_URL + 'chat/confirm-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: 'challenge' })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    buttonsDiv.innerHTML = `<span style="color:#dc3545;font-size:0.85rem;">${data.error}</span>`;
                    return;
                }
                buttonsDiv.innerHTML = `
                    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:10px;margin-top:4px;">
                        <div style="font-weight:600;color:#856404;font-size:0.85rem;margin-bottom:6px;">
                            <i class="fas fa-shield-alt me-1"></i>${data.challenge}
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <input type="number" id="deleteAnswer" class="form-control form-control-sm" style="width:80px;border-radius:8px;" placeholder="?">
                            <button class="btn-confirm" style="font-size:0.78rem;padding:4px 12px;" onclick="verifyDeleteAnswer(this)">
                                <i class="fas fa-check me-1"></i>Verificar
                            </button>
                            <button class="btn-cancel" style="font-size:0.78rem;padding:4px 12px;" onclick="cancelDelete(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
            })
            .catch(err => {
                buttonsDiv.innerHTML = `<span style="color:#dc3545;font-size:0.85rem;">Error: ${err.message}</span>`;
            });
        }

        function verifyDeleteAnswer(btnEl) {
            const container = btnEl.closest('.confirm-buttons');
            const answer = document.getElementById('deleteAnswer').value;

            if (!answer) return;

            container.innerHTML = '<span style="color:#f39c12;font-size:0.85rem;"><i class="fas fa-spinner fa-spin me-1"></i>Verificando...</span>';

            fetch(BASE_URL + 'chat/confirm-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: 'verify', answer: parseInt(answer) })
            })
            .then(r => r.json())
            .then(data => {
                const color = data.success ? '#198754' : '#dc3545';
                const icon = data.success ? 'check-circle' : 'exclamation-circle';
                container.innerHTML = `<span style="color:${color};font-size:0.85rem;"><i class="fas fa-${icon} me-1"></i>${data.message || data.error}</span>`;
            })
            .catch(err => {
                container.innerHTML = `<span style="color:#dc3545;font-size:0.85rem;">Error: ${err.message}</span>`;
            });
        }

        function cancelDelete(btnEl) {
            const container = btnEl.closest('.confirm-buttons');
            container.innerHTML = '<span style="color:#6c757d;font-size:0.85rem;"><i class="fas fa-ban me-1"></i>Eliminación cancelada</span>';

            fetch(BASE_URL + 'chat/confirm-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ step: 'cancel' })
            }).catch(() => {});
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

        // Auto-resize textarea
        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() { autoResizeTextarea(this); });

        function autoResizeTextarea(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        }

        // =====================================================================
        // Schema panel
        // =====================================================================
        let schemaLoaded = false;

        function toggleSchema() {
            const panel = document.getElementById('schemaPanel');
            const overlay = document.getElementById('schemaOverlay');
            const isOpen = panel.classList.contains('open');

            panel.classList.toggle('open');
            overlay.classList.toggle('open');

            if (!isOpen && !schemaLoaded) {
                loadSchema();
            }
        }

        function loadSchema() {
            fetch(BASE_URL + 'chat/schema')
            .then(r => r.json())
            .then(data => {
                schemaLoaded = true;
                const body = document.getElementById('schemaPanelBody');

                if (!data.success) {
                    body.innerHTML = '<div class="text-danger p-3">Error: ' + data.error + '</div>';
                    return;
                }

                let html = '<div style="margin-bottom:10px;"><input type="text" id="schemaSearch" class="form-control form-control-sm" placeholder="Buscar tabla..." oninput="filterSchema(this.value)"></div>';
                html += '<div id="schemaList">';
                data.tables.forEach(t => {
                    html += `<div class="schema-table-item" data-name="${t.name}" onclick="insertTableName('${t.name}')">
                        <span class="table-name">${t.name}</span>
                        <span class="row-count">${t.rows} filas</span>
                    </div>`;
                });
                html += '</div>';
                body.innerHTML = html;
            })
            .catch(err => {
                document.getElementById('schemaPanelBody').innerHTML =
                    '<div class="text-danger p-3">Error: ' + err.message + '</div>';
            });
        }

        function filterSchema(query) {
            const items = document.querySelectorAll('.schema-table-item');
            const q = query.toLowerCase();
            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                item.style.display = name.includes(q) ? '' : 'none';
            });
        }

        function insertTableName(name) {
            const input = document.getElementById('messageInput');
            input.value += (input.value ? ' ' : '') + name;
            input.focus();
        }

        // Focus en el input al cargar
        document.getElementById('messageInput').focus();

        // =====================================================================
        // INACTIVIDAD: enviar resumen por email al consultor tras 10 min sin uso
        // =====================================================================
        const INACTIVITY_MS  = 10 * 60 * 1000; // 10 minutos
        let inactivityTimer  = null;
        let sessionEmailSent = false;

        function sendSessionEmail() {
            if (sessionEmailSent || conversationHistory.length === 0) return;
            sessionEmailSent = true;
            const payload = JSON.stringify({ history: conversationHistory });
            if (navigator.sendBeacon) {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(BASE_URL + 'chat/end-session', blob);
            } else {
                fetch(BASE_URL + 'chat/end-session', {
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

        async function finalizarConversacion() {
            if (conversationHistory.length === 0) {
                window.location.href = BASE_URL + 'consultant/dashboard';
                return;
            }
            const { isConfirmed } = await Swal.fire({
                title: '¿Finalizar conversación?',
                text: 'Se enviará el resumen de esta sesión a tu correo.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar y enviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#c0392b',
            });
            if (!isConfirmed) return;

            sendSessionEmail();
            await Swal.fire({
                title: '¡Listo!',
                text: 'Resumen enviado a tu correo.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
            });
            window.location.href = BASE_URL + 'consultant/dashboard';
        }

        // =====================================================================
        // PWA: Service Worker
        // =====================================================================
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= base_url("sw_chat.js") ?>', { scope: '<?= base_url() ?>' })
                    .then(function(reg) {
                        console.log('SW Chat registrado:', reg.scope);
                    })
                    .catch(function(err) {
                        console.log('SW Chat error:', err);
                    });
            });
        }
    </script>
</body>

</html>
