from fastapi import FastAPI, WebSocket, WebSocketDisconnect
from fastapi.middleware.cors import CORSMiddleware
from openai import OpenAI
import asyncio
import json
from datetime import datetime
import time
from concurrent.futures import ThreadPoolExecutor

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

# 스레드 풀 생성
executor = ThreadPoolExecutor(max_workers=5)

# OpenAI API 호출을 위한 별도 함수
def call_openai_api(thread_id: str, run_id: str):
    return client.beta.threads.runs.retrieve(thread_id=thread_id, run_id=run_id)

async def wait_for_run_completion(thread_id: str, run_id: str, websocket: WebSocket):
    """실행 완료를 기다리는 함수"""
    start_time = datetime.now()
    retry_count = 0
    current_poll_interval = 1  # 고정된 폴링 간격

    while True:
        try:
            run = await asyncio.get_event_loop().run_in_executor(
                executor, 
                call_openai_api, 
                thread_id, 
                run_id
            )

            if run.status == "completed":
                return True, run
            elif run.status in ["failed", "expired", "cancelled"]:
                return False, f"실행이 {run.status} 상태가 되었습니다."

            elapsed_time = (datetime.now() - start_time).total_seconds()
            if elapsed_time > RUN_TIMEOUT:
                return False, "시간 초과"

            await asyncio.sleep(current_poll_interval)  # 고정된 폴링 간격

        except Exception as e:
            retry_count += 1
            if retry_count >= MAX_RETRY_ATTEMPTS:
                return False, f"API 호출 중 오류가 발생했습니다: {str(e)}"
            await asyncio.sleep(RETRY_DELAY)  # 재시도 시 대기

# 활성 연결 관리를 위한 클래스 추가
class ConnectionManager:
    def __init__(self):
        self.active_connections: dict = {}

    async def connect(self, websocket: WebSocket, client_id: str):
        await websocket.accept()
        self.active_connections[client_id] = websocket

    def disconnect(self, client_id: str):
        if client_id in self.active_connections:
            del self.active_connections[client_id]

    async def send_message(self, message: str, client_id: str):
        if client_id in self.active_connections:
            try:
                await self.active_connections[client_id].send_text(message)
            except RuntimeError:
                # 연결이 이미 닫힌 경우 무시
                self.disconnect(client_id)

manager = ConnectionManager()

@app.websocket("/chat")
async def websocket_endpoint(websocket: WebSocket):
    # 클라이언트 IP와 포트 정보 추출
    client_host = websocket.client.host
    client_port = websocket.client.port
    client_id = str(id(websocket))
    
    print(f"새로운 연결: {client_host}:{client_port} ({datetime.now().strftime('%Y-%m-%d %H:%M:%S')})")
    await manager.connect(websocket, client_id)
    
    try:
        thread = await asyncio.get_event_loop().run_in_executor(
            executor,
            client.beta.threads.create
        )
        
        while True:
            try:
                data = await websocket.receive_text()
                start_time = time.time()

                print(f"메시지 수신 [{client_host}:{client_port}] ({datetime.now().strftime('%Y-%m-%d %H:%M:%S')}): {data}")

                if data == "new_session":
                    thread = await asyncio.get_event_loop().run_in_executor(
                        executor,
                        client.beta.threads.create
                    )
                    await manager.send_message(
                        json.dumps({
                            "type": "session",
                            "message": "새로운 세션이 시작되었습니다."
                        }),
                        client_id
                    )
                    continue

                # 메시지 생성을 비동기로 처리
                try:
                    message = await asyncio.get_event_loop().run_in_executor(
                        executor,
                        lambda: client.beta.threads.messages.create(
                            thread_id=thread.id,
                            role="user",
                            content=data
                        )
                    )
                except Exception as e:
                    await manager.send_message(
                        json.dumps({
                            "type": "error",
                            "message": "메시지 생성 중 오류가 발생했습니다."
                        }),
                        client_id
                    )
                    continue

                # 실행 생성을 비동기로 처리
                try:
                    run = await asyncio.get_event_loop().run_in_executor(
                        executor,
                        lambda: client.beta.threads.runs.create(
                            thread_id=thread.id,
                            assistant_id=ASSISTANT_ID
                        )
                    )
                except Exception as e:
                    print("Exception! ==> " + str(e))
                    await manager.send_message(
                        json.dumps({
                            "type": "error",
                            "message": "실행 생성 중 오류가 발생했습니다: " + str(e)
                        }),
                        client_id
                    )
                    continue

                # 실행 완료 대기
                success, result = await wait_for_run_completion(
                    thread.id, run.id, websocket
                )

                if not success:
                    await manager.send_message(
                        json.dumps({"type": "error", "message": result}),
                        client_id
                    )
                    continue

                try:
                    # messages = client.beta.threads.messages.list(thread_id=thread.id)
                    messages = await asyncio.get_event_loop().run_in_executor(
                        executor,
                        lambda: client.beta.threads.messages.list(thread_id=thread.id)
                    )
                    assistant_message = messages.data[0].content[0].text.value
                    
                    end_time = time.time()
                    elapsed_time = end_time - start_time
                    print(f"응답 완료 [{client_host}:{client_port}] ({datetime.now().strftime('%Y-%m-%d %H:%M:%S')}) - 소요시간: {elapsed_time:.2f}초")
                    
                    lines = assistant_message.splitlines(True)
                    for line in lines:
                        await manager.send_message(
                            json.dumps({"type": "token", "message": line}),
                            client_id
                        )
                        await asyncio.sleep(0.1)
                    
                    await manager.send_message(
                        json.dumps({"type": "end", "message": ""}),
                        client_id
                    )

                except Exception as e:
                    await manager.send_message(
                        json.dumps({
                            "type": "error",
                            "message": "응답 처리 중 오류가 발생했습니다."
                        }),
                        client_id
                    )

            except WebSocketDisconnect:
                print(f"연결 종료: {client_host}:{client_port} ({datetime.now().strftime('%Y-%m-%d %H:%M:%S')})")
                manager.disconnect(client_id)
                break
            except Exception as e:
                try:
                    await manager.send_message(
                        json.dumps({
                            "type": "error",
                            "message": f"처리 중 오류가 발생했습니다: {str(e)}"
                        }),
                        client_id
                    )
                except:
                    break

    except WebSocketDisconnect:
        print(f"연결 종료: {client_host}:{client_port} ({datetime.now().strftime('%Y-%m-%d %H:%M:%S')})")
        manager.disconnect(client_id)
    except Exception as e:
        try:
            await manager.send_message(
                json.dumps({"type": "error", "message": str(e)}),
                client_id
            )
        except:
            pass
    finally:
        manager.disconnect(client_id)


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
