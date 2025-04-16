import time
from fastapi import APIRouter, HTTPException, Query, Request
from sqlalchemy.sql import text
from typing import Dict, Any, List, Optional
from datetime import datetime
from openai import OpenAI

from core.config import settings
from model.chat_model import ChatRequest, ChatResponse
from util.db_func import sp_set_chat_hist

# FastAPI 라우터 설정
router = APIRouter(prefix="/chat", tags=["chat"])

# main.py에서 전달할 logger 변수
logger = None  # main.py에서 설정됨

# OpenAI 클라이언트 인스턴스 생성 (API 키 설정)
client = OpenAI(api_key=settings.API_GPT_API_KEY)

# 신규 스레드 생성 함수
def create_new_thread():
    # 새로운 스레드를 생성합니다.
    thread = client.beta.threads.create()
    return thread


# 반복문에서 대기하는 함수
def wait_on_run(run, thread_id):
    while True:
        # 3-3. 실행 상태를 최신 정보로 업데이트합니다.
        run = client.beta.threads.runs.retrieve(
            thread_id=thread_id,
            run_id=run.id,
        )

        if run.status in ["completed", "failed", "expired", "cancelled"]:
            break

        time.sleep(0.5)

    return run


def submit_message(assistant_id, thread_id, user_message):
    # 3-1. 스레드에 종속된 메시지를 '추가' 합니다.
    client.beta.threads.messages.create(
        thread_id=thread_id, role="user", content=user_message
    )
    # 3-2. 스레드를 실행합니다.
    run = client.beta.threads.runs.create(
        thread_id=thread_id,
        assistant_id=assistant_id,
    )
    return run


def get_response(thread_id):
    # 3-4. 스레드에 종속된 메시지를 '조회' 합니다.
    messages = client.beta.threads.messages.list(thread_id=thread_id)
    return messages.data[0].content[0].text.value


def ask(assistant_id, thread_id, user_message):
    run = submit_message(
        assistant_id,
        thread_id,
        user_message,
    )
    # 실행이 완료될 때까지 대기합니다.
    run = wait_on_run(run, thread_id)
    # print_message(get_response(thread_id).data[-2:])
    return run


@router.post("/", response_model=ChatResponse)
async def post_blog_chat(request: Request, chat_req: ChatRequest):
    """
    블로그 채팅 요청 응답 메소드

    Args:
        request: 시스템 변수
        chat_req: 채팅 요청 정보

    Returns:
        ChatResponse
    """
    start_time = time.time()

    # 사용자 접속 정보(IP, Port) 추출
    client_host = request.client.host if request.client else "unknown"
    client_port = request.client.port if request.client else "unknown"

    logger.info(
        f"Request from member_id: {chat_req.member_id}, client_ip: {client_host}:{client_port}, thread_id: {chat_req.thread_id}, chat: {chat_req.message}"
    )

    # 스레드 ID 확인 (없으면 신규 생성)
    thread_id = chat_req.thread_id if chat_req.thread_id else create_new_thread().id

    # 대화 이력 업데이트: 사용자 메시지 추가
    # messages = build_message_history(thread_id, chat_req.message)

    try:
        run = ask(settings.API_GPT_ASSISTANT_ID, thread_id, chat_req.message)
        if run.status in ["failed", "expired", "cancelled"]:
            raise HTTPException(
                status_code=500, detail=f"OpenAI API 호출 중 오류 발생 : {run.status}"
            )
    except Exception as e:
        # logging.getLogger("uvicorn.access").error(f"OpenAI API 호출 중 오류 발생: {e}")
        raise HTTPException(status_code=500, detail="OpenAI API 호출 중 오류 발생")

    # 응답 메시지 추출 및 대화 이력에 추가 (assistant 역할)
    assistant_message = get_response(thread_id)
    # conversation_threads[thread_id].append(
    #     {"role": "assistant", "content": assistant_message}
    # )

    elapsed_time = time.time() - start_time
    logger.info(
        f"Request from member_id: {chat_req.member_id}, client_ip: {client_host}:{client_port}, thread_id: {chat_req.thread_id}, processed in: {elapsed_time:.3f}"
    )

    result = sp_set_chat_hist(
        {
            "p_user_id": chat_req.member_id,
            "p_thread_id": thread_id,
            "p_req_res": "REQ",
            "p_contents": chat_req.message,
            "p_res_term": 0,
        }
    )

    if result["success"] == False:
        logger.error(
            f"Request DB call error : {result.get('error_code', 'UNKNOWN')}, msg : {result.get('error_message', 'Unknown error')}"
        )

    result = sp_set_chat_hist(
        {
            "p_user_id": chat_req.member_id,
            "p_thread_id": thread_id,
            "p_req_res": "RES",
            "p_contents": assistant_message,
            "p_res_term": elapsed_time,
        }
    )

    if result["success"] == False:
        logger.error(
            f"Response DB call error : {result.get('error_code', 'UNKNOWN')}, msg : {result.get('error_message', 'Unknown error')}"
        )

    return ChatResponse(thread_id=thread_id, response=assistant_message)
