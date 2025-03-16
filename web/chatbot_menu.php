<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Blog Content Generator</title>
    <link href="css/chatbot_menu.css?v=1.0.3" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kaisei+Tokumin:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Korean:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Gowun+Batang:wght@400;700&display=swap" rel="stylesheet">
    <script type='text/javascript' src='/assets/sweetalert/sweetalert2.all.min.js'></script>
    <script>
        let fn_coming_soon = () => {
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
                <div id='chat_card' class='chat_card'>
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
                <div id='chat_card' class='chat_card'>
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
                <div id='chat_card' class='chat_card' onclick="fn_coming_soon()">
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
                <div id='chat_card' class='chat_card' onclick="fn_coming_soon()">
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
                <div id='chat_card' class='chat_card' onclick="fn_coming_soon()">
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
                <div id='chat_card' class='chat_card' onclick="fn_coming_soon()">
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