from sqlalchemy.sql import text
from typing import Dict, Any, List, Optional
from sqlalchemy.exc import OperationalError, SQLAlchemyError

from core.db import get_connection
from util.standard_response import standard_response


def sp_set_chat_hist(data: Dict[str, Any]) -> Dict[str, Any]:
    """
    SP : Chat 요청이력 저장

    Args:
        cat_data (Dict[str, Any]): 테스트 JSON

    Returns:
        Dict[str, Any]: SP 리턴 정보
    """
    try:
        with get_connection() as conn:
            # Chat 요청이력 저장
            # CREATE PROCEDURE lagosana.SP_SET_CHAT_HIST(
            #     P_USER_ID		VARCHAR(50)	--	사용자ID
            #     ,P_THREAD_ID	VARCHAR(50)	--	대화 고유ID
            #     ,P_REQ_RES		VARCHAR(3)	--	요청/응답 구분 (REQ : 요청, RES : 응답)
            #     ,P_CONTENTS 	MEDIUMTEXT		CHARSET utf8	--	대화내용
            # )
            query = text(
                """
                CALL SP_SET_CHAT_HIST(:p_user_id, :p_thread_id, :p_req_res, :p_contents, :p_res_term);
            """
            )

            # 트랜잭션 시작
            with conn.begin():
                result = conn.execute(
                    query,
                    {
                        "p_user_id": data["p_user_id"],
                        "p_thread_id": data["p_thread_id"],
                        "p_req_res": data["p_req_res"],
                        "p_contents": data["p_contents"],
                        "p_res_term": data["p_res_term"],
                    },
                )
                # 모든 결과 반환
                rows = result.fetchall()
                # 결과를 딕셔너리 리스트로 변환
                return standard_response(
                    success=True,
                    data=[dict(row._mapping) for row in rows],
                )

    except KeyError:
        return standard_response(
            success=False,
            error_code="BAD_REQUEST",
            error_message="필수 파라미터가 누락되었습니다.",
        )
    except OperationalError as e:
        # MySQL 연결 관련 오류 처리
        error_msg = str(e)
        if "MySQL server has gone away" in error_msg or "ConnectionAbortedError" in error_msg:
            return standard_response(
                success=False,
                error_code="DB_CONNECTION_ERROR",
                error_message=f"데이터베이스 연결 오류: {error_msg}",
            )
        return standard_response(
            success=False,
            error_code="DB_OPERATIONAL_ERROR",
            error_message=f"데이터베이스 작업 오류: {error_msg}",
        )
    except SQLAlchemyError as e:
        return standard_response(
            success=False,
            error_code="DB_ERROR",
            error_message=f"데이터베이스 오류: {str(e)}",
        )
    except Exception as e:
        return standard_response(
            success=False,
            error_code="INTERNAL_ERROR",
            error_message=f"SP_SET_CHAT_HIST 저장 실패: {str(e)}",
        )
