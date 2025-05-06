<?php
session_start();

function getCurrentDomain() {
    // HTTP 또는 HTTPS 판별
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

    // 호스트 이름 가져오기
    $host = $_SERVER['HTTP_HOST'];

    // 최종 도메인 주소 반환
    return $protocol . $host . ":8088";
}

// API 호출 함수
function callApi($url) {
    // cURL 초기화
    $ch = curl_init();
    
    // cURL 옵션 설정
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // API 호출 실행
    $response = curl_exec($ch);
    
    // cURL 오류 확인
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'error' => $error];
    }
    
    // cURL 종료
    curl_close($ch);
    
    // JSON 응답 디코딩
    return json_decode($response, true);
}

/**
 * 사용자의 채팅 이력을 가져오는 함수
 * 
 * 이 함수는 외부에서 직접 호출할 수 있으며,
 * 특정 스레드의 채팅 이력 데이터를 배열 형태로 반환합니다.
 * 
 * @return array 채팅 이력 데이터
 */
function get_thread_history() {
    // 사용자 ID 가져오기
    $user_id = $_SESSION['lagosana_member_id'];

    // API URL 구성
    $api_url = getCurrentDomain() . "/chat-hist/user-thread/{$user_id}?read_cnt=20";

    // API 호출 및 응답 처리
    $api_response = callApi($api_url);

    // 채팅 기록 데이터 초기화
    $chat_history = [];

    // API 응답이 성공적인 경우 데이터 처리
    if (isset($api_response['success']) && $api_response['success'] === true && isset($api_response['data'])) {
        // 데이터 가공
        foreach ($api_response['data'] as $item) {
            // 날짜 형식 변환 (예: 2023-04-15 14:30:00 -> 2023.04.15)
            $date = new DateTime($item['create_dtm']);
            $formatted_date = $date->format('Y.m.d');
            
            // 현재 날짜와 비교하여 "오늘", "어제" 등으로 표시
            $today = new DateTime();
            $yesterday = new DateTime('yesterday');
            
            if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
                $display_date = '오늘 ' . $date->format('H:i');
            } elseif ($date->format('Y-m-d') === $yesterday->format('Y-m-d')) {
                $display_date = '어제';
            } else {
                $display_date = $formatted_date;
            }
            
            // 채팅 기록 배열에 추가
            $chat_history[] = [
                'thread_id' => $item['thread_id'],
                'contents' => $item['contents'],
                'create_dtm' => $display_date
            ];
        }
    }

    // 결과 반환
    return [
        'success' => true,
        'chat_history' => $chat_history
    ];
}

/**
 * 특정 스레드의 채팅 이력을 가져오는 함수
 * 
 * 이 함수는 외부에서 직접 호출할 수 있으며,
 * 특정 스레드의 채팅 이력 데이터를 배열 형태로 반환합니다.
 * 
 * @param string $thread_id 채팅 스레드 ID
 * @return array 채팅 이력 데이터
 */
function get_chat_thread_history($thread_id) {
    // 사용자 ID 가져오기
    $user_id = $_SESSION['lagosana_member_id'];

    // API URL 구성
    $api_url = getCurrentDomain() . "/chat-hist/user-chat/{$user_id}/{$thread_id}";

    // API 호출 및 응답 처리
    $api_response = callApi($api_url);

    // 채팅 이력 데이터 초기화
    $chat_thread_history = [];

    // API 응답이 성공적인 경우 데이터 처리
    if (isset($api_response['success']) && $api_response['success'] === true && isset($api_response['data'])) {
        // 데이터 가공
        foreach ($api_response['data'] as $item) {
            // 날짜 형식 변환
            $date = new DateTime($item['create_dtm']);
            $formatted_time = $date->format('H:i');
            
            // 채팅 이력 배열에 추가
            $chat_thread_history[] = [
                'thread_id' => $item['thread_id'],
                'contents' => $item['contents'],
                'create_dtm' => $formatted_time,
                'req_res' => $item['req_res']
            ];
        }
    }

    // 결과 반환
    return [
        'success' => true,
        'chat_thread_history' => $chat_thread_history
    ];
}

// AJAX 요청 처리 함수 추가
function handle_ajax_request() {
    // 출력 버퍼 시작
    ob_start();
    
    // 오류 핸들러 설정
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // 오류를 JSON 형식으로 반환
        ob_clean(); // 출력 버퍼 정리
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]);
        exit;
    });
    
    // 예외 핸들러 설정
    set_exception_handler(function($exception) {
        // 예외를 JSON 형식으로 반환
        ob_clean(); // 출력 버퍼 정리
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
        exit;
    });
    
    try {
        if ($_GET['tp'] == "chatThread") {
            $result = get_thread_history();
        } else if ($_GET['tp'] == "chatThreadDetail") {
            if (isset($_GET['thread_id'])) {
                $result = get_chat_thread_history($_GET['thread_id']);
            }
            else {
                throw new Exception("Thread ID is required.");
            }
        }
        else {
            throw new Exception("Invalid method type.");
        }
        
        // 출력 버퍼 정리
        ob_clean();
        
        // JSON 헤더 설정 (출력 버퍼 정리 후에 설정)
        header('Content-Type: application/json; charset=utf-8');
        
        // JSON 출력
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Exception $e) {
        // 출력 버퍼 정리
        ob_clean();
        
        // JSON 헤더 설정 (출력 버퍼 정리 후에 설정)
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    // 출력 버퍼 종료
    ob_end_flush();
}

// AJAX 요청인 경우 처리 함수 호출
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    handle_ajax_request();
} 
?>
