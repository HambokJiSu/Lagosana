from fastapi import FastAPI, WebSocket
from fastapi.middleware.cors import CORSMiddleware
from openai import OpenAI
import asyncio
import json

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
client = OpenAI(
    api_key="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"
)

ASSISTANT_ID = "asst_w4Q77MpxzeCGc8Iq50IzRevk"

@app.websocket("/chat")
async def websocket_endpoint(websocket: WebSocket):
    await websocket.accept()
    
    thread = client.beta.threads.create()
    
    try:
        while True:
            # 클라이언트로부터 메시지 수신
            data = await websocket.receive_text()

            print(f"Received message: {data}")
            
            if data == "new_session":
                thread = client.beta.threads.create()
                await websocket.send_text(json.dumps({"type": "session", "message": "새로운 세션이 시작되었습니다."}))
                continue
                
            # 메시지 생성
            message = client.beta.threads.messages.create(
                thread_id=thread.id,
                role="user",
                content=data
            )
            
            # 실행 생성
            run = client.beta.threads.runs.create(
                thread_id=thread.id,
                assistant_id=ASSISTANT_ID
            )
            
            # 실행 완료 대기 및 스트리밍
            while True:
                run = client.beta.threads.runs.retrieve(
                    thread_id=thread.id,
                    run_id=run.id
                )
                
                if run.status == "completed":
                    messages = client.beta.threads.messages.list(thread_id=thread.id)
                    assistant_message = messages.data[0].content[0].text.value
                    
                    # 줄 단위로 분할하여 전송
                    lines = assistant_message.splitlines(True)  # keepends=True로 개행문자 유지
                    for line in lines:
                        await websocket.send_text(json.dumps({"type": "token", "message": line}))
                        await asyncio.sleep(0.1)
                    
                    await websocket.send_text(json.dumps({"type": "end", "message": ""}))
                    break
                    
                elif run.status == "failed":
                    await websocket.send_text(json.dumps({"type": "error", "message": "처리 중 오류가 발생했습니다."}))
                    break
                    
                await asyncio.sleep(0.5)
                
    except Exception as e:
        await websocket.send_text(json.dumps({"type": "error", "message": str(e)}))
        
if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000) 