poetry로 버전 관리

모듈 추가시
poetry add <package_name>

# 최초 작업
## 가상환경 생성
python -m venv .venv

# 프로젝트 수행시
## Windows
cd backend
.venv\Scripts\activate

# 구) 실행파일 생성 방법
pyinstaller --onefile main.py

## MySQL 포함 후 실행파일 생성 방법
poetry run pyinstaller --onefile --paths=$(poetry env info --path)/lib/site-packages --collect-submodules=sqlmodel main.py