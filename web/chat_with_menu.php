<!DOCTYPE html>
<html lang="ko">
<?php
session_start();

if (empty($_SESSION['lagosana_group_name']) || $_SESSION['lagosana_group_name'] !== "VIP") {
    die("<script>alert('비정상적인 접근입니다.'); window.location.href='https://lagosana.com';</script>");
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LagoSana 챗봇</title>
    <link href="css/chat_with_menu.css?v=1.0.2" rel="stylesheet">
</head>
<body>
    <!-- 왼쪽 사이드바 (대화 기록) -->
    <div class="sidebar-container">
        <div class="brand">
            <!-- <img src="/api/placeholder/130/40" alt="LagoSana 로고"> -->
            <img src="https://ecimg.cafe24img.com/pg1028b12001162094/platform66/web/upload/category/editor/2024/06/27/27b3562f49cec05d23bec700ae7d64e7.png" alt="LagoSana 로고">
        </div>
        <div class="sidebar">
            <div class="history-title">대화 기록</div>
            <ul class="chat-history">
                <!-- 채팅 기록은 JavaScript로 동적 로딩됩니다 -->
            </ul>
        </div>
    </div>
    
    <!-- 중앙 채팅 영역 -->
    <div class="chat-container">
        <!-- 상단 메뉴 -->
        <div class="chat-tabs">
            <div class="tab active">전문 마케터</div>
            <div class="tab">리뷰 마법사</div>
            <div class="tab">라고사나 Q&A</div>
            <div class="tab">이벤트 플래너</div>
            <div class="tab">패키징 마법사</div>
            <div class="tab">Daily SNS</div>
        </div>
        
        <!-- 채팅 메시지 영역 -->
        <div class="chat-messages">
            
        </div>
        
        <!-- 도구 버튼 -->
        <div class="tool-buttons">
            <button class="tool-button">복사하기</button>
            <button class="tool-button">다운로드</button>
        </div>
        
        <!-- 채팅 입력 영역 -->
        <div class="chat-input-container">
            <div class="chat-input-wrap">
                <textarea class="chat-input" placeholder="메시지를 입력하세요..." rows="1"></textarea>
                <button class="send-button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        // 전역 변수로 현재 thread_id 저장
        let currentThreadId = null;
        
        // 현재 선택된 탭의 chatbot_tp_cd 값 저장
        let currentChatbotType = '01'; // 기본값은 '전문 마케터'
        
        // 채팅 기록을 가져오는 함수
        async function loadChatHistory() {
            try {
                const response = await fetch('data/ajax/get_ajax.php?tp=chatThread&chatbotTp=' + currentChatbotType, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success && data.chat_history && data.chat_history.length > 0) {
                    const chatHistoryList = document.querySelector('.chat-history');
                    chatHistoryList.innerHTML = ''; // 기존 목록 초기화
                    
                    // 첫 번째 항목은 활성 상태로 표시
                    let firstItem = true;
                    
                    data.chat_history.forEach(item => {
                        const li = document.createElement('li');
                        li.className = firstItem ? 'active' : '';
                        li.setAttribute('data-thread-id', item.thread_id);
                        
                        li.innerHTML = `
                            <div class="history-item-title">${item.contents}</div>
                            <div class="history-item-date">${item.create_dtm}</div>
                        `;
                        
                        chatHistoryList.appendChild(li);
                        firstItem = false;
                    });
                    
                    // 이벤트 리스너 다시 추가
                    attachChatHistoryEventListeners();
                } else {
                    // 채팅 기록이 없는 경우 기본 항목 표시
                    const chatHistoryList = document.querySelector('.chat-history');
                    chatHistoryList.innerHTML = `
                        <li class="active" data-thread-id="default">
                            <div class="history-item-title">새로운 대화 시작하기</div>
                            <div class="history-item-date">지금</div>
                        </li>
                    `;
                    
                    // 이벤트 리스너 추가
                    attachChatHistoryEventListeners();
                }
            } catch (error) {
                console.error('채팅 기록 로딩 중 오류 발생:', error);
            }
        }
        
        // 채팅 기록 항목에 이벤트 리스너 추가하는 함수
        function attachChatHistoryEventListeners() {
            document.querySelectorAll('.chat-history li').forEach(item => {
                item.addEventListener('click', function() {
                    // 활성 클래스 제거
                    document.querySelectorAll('.chat-history li').forEach(i => i.classList.remove('active'));
                    
                    // 클릭한 항목에 활성 클래스 추가
                    this.classList.add('active');
                    
                    // thread_id 가져오기
                    const threadId = this.getAttribute('data-thread-id');
                    
                    // 현재 thread_id 설정
                    currentThreadId = threadId !== 'default' ? threadId : null;
                    
                    // 채팅 메시지 영역 초기화
                    const chatMessages = document.querySelector('.chat-messages');
                    chatMessages.innerHTML = '';
                    
                    // 로딩 메시지 표시
                    const loadingMessage = document.createElement('div');
                    loadingMessage.className = 'message message-bot';
                    loadingMessage.innerHTML = `
                        <div class="message-content">
                            대화 내용을 불러오는 중입니다...
                        </div>
                        <div class="message-time">${getCurrentTime()}</div>
                    `;
                    chatMessages.appendChild(loadingMessage);
                    
                    // API 호출하여 채팅 이력 가져오기
                    fetch(`data/ajax/get_ajax.php?tp=chatThreadDetail&thread_id=${threadId}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        // 로딩 메시지 제거
                        chatMessages.removeChild(loadingMessage);
                        
                        if (data.success && data.chat_thread_history && data.chat_thread_history.length > 0) {
                            // 채팅 이력 표시
                            data.chat_thread_history.forEach(chat => {
                                const messageElement = document.createElement('div');
                                messageElement.className = `message message-${chat.req_res === 'REQ' ? 'user' : 'bot'}`;
                                messageElement.innerHTML = `
                                    <div class="message-content">${chat.contents.replace(/\n/g, '<br>')}</div>
                                    <div class="message-time">${chat.create_dtm}</div>
                                `;
                                chatMessages.appendChild(messageElement);
                            });
                        } else {
                            // 채팅 이력이 없는 경우 기본 메시지 표시
                            const defaultMessage = document.createElement('div');
                            defaultMessage.className = 'message message-bot';
                            defaultMessage.innerHTML = `
                                <div class="message-content">
                                    안녕하세요! 라고사나 전문 마케터입니다. 어떤 SNS 플랫폼에서 홍보하실 건가요?
                                </div>
                                <div class="message-time">${getCurrentTime()}</div>
                            `;
                            chatMessages.appendChild(defaultMessage);
                        }
                        
                        // 스크롤을 맨 아래로
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    })
                    .catch(error => {
                        console.error('채팅 이력 로딩 중 오류 발생:', error);
                        chatMessages.removeChild(loadingMessage);
                        
                        // 오류 메시지 표시
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'message message-bot';
                        errorMessage.innerHTML = `
                            <div class="message-content">
                                채팅 이력을 불러오는 중 오류가 발생했습니다.
                            </div>
                            <div class="message-time">${getCurrentTime()}</div>
                        `;
                        chatMessages.appendChild(errorMessage);
                    });
                });
            });
        }
        
        // 페이지 로드 시 채팅 기록 로드
        document.addEventListener('DOMContentLoaded', function() {
            loadChatHistory();
        });
        
        // Enter 키로 메시지 전송 시에도 채팅 기록 갱신
        document.querySelector('.chat-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                
                // 메시지 전송 함수 호출
                sendMessage();
                
                // 메시지 전송 후 채팅 기록 갱신
                setTimeout(loadChatHistory, 1000); // 1초 후 갱신 (서버 처리 시간 고려)
            }
        });
        
        // 메시지 전송 버튼 클릭 이벤트
        document.querySelector('.send-button').addEventListener('click', function() {
            // 메시지 전송 함수 호출
            sendMessage();
            
            // 메시지 전송 후 채팅 기록 갱신
            setTimeout(loadChatHistory, 1000); // 1초 후 갱신 (서버 처리 시간 고려)
        });
        
        // 메시지 전송 함수
        function sendMessage() {
            const input = document.querySelector('.chat-input');
            let message = input.value.trim();
            
            if (message) {
                const chatMessages = document.querySelector('.chat-messages');
                
                // 사용자 메시지 추가
                const userMessageElement = document.createElement('div');
                userMessageElement.className = 'message message-user';
                userMessageElement.innerHTML = `
                    <div class="message-content">
                        ${formatMessageContent(message)}
                    </div>
                    <div class="message-time">${getCurrentTime()}</div>
                `;
                
                chatMessages.appendChild(userMessageElement);
                
                // 입력창 비우기 및 높이 초기화
                input.value = '';
                input.style.height = 'auto';
                
                // 스크롤을 맨 아래로
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // 로딩 메시지 표시
                const loadingMessageElement = document.createElement('div');
                loadingMessageElement.className = 'message message-bot';
                loadingMessageElement.innerHTML = `
                    <div class="message-content">
                        <div class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="message-time">${getCurrentTime()}</div>
                `;
                chatMessages.appendChild(loadingMessageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight;

                // Daily SNS 챗봇이며 새대화 시작인 경우 사업장 특징 정보를 추가
                if (currentChatbotType === '06' && currentThreadId === null) {
                    message += `\n샵 이름: <?php echo $_SESSION['lagosana_add_info_1']; ?>`;
                    message += `\n샵 정보: <?php echo $_SESSION['lagosana_add_info_2']; ?>`;
                }
                
                // API 호출을 위한 데이터 준비
                const requestData = {
                    member_id: '<?php echo isset($_SESSION['lagosana_member_id']) ? $_SESSION['lagosana_member_id'] : ''; ?>', // 세션에서 회원 ID 가져오기
                    message: message,
                    thread_id: currentThreadId, // 현재 thread_id (없으면 null)
                    chatbot_tp_cd: currentChatbotType // 현재 선택된 탭의 chatbot_tp_cd 값
                };

                const chatUrl = window.location.protocol + '//' + window.location.hostname + ':8088/chat';

                console.log(`chatUrl : ${chatUrl}`);    //  TODO : 삭제 예정
                
                // API 호출
                fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    // 응답이 JSON 형식인지 확인
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.error('서버 응답:', text);
                            throw new TypeError('응답이 JSON 형식이 아닙니다.');
                        });
                    }
                    
                    return response.json();
                })
                .then(data => {
                    // 로딩 메시지 제거
                    chatMessages.removeChild(loadingMessageElement);
                    
                    // thread_id 저장
                    if (data.thread_id) {
                        currentThreadId = data.thread_id;
                    }
                    
                    // 봇 응답 메시지 추가
                    const botMessageElement = document.createElement('div');
                    botMessageElement.className = 'message message-bot';
                    botMessageElement.innerHTML = `
                        <div class="message-content">
                            ${formatMessageContent(data.response)}
                        </div>
                        <div class="message-time">${getCurrentTime()}</div>
                    `;
                    
                    chatMessages.appendChild(botMessageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                })
                .catch(error => {
                    console.error('API 호출 오류:', error);
                    
                    // 로딩 메시지 제거
                    chatMessages.removeChild(loadingMessageElement);
                    
                    // 오류 메시지 표시
                    const errorMessageElement = document.createElement('div');
                    errorMessageElement.className = 'message message-bot';
                    errorMessageElement.innerHTML = `
                        <div class="message-content">
                            죄송합니다. 응답을 받는 중 오류가 발생했습니다: ${error.message}
                        </div>
                        <div class="message-time">${getCurrentTime()}</div>
                    `;
                    
                    chatMessages.appendChild(errorMessageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
            }
        }
        
        // 탭 전환 기능
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // 활성 탭 클래스 제거
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                
                // 클릭한 탭에 활성 클래스 추가
                this.classList.add('active');
                
                // thread_id 초기화 (새로운 대화 시작)
                currentThreadId = null;
                
                // 탭에 따른 chatbot_tp_cd 설정
                switch(this.textContent) {
                    case '전문 마케터':
                        currentChatbotType = '01';
                        break;
                    case '리뷰 마법사':
                        currentChatbotType = '02';
                        break;
                    case '라고사나 Q&A':
                        currentChatbotType = '03';
                        break;
                    case '이벤트 플래너':
                        currentChatbotType = '04';
                        break;
                    case '패키징 마법사':
                        currentChatbotType = '05';
                        break;
                    case 'Daily SNS':
                        currentChatbotType = '06';
                        break;
                }

                loadChatHistory(); // 채팅 기록 로드
                
                // 탭에 따른 초기 메시지 설정 (실제 구현 시 이 부분 확장)
                const chatMessages = document.querySelector('.chat-messages');
                chatMessages.innerHTML = ''; // 기존 메시지 지우기
                
                let initialMessage = '';
                
                switch(this.textContent) {
                    case '전문 마케터':
                        initialMessage = '안녕하세요! 라고사나 전문 마케터입니다. 어떤 SNS 플랫폼에서 홍보하실 건가요?';
                        break;
                    case '리뷰 마법사':
                        initialMessage = '안녕하세요! 라고사나 리뷰 마법사입니다. 시술 후 어떤 변화가 있었나요?';
                        break;
                    case '라고사나 Q&A':
                        initialMessage = '안녕하세요! 라고사나 Q&A 도우미입니다. 무엇을 도와드릴까요?';
                        break;
                    case '이벤트 플래너':
                        initialMessage = '안녕하세요! 라고사나 이벤트 플래너입니다. 어떤 이벤트를 기획하고 싶으신가요?';
                        break;
                    case '패키징 마법사':
                        initialMessage = '안녕하세요! 라고사나 패키징 마법사입니다. 어떤 패키징 아이디어가 필요하신가요?';
                        break;
                    case 'Daily SNS':
                        initialMessage = '안녕하세요! 라고사나 Daily SNS 챗봇입니다. Blog, Facebook, Instagram, Thread 중 하나를 골라주세요.';
                        break;
                }
                
                // 초기 메시지 추가
                const messageElement = document.createElement('div');
                messageElement.className = 'message message-bot';
                messageElement.innerHTML = `
                    <div class="message-content">
                        ${initialMessage}
                    </div>
                    <div class="message-time">${getCurrentTime()}</div>
                `;
                
                chatMessages.appendChild(messageElement);
            });
        });
        
        // 입력창 자동 높이 조절
        document.querySelector('.chat-input').addEventListener('input', function() {
            // 높이 초기화
            this.style.height = 'auto';
            // 내용에 맞게 높이 조절 (최소 1줄, 최대 5줄)
            const newHeight = Math.min(Math.max(this.scrollHeight, 40), 120);
            this.style.height = newHeight + 'px';
        });
        
        // 현재 시간 가져오기
        function getCurrentTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            return `${hours}:${minutes}`;
        }
        
        // 개행 문자를 HTML <br> 태그로 변환하는 함수
        function formatMessageContent(content) {
            if (!content) return '';
            // 개행 문자(\n)를 <br> 태그로 변환
            return content.replace(/\n/g, '<br>');
        }
        
        // 복사하기 버튼
        document.querySelector('.tool-buttons .tool-button:first-child').addEventListener('click', function() {
            // 마지막 봇 메시지 내용 가져오기
            const lastBotMessage = document.querySelector('.message-bot:last-child .message-content').innerText;
            
            // 클립보드에 복사
            navigator.clipboard.writeText(lastBotMessage)
                .then(() => {
                    alert('내용이 클립보드에 복사되었습니다.');
                })
                .catch(err => {
                    console.error('복사 실패:', err);
                });
        });
    </script>
</body>
</html>