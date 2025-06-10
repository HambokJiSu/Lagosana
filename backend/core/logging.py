import logging
import sys
from pythonjsonlogger import jsonlogger
from datetime import datetime
import os
from core.config import settings

class AsyncJsonFormatter(jsonlogger.JsonFormatter):
    def add_fields(self, log_record, record, message_dict):
        super(AsyncJsonFormatter, self).add_fields(log_record, record, message_dict)
        log_record['timestamp'] = datetime.utcnow().isoformat()
        log_record['level'] = record.levelname
        log_record['logger'] = record.name

def setup_logging():
    # 로그 디렉토리 생성
    os.makedirs(settings.SERVER_LOG_DIR, exist_ok=True)
    
    # JSON 포맷터 설정
    formatter = AsyncJsonFormatter(
        '%(timestamp)s %(level)s %(name)s %(message)s'
    )
    
    # 파일 핸들러 설정
    file_handler = logging.handlers.TimedRotatingFileHandler(
        filename=os.path.join(settings.SERVER_LOG_DIR, f"app_{datetime.now().strftime('%Y-%m-%d')}.log"),
        when='midnight',
        interval=1,
        backupCount=settings.SERVER_LOG_BACKUP_TERMS,
        encoding='utf-8'
    )
    file_handler.setFormatter(formatter)
    
    # 콘솔 핸들러 설정
    console_handler = logging.StreamHandler(sys.stdout)
    console_handler.setFormatter(formatter)
    
    # 루트 로거 설정
    root_logger = logging.getLogger()
    root_logger.setLevel(logging.INFO)
    root_logger.addHandler(file_handler)
    root_logger.addHandler(console_handler)
    
    # FastAPI 로거 설정
    fastapi_logger = logging.getLogger("fastapi")
    fastapi_logger.setLevel(logging.INFO)
    fastapi_logger.addHandler(file_handler)
    fastapi_logger.addHandler(console_handler)
    
    # Uvicorn 로거 설정
    uvicorn_logger = logging.getLogger("uvicorn")
    uvicorn_logger.setLevel(logging.INFO)
    uvicorn_logger.addHandler(file_handler)
    uvicorn_logger.addHandler(console_handler)
    
    return root_logger 