<!DOCTYPE html>
<html>
<?php
$config = parse_ini_file('../lagosana_conf.ini', true);

if ($config['SERVER']['runEnv'] != "local") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $customer = json_decode($_POST['customer'] ?? '[]', true);
    
        if (empty($customer['group_name']) || $customer['group_name'] !== "VIP") {
            // VIP가 아닌 경우, 접근 제한 메시지를 출력하고 종료
            die("<script>alert('비정상적인 접근입니다.'); window.location.href='https://lagosana.com';</script>");
        } else {
            // 세션 유지 시간 설정 (30분)
            ini_set('session.gc_maxlifetime', 1800); // 서버에서 세션 데이터 보존 시간
            session_set_cookie_params(1800);         // 브라우저 쿠키 유효 시간

            // 세션 시작
            session_start();

            // 세션에 값 저장
            $_SESSION['lagosana_member_id']     = $customer['member_id'];
            $_SESSION['lagosana_group_name']    = $customer['group_name'];
            $_SESSION['lagosana_add_info_0']     = isset($customer['additional_information'][0]) ? $customer['additional_information'][0]['value'] : "";    //  사업자등록번호
            $_SESSION['lagosana_add_info_1']     = isset($customer['additional_information'][1]) ? $customer['additional_information'][1]['value'] : "";    //  사업장명칭
            $_SESSION['lagosana_add_info_2']     = isset($customer['additional_information'][2]) ? $customer['additional_information'][2]['value'] : "";    //  사업장특징
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
        }
    } else {
        // POST 요청이 아닌 경우, 접근 제한 메시지를 출력하고 종료
        die("<script>alert('비정상적인 접근입니다.'); window.location.href='https://lagosana.com';</script>");
    }
} else {
    // 로컬 환경에서는 테스트를 위해 하드코딩

    // 세션 유지 시간 설정 (30분)
    ini_set('session.gc_maxlifetime', 1800); // 서버에서 세션 데이터 보존 시간
    session_set_cookie_params(1800);         // 브라우저 쿠키 유효 시간

    // 세션 시작
    session_start();

    // 세션에 값 저장
    $_SESSION['lagosana_member_id']     = "mirang";
    $_SESSION['lagosana_group_name']    = "VIP";
    $_SESSION['lagosana_add_info_0']     = "123-45-67890";  //  사업자 번호
    $_SESSION['lagosana_add_info_1']     = "린아이티";  //  샵 이름
    $_SESSION['lagosana_add_info_2']     = "햄 대표님 지휘아래 주저리 주저리";  //  샵 정보
    $_SESSION['login_time'] = date('Y-m-d H:i:s');
}
?>
<head>
    <meta charset="UTF-8">
    <title>Blog Content Generator</title>
    <link href="css/chatbot_menu.css?v=1.0.5" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kaisei+Tokumin:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Korean:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gowun+Batang:wght@400;700&display=swap" rel="stylesheet">
    <script type='text/javascript' src='/assets/sweetalert/sweetalert2.all.min.js'></script>
    <script>
        let fnOpenChatbot = (menu) => {
            window.location.href = `chat_with_menu.php?chatbotTp=${menu}`;
        }

        let fnComingSoon = () => {
            Swal.fire("Coming Soon", "서비스 준비 중입니다.", "info");
        }
    </script>
</head>
<body>
    <div id='desktop-1' class='desktop-1'>
        <div id='lagosana_ai_solution' class='lagosana_ai_solution'>
            <div id='lagosana' class='lagosana'>LagoSana</div>
            <div id='aisolution' class='aisolution'>
                AI Solution
            </div>
        </div>
        <div id='texts' class='texts'>
            <div id='text' class='text'>
                <p>여러분이 아름다움에 집중할 수 있도록</p>
                <p>운영은 라고사나 AI 솔루션이 도와드립니다</p>
            </div>
        </div>
        <div id='swiper_container' class='swiper_container'>
            <div id='cards' class='cards'>
                <div id='chat_card' class='chat_card' onclick="fnOpenChatbot('01');">
                    <div id='card_contents' class='card_contents'>
                        <div id='card_name_icon' class='card_name_icon'>
                            <div id='card_name' class='card_name'>
                                SNS전문 마케터</div>
                            <img id='edit-marker' class='edit-marker' src='../img/sns.svg' />
                        </div>
                        <div id='card_description' class='card_description'>
                            매력적인 SNS 홍보 글 생성</div>
                    </div>
                </div>
                <div id='chat_card' class='chat_card' onclick="fnOpenChatbot('02');">
                    <div id='card_contents' class='card_contents'>
                        <div id='card_name_icon' class='card_name_icon'>
                            <div id='card_name' class='card_name'>
                                리뷰 마법사
                            </div>
                            <img id='edit-marker' class='edit-marker' src='../img/review.svg' />
                        </div>
                        <div id='card_description' class='card_description'>
                            자연스러운 고객 리뷰 작성
                        </div>
                    </div>
                </div>
                <div id='chat_card' class='chat_card' onclick="fnComingSoon()">
                    <div id='card_contents' class='card_contents'>
                        <div id='card_name_icon' class='card_name_icon'>
                            <div id='card_name' class='card_name'>
                                라고사나 Q&A</div>
                            <img id='edit-marker' class='edit-marker' src='../img/qna.svg' />
                        </div>
                        <div id='card_description' class='card_description'>
                            이럴 땐, 이렇게 사용하세요
                        </div>
                    </div>
                </div>
            </div>
            <div id='cards' class='cards'>
                <div id='chat_card' class='chat_card' onclick="fnOpenChatbot('04');">
                    <div id='card_contents' class='card_contents'>
                        <div id='card_name_icon' class='card_name_icon'>
                            <div id='card_name' class='card_name'>
                                이벤트 플래너</div>
                            <img id='edit-marker' class='edit-marker' src='../img/planner.svg' />
                        </div>
                        <div id='card_description' class='card_description'>
                            고객 맞춤 이벤트 기획과<br>
                            홍보 전략까지 추천
                        </div>
                    </div>
                </div>
                <div id='chat_card' class='chat_card' onclick="fnComingSoon()">
                    <div id='card_contents' class='card_contents'>
                        <div id='card_name_icon' class='card_name_icon'>
                            <div id='card_name' class='card_name'>
                                시술 패키징 마법사
                            </div>
                            <img id='edit-marker' class='edit-marker' src='../img/wizard.svg' />
                        </div>
                        <div id='card_description' class='card_description'>
                            고객 맞춤 시술 구성 및<br>
                            홍보 문구 작성
                        </div>
                    </div>
                </div>
                <div id='chat_card' class='chat_card' onclick="fnOpenChatbot('06');">
                    <div id='card_contents' class='card_contents'>
                        <div id='card_name_icon' class='card_name_icon'>
                            <div id='card_name' class='card_name'>
                                Daily SNS
                            </div>
                            <img id='edit-marker' class='edit-marker' src='../img/daily.svg' />
                        </div>
                        <div id='card_description' class='card_description'>
                            매일매일 습관처럼<br>SNS 포스팅하기
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>