from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import text
from typing import Dict, Any, List, Optional
from sqlalchemy.exc import OperationalError, SQLAlchemyError

# from core.db import get_connection
# from util.standard_response import standard_response


async def sp_set_chat_hist(db: AsyncSession, params: Dict[str, Any]) -> Dict[str, Any]:
    """
    SP : Chat 요청이력 저장

    Args:
        db: DB 세션
        params: 저장할 파라미터

    Returns:
        Dict[str, Any]: SP 리턴 정보
    """
    try:
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
        
        await db.execute(query, params)
        await db.commit()
        
        return {
            "success": True
        }
        
    except Exception as e:
        await db.rollback()
        return {
            "success": False,
            "error_code": "DB_ERROR",
            "error_message": str(e)
        }


async def get_assistants_id(db: AsyncSession, params: Dict[str, Any]) -> Dict[str, Any]:
    """
    챗봇 타입 코드에 해당하는 assistants_id를 조회하는 함수

    Args:
        db: DB 세션
        params: 조회 파라미터

    Returns:
        Dict[str, Any]: 조회된 assistants_id 정보
    """
    try:
        query = text("""
            SELECT  attr1 AS assistants_id
            FROM    tb_common_code
            WHERE   cls_cd = 'CHATBOT_TP'
            AND     cls_yn = 'N'
            AND     comm_cd = :chatbot_tp_cd
        """)
        
        result = await db.execute(query, params)
        row = result.fetchone()
        
        if row:
            return {
                "success": True,
                "data": {"assistants_id": row[0]}
            }
        else:
            return {
                "success": False,
                "error_code": "NOT_FOUND",
                "error_message": "Assistant ID not found"
            }
            
    except Exception as e:
        return {
            "success": False,
            "error_code": "DB_ERROR",
            "error_message": str(e)
        }

