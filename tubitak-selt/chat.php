<?php
/**
 * Board Game Generator - AI Assistant Module
 * 
 * This file provides a reusable chat interface using Gemini AI.
 * It handles:
 * 1. Loading API key and system prompt from .env
 * 2. Managing chat session history
 * 3. Processing AJAX requests to the Gemini API
 * 4. Rendering chat UI components
 */

// Load environment variables from .env file
function loadEnv() {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos($line, '#') === 0) continue;
            
            // Parse key=value, but skip invalid lines without equals sign
            if (strpos($line, '=') === false) continue;
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }
            
            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Initialize chat session
function initChat() {
    // Load environment variables
    loadEnv();
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get API key and system prompt from environment without fallbacks
    $apiKey = getenv('GEMINI_API_KEY');
    $systemPrompt = getenv('SYSTEM_PROMPT');
    
    // Check if values exist
    if (!$apiKey) {
        error_log('Error: GEMINI_API_KEY not found in .env file');
    }
    
    if (!$systemPrompt) {
        error_log('Error: SYSTEM_PROMPT not found in .env file');
        $systemPrompt = 'You are SELT Board Game Generator Assistant';
    }
    
    // Initialize chat history if not exists
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
    }
    
    return [
        'apiKey' => $apiKey,
        'systemPrompt' => $systemPrompt
    ];
}

// Handle AJAX requests for chat
function handleChatRequest() {
    $config = initChat();
    
    // Process only AJAX POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

        $input = json_decode(file_get_contents('php://input'), true);
        $userText = trim($input['prompt'] ?? '');
        if ($userText === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Empty prompt']);
            exit;
        }

        // Save user message in history
        $_SESSION['chat_history'][] = ['role' => 'user', 'text' => $userText];

        // Build parts with system prompt first, then conversation
        $parts = [];
        $parts[] = ['text' => "SYSTEM: {$config['systemPrompt']}\n"]; 
        foreach ($_SESSION['chat_history'] as $msg) {
            $prefix = strtoupper($msg['role']) . ':';
            $parts[] = ['text' => "{$prefix} {$msg['text']}\n"];
        }

        // Call Gemini API
        $payload = ['contents' => [[ 'parts' => $parts ]]];
        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$config['apiKey']}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        // Parse response
        $replyText = 'No response';
        $data = json_decode($result, true);
        if (!empty($data['candidates'][0])) {
            $cand = $data['candidates'][0];
            $partsOut = $cand['content']['parts'] ?? $cand['parts'] ?? [];
            $texts = array_map(fn($p) => $p['text'] ?? '', $partsOut);
            $replyText = implode('', $texts);
        }

        // Save bot response
        $_SESSION['chat_history'][] = ['role' => 'bot', 'text' => $replyText];

        // Return JSON
        echo json_encode(['reply' => $replyText]);
        exit;
    }
}

