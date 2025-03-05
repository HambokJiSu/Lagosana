<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Blog Content Generator</title>
    <link href="css/lagosana.css?v=1.0.2" rel="stylesheet">
</head>
<?php
$config = parse_ini_file('../lagosana_conf.ini', true);
$blogChatApiUrl = $config['FRONT']['blogChatApiUrl'];

if ($config['SERVER']['runEnv'] != "local") {
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
} else {
    // 로컬 환경에서는 테스트를 위해 하드코딩
    $customer = [
        'member_id' => 'test_chan',
        'group_name' => 'VIP'
    ];
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
    
    <div class="chat-container" id="chatContainer"></div>

    <script>
        const cust = "<?php echo $customer ?? ''; ?>";
        console.log(cust);

        const _LOADING_MSGS = [
            "🌟 당신의 고객이 더 빛날 수 있도록, 라고사나 AI 솔루션이 고민하는 중!"
            ,"🥰 고객님의 샵에 꼭 맞는 포스팅을 준비하고 있어요."
            ,"💆‍♂ `피부는 기다림을 배신하지 않죠` AI도 최선을 다하는 중입니다!"
            ,"🛁 피부처럼 촉촉한 답변을 준비하는 중!"
            ,"💎 원장님만을 위한 뷰티 인사이트를 만드는 중이에요!"
            ,"🔮라고사나 AI 솔루션이 최적의 답을 찾아내는 중!"
        ];

        // const _API_URL = "https://ai.lagosana.com:8088/chat"; // FastAPI 서버의 URL
        const _API_URL = "<?php echo $blogChatApiUrl; ?>"; // FastAPI 서버의 URL
        let _THREAD_ID = null; // thread_id를 저장할 변수

        let currentResponseArea = null;
        let selectedSNS = null;
        let isLoading = false;

        function showLoading() {
            isLoading = true;
            const loadingText = document.querySelector('.loading-text');
            let msgIdx = 0;
            document.getElementById('loadingIndicator').style.display = 'flex';
            loadingText.textContent = _LOADING_MSGS[msgIdx];
            loadingInterval = setInterval(() => {
                msgIdx = msgIdx >= _LOADING_MSGS.length - 1 ? 0 : msgIdx + 1;
                loadingText.textContent = _LOADING_MSGS[msgIdx];
            }, 3000);
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

            // 질문 시작 시 로딩 표시
            showLoading();

            // AJAX 요청을 통해 API 호출
            fn_CallAPI(selectedSNS);
        }

        function fn_CallAPI(pQuestion) {
            const member_id = "<?php echo $customer['member_id'] ?? ''; ?>";
            
            const requestData = {
                member_id: member_id,
                message: pQuestion,
                thread_id: _THREAD_ID // 이전 질문의 thread_id를 전달
            };

            fetch(_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData),
                mode: 'cors' // CORS 요청을 명확히 지정
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // 응답 처리
                _THREAD_ID = data.thread_id; // 새로운 thread_id 저장
                currentResponseArea.textContent = data.response; // 응답 내용 표시
                hideLoading(); // 로딩 종료

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
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading(); // 로딩 종료
                alert('오류가 발생했습니다. 다시 시도해주세요.');
            });
        }

        function handleKeyPress(event, element) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                
                if (isLoading) return;
                
                const question = element.textContent.trim();
                if (question) {
                    element.contentEditable = false;
                    currentResponseArea = element.closest('.chat-set').querySelector('.response-container');
                    showLoading();
                    fn_CallAPI(question);
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

        function startNewSession() {
            location.reload(); // 새 질문 시작 시 페이지 새로고침
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
    </script>
</body>
</html> 