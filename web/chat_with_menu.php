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
            <img src="/api/placeholder/130/40" alt="LagoSana 로고">
        </div>
        <div class="history-title">대화 기록</div>
        <ul class="chat-history">
            <li class="active">
                <div class="history-item-title">마케팅 포스팅 작성</div>
                <div class="history-item-preview">SNS 인스타그램 포스팅 작성</div>
                <div class="history-item-date">오늘 14:32</div>
            </li>
            <li>
                <div class="history-item-title">고객 리뷰 작성</div>
                <div class="history-item-preview">필러 시술 후기 작성</div>
                <div class="history-item-date">오늘 11:25</div>
            </li>
            <li>
                <div class="history-item-title">라고사나 Q&A</div>
                <div class="history-item-preview">가격 문의 및 상담</div>
                <div class="history-item-date">어제</div>
            </li>
            <li>
                <div class="history-item-title">이벤트 기획</div>
                <div class="history-item-preview">봄맞이 프로모션 아이디어</div>
                <div class="history-item-date">2025.04.13</div>
            </li>
            <li>
                <div class="history-item-title">패키징 아이디어</div>
                <div class="history-item-preview">선물용 패키지 디자인</div>
                <div class="history-item-date">2025.04.10</div>
            </li>
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
                
                // 실제 구현 시 이 부분에서 해당 대화 내용을 불러옴
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
                        ${message}
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
                    const botMessageElement = document.createElement('div');
                    botMessageElement.className = 'message message-bot';
                    botMessageElement.innerHTML = `
                        <div class="message-content">
                            감사합니다! 입력하신 내용을 확인 중입니다. 잠시만 기다려주세요.
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