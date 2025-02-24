from typing import Optional, List
from pydantic import BaseModel


# 4. Pydantic 모델 정의
class ChatRequest(BaseModel):
    member_id: str  # 사용자 식별자 (없으면 빈 문자열)
    message: str
    thread_id: Optional[str] = None  # 기존 대화 스레드 식별자 (없으면 신규 생성)


class ChatResponse(BaseModel):
    thread_id: str
    response: str
