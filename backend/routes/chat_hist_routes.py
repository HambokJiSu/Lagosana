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
                WITH W_CHAT_HIST AS (	--	조회기간 필터링
                    SELECT	*
                    FROM	tb_chat_hist
                    WHERE	create_dtm >= STR_TO_DATE(:start_date, '%Y-%m-%d')
                    AND		create_dtm < DATE_ADD(STR_TO_DATE(:end_date, '%Y-%m-%d'), INTERVAL 1 DAY)
                    AND		req_res = 'RES'	--	응답기준
                ), W_EXCEPT AS (		--	제외대상 선정
                    SELECT	user_id, thread_id
                    FROM	W_CHAT_HIST
                    GROUP BY user_id, thread_id
                    HAVING COUNT(1) < 2		--	대화 주제별 1번의 요청만 있는 경우는 제외
                )
                SELECT	ROW_NUMBER()OVER(
                            ORDER BY COUNT(DISTINCT T.thread_id) DESC, COUNT(1) DESC) 	AS rnk			--	포스팅 건수별 + API 호출 횟수 랭킹
                        ,T.user_id
                        ,COUNT(DISTINCT T.thread_id) 									AS posting_cnt	--	포스팅 건수
                        ,COUNT(1) 														AS api_use_cnt	--	API 호출 횟수
                        ,ROUND(SUM(T.res_term) / COUNT(DISTINCT T.thread_id), 1) 		AS posting_avg	--	포스팅별 평균 소요시간(초)
                FROM	W_CHAT_HIST AS T
                WHERE	NOT EXISTS 	(
                                    SELECT	0
                                    FROM	W_EXCEPT AS S
                                    WHERE	S.user_id 	= T.user_id
                                    AND		S.thread_id = T.thread_id
                                    )
                GROUP BY T.user_id
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
