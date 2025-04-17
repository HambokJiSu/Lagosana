<?php
/**
 * 채팅 기록을 API에서 가져와 가공하는 스크립트
 * 
 * 이 스크립트는 사용자의 채팅 기록을 API에서 가져와 가공하여
 * chat_with_menu.php 페이지에서 사용할 수 있는 형태로 반환합니다.
 */

// $config = parse_ini_file('../lagosana_conf.ini', true);

// if ($config['SERVER']['runEnv'] != "local") {
//     if ($_SERVER["REQUEST_METHOD"] == "POST") {
//         $customer = json_decode($_POST['customer'] ?? '[]', true);
    
//         if (empty($customer['group_name']) || $customer['group_name'] !== "VIP") {
//             // VIP가 아닌 경우, 접근 제한 메시지를 출력하고 종료
//             die("<script>alert('비정상적인 접근입니다.'); window.location.href='https://lagosana.com';</script>");
//         }
//     } else {
//         // POST 요청이 아닌 경우, 접근 제한 메시지를 출력하고 종료
//         die("<script>alert('비정상적인 접근입니다.'); window.location.href='https://lagosana.com';</script>");
//     }
// } else {
    // 로컬 환경에서는 테스트를 위해 하드코딩
    $customer = [
        'member_id' => 'mirang',
        'group_name' => 'VIP'
    ];
// }

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
 * 채팅 기록을 가져오는 함수
 * 
 * 이 함수는 외부에서 직접 호출할 수 있으며,
 * 채팅 기록 데이터를 배열 형태로 반환합니다.
 * 
 * @return array 채팅 기록 데이터
 */
function get_chat_history() {
    // 세션 시작 (사용자 인증 정보를 가져오기 위해)
    // session_start();
    global $customer;

    // $customer 변수가 정의되지 않은 경우 기본값 설정
    if (!isset($customer) || !is_array($customer)) {
        $customer = [
            'member_id' => 'mirang',
            'group_name' => 'default_group'
        ];
    }

    // 사용자 ID 가져오기 (실제 구현에서는 세션에서 가져오거나 다른 방식으로 처리)
    $user_id = $customer['member_id'];

    // API URL 구성
    $api_url = "http://localhost:8088/chat-hist/user-thread/{$user_id}?read_cnt=20";

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
    global $customer;

    // $customer 변수가 정의되지 않은 경우 기본값 설정
    if (!isset($customer) || !is_array($customer)) {
        $customer = [
            'member_id' => 'mirang',
            'group_name' => 'default_group'
        ];
    }

    // 사용자 ID 가져오기
    $user_id = $customer['member_id'];

    // API URL 구성
    $api_url = "http://localhost:8088/chat-hist/user-chat/{$user_id}/{$thread_id}";

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
        // 스레드 ID가 제공된 경우 해당 스레드의 채팅 이력 반환
        if (isset($_GET['thread_id'])) {
            $result = get_chat_thread_history($_GET['thread_id']);
        } else {
            // 스레드 ID가 없는 경우 전체 채팅 이력 반환
            $result = get_chat_history();
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
// GET 파라미터로 thread_id가 있는 경우에도 처리 함수 호출 (AJAX 요청이 아닌 경우에만)
else if (isset($_GET['thread_id'])) {
    handle_ajax_request();
}
?>
