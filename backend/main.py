from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import logging
from logging.handlers import TimedRotatingFileHandler
import os
from datetime import datetime

from core.config import settings
from routes import chat_routes, chat_hist_routes

# 애플리케이션 시작 시 한 번만 실행되는 로거 설정 함수
def setup_logger_once(pLog_dir, pLog_backup_terms):
    global logger  # 전역 로거 객체 사용

    if "app_logger" in logging.root.manager.loggerDict:
        return logging.getLogger("app_logger")  # 이미 설정된 로거 반환

    os.makedirs(pLog_dir, exist_ok=True)

    # 현재 날짜를 포함한 로그 파일명
    log_filename = os.path.join(
        pLog_dir, f"app_{datetime.now().strftime('%Y-%m-%d')}.log"
    )

    handler = TimedRotatingFileHandler(
        log_filename, when="midnight", interval=1, backupCount=pLog_backup_terms, encoding="utf-8"
    )
    handler.suffix = "%Y-%m-%d"  # 일자별 로그 파일 자동 생성

    # 로그 포맷 설정 (로그 생성일시 포함)
    formatter = logging.Formatter("%(asctime)s - %(levelname)s - %(message)s")
    handler.setFormatter(formatter)

    # 스트림 핸들러 추가 (콘솔 출력)
    stream_handler = logging.StreamHandler()
    stream_handler.setFormatter(formatter)  # 동일한 포맷 적용

    logger = logging.getLogger("app_logger")
    logger.setLevel(logging.INFO)
    logger.addHandler(handler)  # 파일 로깅 추가
    logger.addHandler(stream_handler)  # 터미널 출력 추가

    return logger

# 전역적으로 로거 인스턴스를 설정
app_logger = setup_logger_once(settings.SERVER_LOG_DIR, settings.SERVER_LOG_BACKUP_TERMS)

# FastAPI 앱 생성 및 로깅 설정
app = FastAPI(
    title="Lagosana Chat API",
    debug=settings.SERVER_DEBUG,
)

app.include_router(chat_routes.router)      #  블로그 작성 도움 API 라우터 추가
app.include_router(chat_hist_routes.router) #  블로그 작성 통계 API 라우터 추가

chat_routes.logger = app_logger
chat_hist_routes.logger = app_logger

# CORS 미들웨어 추가
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost",
        "https://ai.lagosana.com",
    ],  # 특정 도메인만 허용하려면 "*" 대신 ["http://localhost:3000"] 같은 리스트를 사용
    allow_credentials=True,
    allow_methods=["*"],  # 모든 HTTP 메서드 허용 (OPTIONS 포함)
    allow_headers=["*"],  # 모든 헤더 허용
)

if __name__ == "__main__":
    import uvicorn

    if settings.SERVER_RUN_ENV == "prod":  # 운영환경에서는 SSL 적용
        uvicorn.run(
            app,
            host="0.0.0.0",
            port=8088,
            workers=1,
            ssl_certfile=settings.CERTS_SSL_CERTFILE,
            ssl_keyfile=settings.CERTS_SSL_KEYFILE,
            ssl_keyfile_password=settings.CERTS_SSL_KEYFILE_PASS,
        )
    else:  # 개발환경에서는 SSL 미적용
        uvicorn.run(app, host="0.0.0.0", port=8088)