// Reset chat history
function resetChat() {
    initChat();
    
    // Handle GET parameter for backward compatibility
    if (isset($_GET['reset_chat']) && $_GET['reset_chat'] === 'true') {
        $_SESSION['chat_history'] = [];
        
        // Redirect to remove the query parameter
        $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: $redirectUrl");
        exit;
    }
    
    // Handle AJAX requests for chat reset
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        
        // Read JSON data from request body
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if this is a chat reset request
        if (isset($input['action']) && $input['action'] === 'reset_chat') {
            // Only clear chat history, preserving other session data
            $_SESSION['chat_history'] = [];
            
            // Return success response
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

// Render chat UI components
function getChatUI() {
    initChat();
    
    // Chat button HTML
    $chatButtonHtml = '
    <div id="chat-button" class="chat-button">
        <i class="fas fa-comments"></i>
    </div>';
    
    // Chat container HTML
    $chatContainerHtml = '
    <div id="chat-container" class="chat-container">
        <div class="chat-header">
            <div class="chat-title">
                <i class="fas fa-robot"></i>
                <span>SELT Board Game Assistant</span>
            </div>
            <div class="chat-actions">
                <button id="chat-clear" class="chat-clear">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button id="chat-close" class="chat-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div id="chat-messages" class="chat-messages">';
    
    // If chat history exists, add messages
    if (isset($_SESSION['chat_history']) && !empty($_SESSION['chat_history'])) {
        foreach ($_SESSION['chat_history'] as $msg) {
            $role = htmlspecialchars($msg['role']);
            $text = parseMarkdown($msg['text']);
            $icon = $role === 'bot' ? 'fa-robot' : 'fa-user';
            
            $chatContainerHtml .= "
            <div class=\"chat-message $role\">
                <div class=\"chat-avatar\">
                    <i class=\"fas $icon\"></i>
                </div>
                <div class=\"chat-content\">$text</div>
            </div>";
        }
    } else {
        // Empty state
        $chatContainerHtml .= '
        <div class="chat-empty-state">
            <i class="fas fa-comments"></i>
            <h3>Start a conversation</h3>
            <p>Ask me anything about board games or how I can help you generate board game ideas!</p>
        </div>';
    }
    
    // Close messages div and add input area
    $chatContainerHtml .= '
        </div>
        <div class="chat-input-area">
            <form id="chat-form">
                <div class="chat-input-container">
                    <input type="text" id="chat-input" class="chat-input" placeholder="Type your message..." autocomplete="off">
                    <button type="submit" id="chat-send" class="chat-send" disabled>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>';
    
    return $chatButtonHtml . $chatContainerHtml;
}

// Include CSS styles for chat
function getChatStyles() {
    return '
    <style>
        /* Chat Button */
        .chat-button {
            position: fixed;
            bottom: 33px;
            right: 33px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .chat-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
        }
        
        .chat-button i {
            font-size: 24px;
        }
        
        /* Chat Container */
        .chat-container {
            display: none;
            position: fixed;
            top: 90px;
            right: 30px;
            width: 550px;
            height: calc(100vh - 120px);
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15), 0 0 0 3px var(--primary-color), 0 0 0 6px rgba(92, 51, 162, 0.3);
            z-index: 999;
            overflow: hidden;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        
        .chat-container.active {
            display: flex;
            animation: slidein 0.3s forwards;
        }
        
        @keyframes slidein {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Chat Header */
        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }
        
        .chat-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        .chat-title i {
            font-size: 20px;
        }
        
        .chat-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chat-close, .chat-clear {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
        }
        
        .chat-close:hover, .chat-clear:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Chat Messages */
        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .chat-message {
            display: flex;
            gap: 12px;
            max-width: 85%;
        }
        
        .chat-message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        
        .chat-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .chat-message.user .chat-avatar {
            background-color: #64748b;
        }
        
        .chat-content {
            padding: 12px 16px;
            border-radius: 12px;
            background-color: #f8fafc;
            border: 1px solid var(--divider-color);
            line-height: 1.5;
        }
        
        .chat-message.bot .chat-content {
            border-top-left-radius: 4px;
        }
        
        .chat-message.user .chat-content {
            background-color: #eff6ff;
            border-top-right-radius: 4px;
        }
        
        /* Empty State */
        .chat-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px;
            text-align: center;
            color: var(--text-muted);
        }
        
        .chat-empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: var(--primary-color);
        }
        
        .chat-empty-state h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--text-dark);
            position: relative;
        }
        
        .chat-empty-state h3:after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 180px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .chat-empty-state p {
            font-size: 14px;
            max-width: 80%;
        }
        
        /* Chat Input Area */
        .chat-input-area {
            padding: 16px;
            border-top: 1px solid var(--divider-color);
        }
        
        .chat-input-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chat-input {
            flex-grow: 1;
            padding: 12px 16px;
            border: 1px solid var(--divider-color);
            border-radius: 24px;
            outline: none;
            font-family: "Montserrat", sans-serif;
            transition: border-color 0.2s ease;
        }
        
        .chat-input:focus {
            border-color: var(--primary-color);
        }
        
        .chat-send {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .chat-send:disabled {
            background: #e2e8f0;
            cursor: not-allowed;
        }
        
        .chat-send:not(:disabled):hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Typing Indicator */
        .chat-typing {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 12px 16px;
            border-radius: 12px;
            border-top-left-radius: 4px;
            background-color: #f8fafc;
            border: 1px solid var(--divider-color);
            width: fit-content;
        }
        
        .chat-typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--text-muted);
            animation: typing-dot 1.4s infinite ease-in-out both;
        }
        
        .chat-typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .chat-typing-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typing-dot {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .chat-container {
                width: 95%;
                right: 2.5%;
                height: 70vh;
            }
            
            .chat-button {
                bottom: 20px;
                right: 20px;
            }
        }
    </style>';
}

