from pydantic import MySQLDsn
from pydantic_settings import BaseSettings, SettingsConfigDict
from dotenv import load_dotenv
import os
from pathlib import Path

# .env 파일 경로 설정
env_path = Path(__file__).parent.parent / ".env"
load_dotenv(dotenv_path=env_path)


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=env_path, env_file_encoding="utf-8")

    # Database
    MYSQL_SERVER: str
    MYSQL_PORT: int
    MYSQL_USER: str
    MYSQL_PASSWORD: str
    MYSQL_DB: str

    @property
    def SQLALCHEMY_DATABASE_URL(self) -> MySQLDsn:
        return MySQLDsn.build(
            scheme="mysql+pymysql",
            username=self.MYSQL_USER,
            password=self.MYSQL_PASSWORD,
            host=self.MYSQL_SERVER,
            port=self.MYSQL_PORT,
            path=self.MYSQL_DB,
        )


# .env 파일에서 설정을 읽어오고 환경변수를 설정합니다.
settings = Settings()  # type: ignore
