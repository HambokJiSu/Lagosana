from core.config import settings
from sqlalchemy.engine import Connection
from sqlmodel import create_engine

engine = create_engine(str(settings.SQLALCHEMY_DATABASE_URL))


def get_connection() -> Connection:
    """
    데이터베이스 커넥션을 반환하는 함수

    Returns:
        Connection: SQLAlchemy 데이터베이스 커넥션 객체
    """
    return engine.connect()