// Include JavaScript for chat functionality
function getChatScript() {
    return '
    <!-- Include marked.js for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked@10.0.0/marked.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Elements
            const chatButton = document.getElementById("chat-button");
            const chatContainer = document.getElementById("chat-container");
            const chatClose = document.getElementById("chat-close");
            const chatClear = document.getElementById("chat-clear");
            const chatForm = document.getElementById("chat-form");
            const chatInput = document.getElementById("chat-input");
            const chatSend = document.getElementById("chat-send");
            const chatMessages = document.getElementById("chat-messages");
            
            // Configure marked.js
            marked.use({
                breaks: true, // Enable line breaks
                gfm: true     // Enable GitHub Flavored Markdown
            });
            
            // Toggle chat visibility
            chatButton.addEventListener("click", function() {
                chatContainer.classList.add("active");
                chatInput.focus();
            });
            
            // Close chat
            chatClose.addEventListener("click", function() {
                chatContainer.classList.remove("active");
            });
            
            // Clear chat with confirmation
            chatClear.addEventListener("click", function() {
                if (confirm("Are you sure you want to clear the chat history?")) {
                    // Use AJAX to clear chat without page reload
                    fetch(window.location.pathname, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: JSON.stringify({ action: "reset_chat" })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Clear chat messages in the UI
                            chatMessages.innerHTML = `
                                <div class="chat-empty-state">
                                    <i class="fas fa-comments"></i>
                                    <h3>Start a conversation</h3>
                                    <p>Ask me anything about board games or how I can help you generate board game ideas!</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error("Error clearing chat:", error);
                    });
                }
            });
            
            // Enable/disable send button based on input
            chatInput.addEventListener("input", function() {
                chatSend.disabled = !this.value.trim();
            });
            
            // Handle form submission
            chatForm.addEventListener("submit", async function(e) {
                e.preventDefault();
                
                const text = chatInput.value.trim();
                if (!text) return;
                
                // Clear input
                chatInput.value = "";
                chatSend.disabled = true;
                
                // Remove empty state if present
                const emptyState = chatMessages.querySelector(".chat-empty-state");
                if (emptyState) {
                    chatMessages.removeChild(emptyState);
                }
                
                // Add user message
                const userMessage = createMessageElement("user", text);
                chatMessages.appendChild(userMessage);
                scrollToBottom();
                
                // Add typing indicator
                const typingIndicator = document.createElement("div");
                typingIndicator.className = "chat-message bot";
                typingIndicator.innerHTML = `
                    <div class="chat-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="chat-typing">
                        <div class="chat-typing-dot"></div>
                        <div class="chat-typing-dot"></div>
                        <div class="chat-typing-dot"></div>
                    </div>
                `;
                chatMessages.appendChild(typingIndicator);
                scrollToBottom();
                
                try {
                    // Send request to server
                    const response = await fetch(window.location.pathname, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: JSON.stringify({ prompt: text })
                    });
                    
                    const data = await response.json();
                    
                    // Remove typing indicator
                    chatMessages.removeChild(typingIndicator);
                    
                    // Add bot message
                    const botMessage = createMessageElement("bot", data.reply || "Sorry, I encountered an error.");
                    chatMessages.appendChild(botMessage);
                    scrollToBottom();
                    
                } catch (error) {
                    console.error("Error:", error);
                    
                    // Remove typing indicator
                    chatMessages.removeChild(typingIndicator);
                    
                    // Add error message
                    const errorMessage = createMessageElement("bot", "Sorry, I encountered an error. Please try again.");
                    chatMessages.appendChild(errorMessage);
                    scrollToBottom();
                }
            });
            
            // Create message element
            function createMessageElement(role, text) {
                const messageDiv = document.createElement("div");
                messageDiv.className = `chat-message ${role}`;
                
                const avatar = document.createElement("div");
                avatar.className = "chat-avatar";
                avatar.innerHTML = role === "bot" ? 
                    \'<i class="fas fa-robot"></i>\' : 
                    \'<i class="fas fa-user"></i>\';
                
                const content = document.createElement("div");
                content.className = "chat-content";
                
                // Parse Markdown to HTML using marked.js
                if (role === "bot") {
                    content.innerHTML = marked.parse(text);
                } else {
                    // Don\'t parse Markdown for user messages
                    content.innerHTML = text;
                }
                
                messageDiv.appendChild(avatar);
                messageDiv.appendChild(content);
                
                return messageDiv;
            }
            
            // Scroll to bottom of chat
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    </script>';
}

// Parse simple Markdown syntax to HTML
function parseMarkdown($text) {
    // Convert Markdown to HTML
    $html = htmlspecialchars($text);
    
    // Parse bold text: **text** to <strong>text</strong>
    $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html);
    
    // Parse italic text: *text* to <em>text</em> (but not if it's part of **)
    $html = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $html);
    
    // Parse inline code: `code` to <code>code</code>
    $html = preg_replace('/`(.*?)`/s', '<code>$1</code>', $html);
    
    // Convert line breaks to <br>
    $html = nl2br($html);
    
    return $html;
}

// Main function to handle all chat functionality
function setupChat() {
    // Process GET request for resetting chat
    resetChat();
    
    // Process AJAX POST request for chat
    handleChatRequest();
    
    // Return all HTML, CSS, and JS for chat
    return [
        'ui' => getChatUI(),
        'styles' => getChatStyles(),
        'script' => getChatScript()
    ];
}
