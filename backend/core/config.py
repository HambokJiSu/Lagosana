from pydantic import MySQLDsn
from pydantic_settings import BaseSettings, SettingsConfigDict
from dotenv import load_dotenv
from pathlib import Path
import sys

# PyInstaller 실행 환경인지 확인
if getattr(sys, 'frozen', False):
    env_path = ".env"
else:
    env_path = Path(__file__).parent.parent / ".env"

# .env 파일 경로 설정
# env_path = Path(__file__).parent.parent / ".env"
# env_path = "D:/_Lagosana/backend/.env"
# env_path = Path(__file__).parent / ".env"
print('env_path:', env_path)
load_dotenv(dotenv_path=env_path)

class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=env_path, env_file_encoding="utf-8")

    # Database
    MYSQL_SERVER: str
    MYSQL_PORT: int
    MYSQL_USER: str
    MYSQL_PASSWORD: str
    MYSQL_DB: str

    # Server
    SERVER_RUN_ENV: str
    SERVER_DEBUG: bool
    SERVER_LOG_DIR: str
    SERVER_LOG_BACKUP_TERMS: int

    # CERTS
    CERTS_SSL_CERTFILE: str
    CERTS_SSL_KEYFILE: str
    CERTS_SSL_KEYFILE_PASS: str

    # API
    API_GPT_API_KEY: str
    API_GPT_ASSISTANT_ID: str

    # FRONT
    FRONT_BLOG_CHAT_API_URL: str

    @property
    def SQLALCHEMY_DATABASE_URL(self) -> MySQLDsn:
        return MySQLDsn.build(
            scheme="mysql+pymysql",
            username=self.MYSQL_USER,
            password=self.MYSQL_PASSWORD,
            host=self.MYSQL_SERVER,
            port=self.MYSQL_PORT,
            path=self.MYSQL_DB,
            query="charset=utf8mb4"  # charset 추가
        )

    @property
    def SERVER_RUN_ENV(self) -> str:
        return self.SERVER_RUN_ENV

    @property
    def SERVER_DEBUG(self) -> bool:
        return self.SERVER_DEBUG

    @property
    def SERVER_LOG_DIR(self) -> str:
        return self.SERVER_LOG_DIR

    @property
    def SERVER_LOG_BACKUP_TERMS(self) -> int:
        return self.SERVER_LOG_BACKUP_TERMS

    @property
    def CERTS_SSL_CERTFILE(self) -> str:
        return self.CERTS_SSL_CERTFILE

    @property
    def CERTS_SSL_KEYFILE(self) -> str:
        return self.CERTS_SSL_KEYFILE

    @property
    def CERTS_SSL_KEYFILE_PASS(self) -> str:
        return self.CERTS_SSL_KEYFILE_PASS

    @property
    def API_GPT_API_KEY(self) -> str:
        return self.API_GPT_API_KEY

    @property
    def API_GPT_ASSISTANT_ID(self) -> str:
        return self.API_GPT_ASSISTANT_ID

    @property
    def FRONT_BLOG_CHAT_API_URL(self) -> str:
        return self.FRONT_BLOG_CHAT_API_URL


# .env 파일에서 설정을 읽어오고 환경변수를 설정합니다.
settings = Settings()  # type: ignore
