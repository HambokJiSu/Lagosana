<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Blog Content Generator</title>
    <link href="css/lagosana.css?v=1.0.1" rel="stylesheet">
</head>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer = json_decode($_POST['customer'] ?? '[]', true);

    if (empty($customer['group_name']) || $customer['group_name'] !== "VIP") {
        // VIP가 아닌 경우, 접근 제한 메시지를 출력하고 종료
        die("<script>alert('비정상적인 접근입니다.'); window.location.href='https://lagosana.com';</script>");
    }
} else {
    // POST 요청이 아닌 경우, 접근 제한 메시지를 출력하고 종료
    die("<script>alert('비정상적인 접근입니다.'); window.location.href='https://lagosana.com';</script>");
}
?>
<body>
    <div class="loading-indicator" id="loadingIndicator">
        <div class="loading-text"></div>
    </div>
    
    <div class="header-container">
        <div class="menu-title">Blog Content Generator</div>
        <div class="controls">
            <button class="btn btn-dark" onclick="startNewSession()">새 질문 시작</button>
        </div>
    </div>

    <div class="sns-selector" id="snsSelector">
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="sns" id="btnBlog" value="Blog" checked>
            <label class="btn btn-outline-primary" for="btnBlog">Blog</label>

            <input type="radio" class="btn-check" name="sns" id="btnThreads" value="Threads">
            <label class="btn btn-outline-primary" for="btnThreads">Threads</label>

            <input type="radio" class="btn-check" name="sns" id="btnInstagram" value="Instagram">
            <label class="btn btn-outline-primary" for="btnInstagram">Instagram</label>
            
            <input type="radio" class="btn-check" name="sns" id="btnFacebook" value="Facebook">
            <label class="btn btn-outline-primary" for="btnFacebook">Facebook</label>
        </div>
        <button class="btn btn-primary w-100 mt-3" onclick="startChat()">질문 시작</button>
    </div>
    <div class="chat-container" style="display: flex;">
        <div class="chat-set">
            <div class="response-wrapper">
                <button type="button" class="copy-button" onclick="copyToClipboard(this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span>복사</span>
                </button>
                <div class="response-container" id="initialResponseText"></div>
            </div>
        </div>
    </div>
    <div class="chat-container" id="chatContainer">
    </div>

    <script>
        // const params = new URLSearchParams(window.location.search);
        // const runEnv = params.get("runEnv");

        // let ws;
        // if (runEnv == 'local') {
        //     ws = new WebSocket('ws://localhost:8088/chat');
        // } else {
        //     ws = new WebSocket('wss://ai.lagosana.com:8088/chat');
        // }

        // let ws = new WebSocket('wss://ai.lagosana.com:8088/chat');
        let ws = new WebSocket('ws://127.0.0.1:8088/chat');

        let currentResponseArea = null;
        let selectedSNS = null;
        let isLoading = false;
        let loadingInterval;

        function showLoading() {
            isLoading = true;
            
            const loadingText = document.querySelector('.loading-text');
            let dotCount = 0;
            
            if (ws.readyState === WebSocket.OPEN) {
                document.getElementById('loadingIndicator').style.display = 'flex';
                loadingInterval = setInterval(() => {
                    dotCount = (dotCount + 1) % 6;
                    loadingText.textContent = '답변을 생성하는 중입니다' + '.'.repeat(dotCount);
                }, 500);
            }
        }

        function hideLoading() {
            document.getElementById('loadingIndicator').style.display = 'none';
            isLoading = false;
            clearInterval(loadingInterval);
        }

        function startChat() {
            const snsOptions = document.getElementsByName('sns');
            for (const option of snsOptions) {
                if (option.checked) {
                    selectedSNS = option.value;
                    break;
                }
            }

            if (!selectedSNS) {
                alert('SNS를 선택해주세요.');
                return;
            }

            document.getElementById('chatContainer').innerHTML = '';
            
            currentResponseArea = document.getElementById('initialResponseText');
            
            snsOptions.forEach(option => {
                option.disabled = true;
            });

            document.querySelector('.btn-primary').disabled = true;
            
            showLoading();
            ws.send(selectedSNS);
        }

        ws.onopen = function(event) {
            console.log('WebSocket 연결됨');
        };

        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            
            if (data.type === 'token') {
                hideLoading();
                if (currentResponseArea) {
                    currentResponseArea.textContent += data.message;
                    currentResponseArea.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            } else if (data.type === 'end') {
                hideLoading();
                if (currentResponseArea && currentResponseArea.textContent.trim()) {
                    const copyButton = currentResponseArea.previousElementSibling;
                    copyButton.style.display = 'flex';
                }
                if (!document.getElementById('chatContainer').style.display) {
                    document.getElementById('chatContainer').style.display = 'flex';
                    addNewChatSet();
                } else {
                    addNewChatSet();
                }
            } else if (data.type === 'error') {
                hideLoading();
                alert(data.message);
            }
        };

        function handleKeyPress(event, element) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                
                if (isLoading) return;
                
                const question = element.textContent.trim();
                if (question) {
                    element.contentEditable = false;
                    currentResponseArea = element.closest('.chat-set').querySelector('.response-container');
                    showLoading();
                    ws.send(question);
                }
            }
        }

        function addNewChatSet() {
            const chatSet = document.createElement('div');
            chatSet.className = 'chat-set';
            chatSet.innerHTML = `
                <div class="question-container">
                    <div class="user-avatar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="11" stroke="currentColor" stroke-width="2"/>
                            <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="2"/>
                            <path d="M19 20C19 16.134 15.866 13 12 13C8.13401 13 5 16.134 5 20" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="question-content" contenteditable="true" placeholder="질문을 입력하고 엔터를 누르세요..." onkeypress="handleKeyPress(event, this)"></div>
                </div>
                <div class="response-wrapper">
                    <button type="button" class="copy-button" onclick="copyToClipboard(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>복사</span>
                    </button>
                    <div class="response-container"></div>
                </div>
            `;
            document.getElementById('chatContainer').appendChild(chatSet);
            chatSet.querySelector('.question-content').focus();
        }

        function copyToClipboard(button) {
            const responseContainer = button.nextElementSibling;
            const textToCopy = responseContainer.textContent;
            
            navigator.clipboard.writeText(textToCopy).then(() => {
                const span = button.querySelector('span');
                const originalText = span.textContent;
                span.textContent = '복사됨';
                
                setTimeout(() => {
                    span.textContent = originalText;
                }, 1500);
            });
        }

        function startNewSession() {
            location.reload();
        }

        window.onbeforeunload = function() {
            ws.close();
        };
    </script>
</body>
</html> 