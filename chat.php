<?php
function loadEnv() {
    // Simplified or removed for rudimentary version
}

function initChat() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
    }
    return [
        'apiKey' => getenv('GEMINI_API_KEY'),
        'systemPrompt' => 'You are a helpful assistant.'
    ];
}

function handleChatRequest() {
    $config = initChat();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $input = json_decode(file_get_contents('php://input'), true);
        $userText = trim($input['prompt'] ?? '');
        if ($userText === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Empty prompt']);
            exit;
        }
        $_SESSION['chat_history'][] = ['role' => 'user', 'text' => $userText];

        // Dummy bot response for rudimentary version
        $replyText = "This is a placeholder response to: " . htmlspecialchars($userText);

        $_SESSION['chat_history'][] = ['role' => 'bot', 'text' => $replyText];
        echo json_encode(['reply' => $replyText]);
        exit;
    }
}

function resetChat() {
    initChat();
    if (isset($_GET['reset_chat'])) {
        $_SESSION['chat_history'] = [];
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action']) && $input['action'] === 'reset_chat') {
            $_SESSION['chat_history'] = [];
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

function getChatUI() {
    initChat();
    $chatButtonHtml = '<div id="chat-button" style="position:fixed; bottom:20px; right:20px; width:50px; height:50px; background:blue; color:white; border-radius:50%; text-align:center; line-height:50px; cursor:pointer;">Chat</div>';
    $chatContainerHtml = '<div id="chat-container" style="display:none; position:fixed; bottom:80px; right:20px; width:300px; height:400px; border:1px solid #ccc; background:white; flex-direction:column;">';
    $chatContainerHtml .= '<div id="chat-messages" style="flex-grow:1; overflow-y:auto; padding:10px;">';

    if (!empty($_SESSION['chat_history'])) {
        foreach ($_SESSION['chat_history'] as $msg) {
            $role = htmlspecialchars($msg['role']);
            $text = htmlspecialchars($msg['text']);
            $chatContainerHtml .= "<div><strong>$role:</strong> $text</div>";
        }
    } else {
        $chatContainerHtml .= '<div>Ask me anything!</div>';
    }

    $chatContainerHtml .= '</div>';
    $chatContainerHtml .= '<form id="chat-form" style="padding:10px;"><input type="text" id="chat-input" style="width:calc(100% - 70px);"><button type="submit">Send</button></form>';
    $chatContainerHtml .= '</div>';
    
    return $chatButtonHtml . $chatContainerHtml;
}

function getChatStyles() {
    return '<style>/* Minimal styles are inline in getChatUI */</style>';
}

function getChatScript() {
    return '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chatButton = document.getElementById("chat-button");
            const chatContainer = document.getElementById("chat-container");
            const chatForm = document.getElementById("chat-form");
            const chatInput = document.getElementById("chat-input");
            const chatMessages = document.getElementById("chat-messages");

            chatButton.addEventListener("click", () => {
                chatContainer.style.display = chatContainer.style.display === "none" ? "flex" : "none";
            });

            chatForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                const text = chatInput.value.trim();
                if (!text) return;
                chatInput.value = "";
                
                let div = document.createElement("div");
                div.innerHTML = "<strong>user:</strong> " + text;
                chatMessages.appendChild(div);

                const response = await fetch(window.location.pathname, {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
                    body: JSON.stringify({ prompt: text })
                });
                const data = await response.json();

                let botDiv = document.createElement("div");
                botDiv.innerHTML = "<strong>bot:</strong> " + data.reply;
                chatMessages.appendChild(botDiv);
            });
        });
    </script>';
}

function setupChat() {
    resetChat();
    handleChatRequest();
    return [
        'ui' => getChatUI(),
        'styles' => getChatStyles(),
        'script' => getChatScript()
    ];
}
?>