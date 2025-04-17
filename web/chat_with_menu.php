<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LagoSana 챗봇</title>
    <link href="css/chat_with_menu.css?v=1.0.0" rel="stylesheet">
</head>
<body>
    <!-- 왼쪽 사이드바 (대화 기록) -->
    <div class="sidebar">
        <div class="brand">
            <!-- <img src="/api/placeholder/130/40" alt="LagoSana 로고"> -->
            <img src="https://ecimg.cafe24img.com/pg1028b12001162094/platform66/web/upload/category/editor/2024/06/27/27b3562f49cec05d23bec700ae7d64e7.png" alt="LagoSana 로고">
        </div>
        <div class="history-title">대화 기록</div>
        <ul class="chat-history">
            <?php
            /**
             * 채팅 기록 데이터 가져오기
             * 
             * get_chat_with_menu.php 파일을 호출하여 API에서 가져온 채팅 기록 데이터를
             * JSON 형태로 받아와 화면에 표시합니다.
             */
            
            // API 호출 결과 가져오기
            require 'data/get_chat_with_menu.php';
            $chat_history_data = get_chat_history();
            
            // 채팅 기록이 있는 경우
            if (isset($chat_history_data['success']) && $chat_history_data['success'] === true && 
                isset($chat_history_data['chat_history']) && !empty($chat_history_data['chat_history'])) {
                
                // 첫 번째 항목은 활성 상태로 표시
                $first_item = true;
                
                // 채팅 기록 반복 처리
                foreach ($chat_history_data['chat_history'] as $item) {
                    // 활성 상태 클래스 설정
                    $active_class = $first_item ? 'active' : '';
                    
                    // HTML 출력
                    echo '<li class="' . $active_class . '" data-thread-id="' . htmlspecialchars($item['thread_id']) . '">';
                    echo '<div class="history-item-title">' . htmlspecialchars($item['contents']) . '</div>';
                    echo '<div class="history-item-date">' . htmlspecialchars($item['create_dtm']) . '</div>';
                    echo '</li>';
                    
                    // 첫 번째 항목 처리 후 플래그 변경
                    $first_item = false;
                }
            } else {
                // 채팅 기록이 없는 경우 기본 항목 표시
                echo '<li class="active" data-thread-id="default">';
                echo '<div class="history-item-title">새로운 대화 시작하기</div>';
                echo '<div class="history-item-date">지금</div>';
                echo '</li>';
            }
            ?>
        </ul>
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
            <div class="message message-bot">
                <div class="message-content">
                    안녕하세요! 라고사나 전문 마케터입니다. 어떤 SNS 플랫폼에서 홍보하실 건가요?
                </div>
                <div class="message-time">14:30</div>
            </div>
            
            <div class="message message-user">
                <div class="message-content">
                    인스타그램에 피부 관리 포스팅을 작성하고 싶어요.
                </div>
                <div class="message-time">14:31</div>
            </div>
            
            <div class="message message-bot">
                <div class="message-content">
                    인스타그램 포스팅을 위한 정보를 알려주세요!<br><br>
                    1. 어떤 시술/제품을 홍보하고 싶으신가요?<br>
                    2. 타겟층은 어떻게 되나요? (연령대, 성별 등)<br>
                    3. 포스팅의 분위기를 선택해주세요 (전문적/친근한/고급스러운)
                </div>
                <div class="message-time">14:31</div>
            </div>
            
            <div class="message message-user">
                <div class="message-content">
                    1. 피부 관리 중 '수분 집중 케어' 프로그램을 홍보하고 싶어요<br>
                    2. 20-30대 여성이 주요 타겟입니다<br>
                    3. 친근하면서도 전문적인 느낌으로 작성해주세요
                </div>
                <div class="message-time">14:32</div>
            </div>
            
            <div class="message message-bot">
                <div class="message-content">
                    감사합니다! 아래와 같은 인스타그램 포스팅을 작성해 드렸습니다:<br><br>
                    
                    ✨ 당신의 피부가 갈증을 느낄 때 #라고사나 ✨<br><br>
                    
                    건조한 날씨에 피부가 푸석푸석하신가요? 🤔<br>
                    화장이 들뜨고 각질이 올라오는 계절이 왔어요!<br><br>
                    
                    라고사나의 #수분집중케어 프로그램으로<br>
                    칙칙한 피부에 생기를 불어넣어보세요 💧<br><br>
                    
                    🔍 라고사나만의 특별한 3단계:<br>
                    1️⃣ 딥 클렌징으로 피부 결 정리<br>
                    2️⃣ 고농축 히알루론산 앰플 투입<br>
                    3️⃣ 쿨링 수분 마스크 마무리<br><br>
                    
                    💬 20대 고객 김OO님의 생생후기<br>
                    "화장품만으로는 해결 안되던 건조함이 싹~ 사라졌어요!"<br><br>
                    
                    🎁 4월 한정 이벤트: 첫 방문 고객 20% 할인<br>
                    👉 라고사나에서 촉촉한 봄을 맞이하세요!<br><br>
                    
                    #라고사나 #수분관리 #피부관리전문 #수분충전<br>
                    #건조피부해결 #피부탄력 #에스테틱 #뷰티케어
                </div>
                <div class="message-time">14:33</div>
            </div>
        </div>
        
        <!-- 도구 버튼 -->
        <div class="tool-buttons">
            <button class="tool-button">복사하기</button>
            <button class="tool-button">다운로드</button>
        </div>
        
        <!-- 채팅 입력 영역 -->
        <div class="chat-input-container">
            <div class="chat-input-wrap">
                <input type="text" class="chat-input" placeholder="메시지를 입력하세요...">
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
        // 탭 전환 기능
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // 활성 탭 클래스 제거
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                
                // 클릭한 탭에 활성 클래스 추가
                this.classList.add('active');
                
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
                        initialMessage = '안녕하세요! 라고사나 Daily SNS 챗봇입니다. 오늘의 SNS 콘텐츠 주제는 무엇인가요?';
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
        
        // 대화 기록 선택 기능
        document.querySelectorAll('.chat-history li').forEach(item => {
            item.addEventListener('click', function() {
                // 활성 클래스 제거
                document.querySelectorAll('.chat-history li').forEach(i => i.classList.remove('active'));
                
                // 클릭한 항목에 활성 클래스 추가
                this.classList.add('active');
                
                // thread_id 가져오기
                const threadId = this.getAttribute('data-thread-id');
                
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
                fetch(`data/get_chat_with_menu.php?thread_id=${threadId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        // 응답이 JSON 형식인지 확인
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            // 응답 본문을 텍스트로 읽어서 오류 메시지 확인
                            return response.text().then(text => {
                                console.error('서버 응답:', text);
                                throw new TypeError('응답이 JSON 형식이 아닙니다.');
                            });
                        }

                        // 응답 본문을 텍스트로 먼저 읽어서 JSON 파싱 오류 확인
                        return response.text().then(text => {
                            try {
                                // JSON 파싱 시도
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('JSON 파싱 오류:', e);
                                console.error('서버 응답:', text);
                                throw new TypeError(`JSON 파싱 오류: ${e.message}`);
                            }
                        });
                    })
                    .then(data => {
                        // 로딩 메시지 제거
                        chatMessages.innerHTML = '';
                        
                        // 오류가 있는 경우 처리
                        if (!data.success) {
                            throw new Error(data.error || '알 수 없는 오류가 발생했습니다.');
                        }
                        
                        // 채팅 이력이 있는 경우
                        if (data.chat_thread_history && data.chat_thread_history.length > 0) {
                            // 채팅 이력 표시
                            data.chat_thread_history.forEach(chat => {
                                const messageElement = document.createElement('div');
                                
                                // req_res 값에 따라 메시지 클래스 설정
                                if (chat.req_res === 'REQ') {
                                    messageElement.className = 'message message-bot';
                                } else if (chat.req_res === 'RES') {
                                    messageElement.className = 'message message-user';
                                }
                                
                                // 메시지 내용 설정
                                messageElement.innerHTML = `
                                    <div class="message-content">
                                        ${formatMessageContent(chat.contents)}
                                    </div>
                                    <div class="message-time">${chat.create_dtm}</div>
                                `;
                                
                                // 채팅 메시지 영역에 추가
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
                        console.error('채팅 이력 불러오기 오류:', error);
                        
                        // 오류 메시지 표시
                        chatMessages.innerHTML = '';
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'message message-bot';
                        errorMessage.innerHTML = `
                            <div class="message-content">
                                채팅 이력을 불러오는 중 오류가 발생했습니다: ${error.message}
                            </div>
                            <div class="message-time">${getCurrentTime()}</div>
                        `;
                        chatMessages.appendChild(errorMessage);
                    });
            });
        });
        
        // 메시지 전송 기능
        document.querySelector('.send-button').addEventListener('click', function() {
            sendMessage();
        });
        
        document.querySelector('.chat-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
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
        
        // 메시지 전송 함수
        function sendMessage() {
            const input = document.querySelector('.chat-input');
            const message = input.value.trim();
            
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
                
                // 입력창 비우기
                input.value = '';
                
                // 스크롤을 맨 아래로
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // 실제 구현 시 이 부분에서 API 호출하여 응답을 받아옴
                // 여기서는 간단한 응답 시뮬레이션
                setTimeout(() => {
                    const botResponse = '감사합니다! 입력하신 내용을 확인 중입니다. 잠시만 기다려주세요.';
                    const botMessageElement = document.createElement('div');
                    botMessageElement.className = 'message message-bot';
                    botMessageElement.innerHTML = `
                        <div class="message-content">
                            ${formatMessageContent(botResponse)}
                        </div>
                        <div class="message-time">${getCurrentTime()}</div>
                    `;
                    
                    chatMessages.appendChild(botMessageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 1000);
            }
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