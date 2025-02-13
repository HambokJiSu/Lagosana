import time
import logging
import configparser
from typing import Optional, List

from openai import OpenAI
from fastapi import FastAPI, Request, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

# 1. 설정파일(config.ini) 읽기
config = configparser.ConfigParser()
config.read("config.ini", encoding="utf-8")  # 파일 인코딩을 UTF-8로 지정

try:
    gpt_api_key = config.get("API", "gptApiKey")
    gpt_assistant_id = config.get("API", "gptAssistantId")
except Exception as e:
    raise Exception("config.ini 파일을 읽는 중 오류가 발생했습니다.") from e

# OpenAI 클라이언트 인스턴스 생성 (API 키 설정)
client = OpenAI(api_key=gpt_api_key)

# 2. FastAPI 앱 생성 및 로깅 설정
app = FastAPI()

# CORS 미들웨어 추가
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "*"
    ],  # 특정 도메인만 허용하려면 "*" 대신 ["http://localhost:3000"] 같은 리스트를 사용
    allow_credentials=True,
    allow_methods=["*"],  # 모든 HTTP 메서드 허용 (OPTIONS 포함)
    allow_headers=["*"],  # 모든 헤더 허용
)

# 로그 포맷 설정 : 접속 IP, Port, 처리시간을 기록
logging.basicConfig(
    level=logging.INFO, format="%(asctime)s - %(levelname)s - %(message)s"
)

# 3. 대화 스레드(대화 이력)를 저장할 전역 변수
# 각 스레드는 리스트 형태의 메시지 이력을 가짐. 메시지 형식은 OpenAI ChatCompletion API와 동일한 형식입니다.
conversation_threads = {}


# 4. Pydantic 모델 정의
class ChatRequest(BaseModel):
    message: str
    thread_id: Optional[str] = None  # 기존 대화 스레드 식별자 (없으면 신규 생성)


class ChatResponse(BaseModel):
    thread_id: str
    response: str


def build_message_history(thread_id: str, user_message: str) -> List[dict]:
    """
    주어진 스레드에 사용자 메시지를 추가한 후 전체 이력을 반환합니다.
    신규 스레드인 경우 system 메시지에 assistant id를 포함시켜 초기화합니다.
    """
    if thread_id not in conversation_threads:
        # 신규 스레드 생성 (시스템 초기 메시지에 gpt_assistant_id 포함)
        conversation_threads[thread_id] = [
            {"role": "system", "content": f"assistant_id: {gpt_assistant_id}"}
        ]
    # 사용자 메시지 추가
    conversation_threads[thread_id].append({"role": "user", "content": user_message})
    return conversation_threads[thread_id]


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


# def print_message(response):
#     for res in response:
#         print(f"[{res.role.upper()}]\n{res.content[0].text.value}\n")


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


@app.post("/chat", response_model=ChatResponse)
async def chat_endpoint(request: Request, chat_req: ChatRequest):
    start_time = time.time()

    # 사용자 접속 정보(IP, Port) 추출
    client_host = request.client.host if request.client else "unknown"
    client_port = request.client.port if request.client else "unknown"

    logging.info(
        f"Request from {client_host}:{client_port} thread_id : {chat_req.thread_id}, chat : {chat_req.message}"
    )

    # 스레드 ID 확인 (없으면 신규 생성)
    thread_id = chat_req.thread_id if chat_req.thread_id else create_new_thread().id

    # 대화 이력 업데이트: 사용자 메시지 추가
    # messages = build_message_history(thread_id, chat_req.message)

    try:
        run = ask(gpt_assistant_id, thread_id, chat_req.message)
        if run.status in ["failed", "expired", "cancelled"]:
            raise HTTPException(
                status_code=500, detail=f"OpenAI API 호출 중 오류 발생 : {run.status}"
            )
    except Exception as e:
        logging.error(f"OpenAI API 호출 중 오류 발생: {e}")
        raise HTTPException(status_code=500, detail="OpenAI API 호출 중 오류 발생")

    # 응답 메시지 추출 및 대화 이력에 추가 (assistant 역할)
    assistant_message = get_response(thread_id)
    # conversation_threads[thread_id].append(
    #     {"role": "assistant", "content": assistant_message}
    # )

    elapsed_time = time.time() - start_time
    logging.info(
        f"Request from {client_host}:{client_port} processed in {elapsed_time:.3f} seconds."
    )

    return ChatResponse(thread_id=thread_id, response=assistant_message)


if __name__ == "__main__":
    import uvicorn

    # uvicorn 실행 시 성능 부하를 최소화하기 위해 싱글 워커로 실행
    uvicorn.run("blog_chat:app", host="0.0.0.0", port=8000, workers=1)
