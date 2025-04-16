from core.config import settings
from sqlalchemy.engine import Connection
from sqlmodel import create_engine
from sqlalchemy.pool import QueuePool
from sqlalchemy.exc import OperationalError
import time

# 연결 풀링 설정 추가
engine = create_engine(
    str(settings.SQLALCHEMY_DATABASE_URL),
    poolclass=QueuePool,
    pool_size=5,  # 기본 연결 풀 크기
    max_overflow=10,  # 추가 연결 허용 수
    pool_timeout=30,  # 연결 대기 시간
    pool_recycle=3600,  # 1시간마다 연결 재활용 (MySQL의 wait_timeout보다 짧게 설정)
    pool_pre_ping=True,  # 연결 전 ping으로 연결 상태 확인
)

def get_connection() -> Connection:
    """
    데이터베이스 커넥션을 반환하는 함수
    연결이 끊어진 경우 재연결을 시도합니다.

    Returns:
        Connection: SQLAlchemy 데이터베이스 커넥션 객체
    """
    max_retries = 3
    retry_count = 0
    
    while retry_count < max_retries:
        try:
            return engine.connect()
        except OperationalError as e:
            if "MySQL server has gone away" in str(e) or "ConnectionAbortedError" in str(e):
                retry_count += 1
                if retry_count < max_retries:
                    # 지수 백오프로 재시도 간격 증가
                    time.sleep(1 * (2 ** (retry_count - 1)))
                    continue
            # 다른 오류이거나 최대 재시도 횟수 초과 시 예외 발생
            raise
