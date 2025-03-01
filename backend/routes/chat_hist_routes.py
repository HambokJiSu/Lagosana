from fastapi import APIRouter, HTTPException, Query
from sqlalchemy.sql import text
from typing import Dict, Any, List, Optional
from datetime import datetime

from util.standard_response import standard_response
from core.db import get_connection

# FastAPI 라우터 설정
router = APIRouter(prefix="/chat-hist", tags=["chat-hist"])

# main.py에서 전달할 logger 변수
logger = None  # main.py에서 설정됨

@router.get("/")
async def get_activity_logs(
    start_date: Optional[datetime] = Query(None, description="조회 시작일"),
    end_date: Optional[datetime] = Query(None, description="조회 종료일"),
) -> Dict[str, Any]:
    """
    사용자 활용 통계 조회

    Args:
        start_date (Optional[datetime]): 조회 시작일
        end_date (Optional[datetime]): 조회 종료일

    Returns:
        Dict[str, Any]: 사용자 활용 통계
    """
    try:
        with get_connection() as conn:
            query = f"""
                SELECT	DATE(A.create_dtm) 				AS create_dt	--	사용일자
                        ,A.user_id										--	사용자ID
                        ,COUNT(DISTINCT A.thread_id) 	AS thread_cnt	--	사용건수
                        ,SUM(IF(req_res = 'RES', 1, 0))	AS api_user_cnt	--	API 사용건수
                        ,SUM(A.res_term)				AS res_term_sum	--	응답 총 소요 시간(초)
                FROM	tb_chat_hist AS A
                WHERE	A.create_dtm >= STR_TO_DATE(:start_date, '%Y-%m-%d')
                AND		A.create_dtm < DATE_ADD(STR_TO_DATE(:end_date, '%Y-%m-%d'), INTERVAL 1 DAY)
                GROUP BY DATE(A.create_dtm), A.user_id
                ORDER BY DATE(A.create_dtm) DESC, A.user_id
            """
            params = {"start_date": start_date, "end_date": end_date}

            result = conn.execute(text(query), params)
            logs = [dict(row._mapping) for row in result]

            if not logs:
                return standard_response(
                    success=False,
                    error_code="NOT_FOUND",
                    error_message=f"조회 이력을 찾을 수 없습니다.",
                )

            return standard_response(success=True, data=logs)

    except HTTPException:
        raise
    except Exception as e:
        return standard_response(
            success=False,
            error_code="INTERNAL_ERROR",
            error_message=f"데이터 오류 : {str(e)}",
        )
