from fastapi import FastAPI, WebSocket
from fastapi.middleware.cors import CORSMiddleware
from openai import OpenAI
import asyncio
import json
from datetime import datetime
import time

from configparser import ConfigParser

#   ini 값 호출
config = ConfigParser()
config.read("config.ini", encoding="utf-8")  # 파일 인코딩을 UTF-8로 지정

app = FastAPI()

# CORS 설정
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# OpenAI 클라이언트 설정
client = OpenAI(api_key=config["API"]["gptApiKey"])

ASSISTANT_ID = config["API"]["gptAssistantId"]

# 상수 정의
MAX_RETRY_ATTEMPTS = 3
RETRY_DELAY = 2  # 초
RUN_TIMEOUT = 30  # 초
POLL_INTERVAL = 0.5  # 초


async def wait_for_run_completion(thread_id: str, run_id: str, websocket: WebSocket):
    """실행 완료를 기다리는 함수"""
    start_time = datetime.now()
    retry_count = 0

    while True:
        try:
            run = client.beta.threads.runs.retrieve(thread_id=thread_id, run_id=run_id)

            # 실행 상태 체크
            if run.status == "completed":
                return True, run
            elif run.status == "failed":
                return False, "실행이 실패했습니다."
            elif run.status == "expired":
                return False, "실행 시간이 만료되었습니다."
            elif run.status == "cancelled":
                return False, "실행이 취소되었습니다."

            # 타임아웃 체크
            elapsed_time = (datetime.now() - start_time).total_seconds()
            if elapsed_time > RUN_TIMEOUT:
                await websocket.send_text(
                    json.dumps(
                        {
                            "type": "error",
                            "message": "응답 시간이 초과되었습니다. 다시 시도해주세요.",
                        }
                    )
                )
                return False, "시간 초과"

            await asyncio.sleep(POLL_INTERVAL)

        except Exception as e:
            retry_count += 1
            if retry_count >= MAX_RETRY_ATTEMPTS:
                return False, f"API 호출 중 오류가 발생했습니다: {str(e)}"

            await asyncio.sleep(RETRY_DELAY)


@app.websocket("/chat")
async def websocket_endpoint(websocket: WebSocket):
    await websocket.accept()

    try:
        thread = client.beta.threads.create()

        while True:
            try:
                # 클라이언트로부터 메시지 수신
                data = await websocket.receive_text()
                start_time = time.time()

                print(f"Received message: {data}")

                if data == "new_session":
                    thread = client.beta.threads.create()
                    await websocket.send_text(
                        json.dumps(
                            {
                                "type": "session",
                                "message": "새로운 세션이 시작되었습니다.",
                            }
                        )
                    )
                    continue

                # 메시지 생성
                try:
                    message = client.beta.threads.messages.create(
                        thread_id=thread.id, role="user", content=data
                    )
                except Exception as e:
                    await websocket.send_text(
                        json.dumps(
                            {
                                "type": "error",
                                "message": "메시지 생성 중 오류가 발생했습니다.",
                            }
                        )
                    )
                    continue

                # 실행 생성
                try:
                    run = client.beta.threads.runs.create(
                        thread_id=thread.id, assistant_id=ASSISTANT_ID
                    )
                except Exception as e:
                    print("Exception! ==> " + e.error.message)
                    await websocket.send_text(
                        json.dumps(
                            {
                                "type": "error",
                                "message": "실행 생성 중 오류가 발생했습니다.",
                            }
                        )
                    )
                    continue

                # 실행 완료 대기
                success, result = await wait_for_run_completion(
                    thread.id, run.id, websocket
                )

                if not success:
                    await websocket.send_text(
                        json.dumps({"type": "error", "message": result})
                    )
                    continue

                try:
                    messages = client.beta.threads.messages.list(thread_id=thread.id)
                    assistant_message = messages.data[0].content[0].text.value

                    end_time = time.time()
                    elapsed_time = end_time - start_time
                    print(f"응답 시작까지 소요된 시간: {elapsed_time:.2f}초")

                    # 줄 단위로 분할하여 전송
                    lines = assistant_message.splitlines(True)
                    for line in lines:
                        await websocket.send_text(
                            json.dumps({"type": "token", "message": line})
                        )
                        await asyncio.sleep(0.1)

                    await websocket.send_text(
                        json.dumps({"type": "end", "message": ""})
                    )

                except Exception as e:
                    await websocket.send_text(
                        json.dumps(
                            {
                                "type": "error",
                                "message": "응답 처리 중 오류가 발생했습니다.",
                            }
                        )
                    )

            except Exception as e:
                await websocket.send_text(
                    json.dumps(
                        {
                            "type": "error",
                            "message": f"처리 중 오류가 발생했습니다: {str(e)}",
                        }
                    )
                )

    except Exception as e:
        await websocket.send_text(json.dumps({"type": "error", "message": str(e)}))


if __name__ == "__main__":
    import uvicorn

    if config["SERVER"]["runEnv"] == "prod":  # 운영환경에서는 SSL 적용
        uvicorn.run(
            app,
            host="0.0.0.0",
            port=8088,
            ssl_certfile=config["CERTS"]["sslCertfile"],
            ssl_keyfile=config["CERTS"]["sslKeyfile"],
            ssl_keyfile_password=config["CERTS"]["sslKeyfilePass"],
        )
    else:  # 개발환경에서는 SSL 미적용
        uvicorn.run(app, host="0.0.0.0", port=8088)
