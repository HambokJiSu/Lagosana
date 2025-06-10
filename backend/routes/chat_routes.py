import time
from fastapi import APIRouter, HTTPException, Query, Request, Depends
from sqlalchemy.ext.asyncio import AsyncSession
from typing import Dict, Any, List, Optional
from datetime import datetime
from openai import AsyncOpenAI
import asyncio

from core.config import settings
from core.database import get_db
from model.chat_model import ChatRequest, ChatResponse
from util.db_func import sp_set_chat_hist, get_assistants_id

# FastAPI 라우터 설정
router = APIRouter(prefix="/chat", tags=["chat"])

# main.py에서 전달할 logger 변수
logger = None

# OpenAI 비동기 클라이언트 인스턴스 생성
client = AsyncOpenAI(api_key=settings.API_GPT_API_KEY)

async def create_new_thread():
    """새로운 스레드를 비동기로 생성합니다."""
    thread = await client.beta.threads.create()
    return thread

async def wait_on_run(run, thread_id):
    """실행 상태를 비동기로 확인합니다."""
    while True:
        run = await client.beta.threads.runs.retrieve(
            thread_id=thread_id,
            run_id=run.id,
        )
        
        if run.status in ["completed", "failed", "expired", "cancelled"]:
            break
            
        await asyncio.sleep(0.5)
    
    return run

async def submit_message(assistant_id, thread_id, user_message):
    """메시지를 비동기로 제출합니다."""
    await client.beta.threads.messages.create(
        thread_id=thread_id,
        role="user",
        content=user_message
    )
    
    run = await client.beta.threads.runs.create(
        thread_id=thread_id,
        assistant_id=assistant_id,
    )
    return run

async def get_response(thread_id):
    """응답을 비동기로 가져옵니다."""
    messages = await client.beta.threads.messages.list(thread_id=thread_id)
    return messages.data[0].content[0].text.value

async def ask(assistant_id, thread_id, user_message):
    """전체 대화 프로세스를 비동기로 처리합니다."""
    run = await submit_message(assistant_id, thread_id, user_message)
    run = await wait_on_run(run, thread_id)
    return run

@router.post("/", response_model=ChatResponse)
async def post_chat(
    request: Request,
    chat_req: ChatRequest,
    db: AsyncSession = Depends(get_db)
):
    """
    챗봇 요청 응답 메소드

    Args:
        request: 시스템 변수
        chat_req: 채팅 요청 정보
        db: DB 세션

    Returns:
        ChatResponse
    """
    start_time = time.time()

    # 사용자 접속 정보 추출
    client_host = request.client.host if request.client else "unknown"
    client_port = request.client.port if request.client else "unknown"

    # assistant_id 호출
    result = await get_assistants_id(db, {"chatbot_tp_cd": chat_req.chatbot_tp_cd})
    if not result["success"]:
        logger.error(
            f"Request DB call error : {result.get('error_code', 'UNKNOWN')}, msg : {result.get('error_message', 'Unknown error')}"
        )
        raise HTTPException(status_code=500, detail="OpenAI assistant_id 호출 중 오류 발생")
    
    assistant_id = result["data"]["assistants_id"]

    logger.info(
        f"Request from member_id: {chat_req.member_id}, client_ip: {client_host}:{client_port}, "
        f"thread_id: {chat_req.thread_id}, chatbot_tp_cd: {chat_req.chatbot_tp_cd}, "
        f"chat: {chat_req.message}"
    )

    # 스레드 ID 확인 (없으면 신규 생성)
    thread_id = chat_req.thread_id if chat_req.thread_id else (await create_new_thread()).id

    try:
        run = await ask(assistant_id, thread_id, chat_req.message)
        if run.status in ["failed", "expired", "cancelled"]:
            raise HTTPException(
                status_code=500,
                detail=f"OpenAI API 호출 중 오류 발생 : {run.status}"
            )
    except Exception as e:
        logger.error(f"OpenAI API 호출 중 오류 발생: {str(e)}")
        raise HTTPException(status_code=500, detail="OpenAI API 호출 중 오류 발생")

    # 응답 메시지 추출
    assistant_message = await get_response(thread_id)

    elapsed_time = time.time() - start_time
    logger.info(
        f"Request from member_id: {chat_req.member_id}, client_ip: {client_host}:{client_port}, "
        f"thread_id: {thread_id}, chatbot_tp_cd: {chat_req.chatbot_tp_cd}, "
        f"processed in: {elapsed_time:.3f}"
    )

    # 대화 이력 저장 (비동기)
    await sp_set_chat_hist(
        db,
        {
            "p_user_id": chat_req.member_id,
            "p_thread_id": thread_id,
            "p_req_res": "REQ",
            "p_contents": chat_req.message,
            "p_res_term": 0,
            "p_chatbot_tp_cd": chat_req.chatbot_tp_cd,
        }
    )

    await sp_set_chat_hist(
        db,
        {
            "p_user_id": chat_req.member_id,
            "p_thread_id": thread_id,
            "p_req_res": "RES",
            "p_contents": assistant_message,
            "p_res_term": elapsed_time,
            "p_chatbot_tp_cd": chat_req.chatbot_tp_cd,
        }
    )

    return ChatResponse(thread_id=thread_id, response=assistant_message)
