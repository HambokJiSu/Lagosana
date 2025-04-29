from sqlalchemy.sql import text
from typing import Dict, Any, List, Optional
from sqlalchemy.exc import OperationalError, SQLAlchemyError

from core.db import get_connection
from util.standard_response import standard_response


def sp_set_chat_hist(data: Dict[str, Any]) -> Dict[str, Any]:
    """
    SP : Chat 요청이력 저장

    Args:
        chat_data (Dict[str, Any]): JSON

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
            #     ,P_RES_TERM 	DECIMAL(10, 3) 	--	응답시간
            #     ,P_CHATBOT_TP_CD 	VARCHAR(2) 	--	챗봇 구분
            # )
            query = text(
                """
                CALL SP_SET_CHAT_HIST(:p_user_id, :p_thread_id, :p_req_res, :p_contents, :p_res_term, :p_chatbot_tp_cd);
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
                        "p_chatbot_tp_cd": data["p_chatbot_tp_cd"],
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


def get_assistants_id(data: Dict[str, Any]) -> Dict[str, Any]:
    """
    챗봇 타입 코드에 해당하는 assistants_id를 조회하는 함수

    Args:
        data (Dict[str, Any]): chatbot_tp_cd가 포함된 JSON 데이터

    Returns:
        Dict[str, Any]: 조회된 assistants_id 정보
    """
    try:
        with get_connection() as conn:
            # assistants_id 조회 쿼리
            query = text(
                """
                SELECT  attr1 AS assistants_id
                FROM    tb_common_code
                WHERE   cls_cd = 'CHATBOT_TP'
                AND     cls_yn = 'N'
                AND     comm_cd = :chatbot_tp_cd
                """
            )

            # 쿼리 실행
            result = conn.execute(query, {
                "chatbot_tp_cd": data["chatbot_tp_cd"]
                })
            
            # 결과를 딕셔너리로 변환
            row = result.fetchone()
            
            if row is None:
                return standard_response(
                    success=False,
                    error_code="NOT_FOUND",
                    error_message="해당하는 assistants_id가 존재하지 않습니다.",
                )
                
            return standard_response(
                success=True,
                data=dict(row._mapping)
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
            error_message=f"assistants_id 조회 실패: {str(e)}",
        )

