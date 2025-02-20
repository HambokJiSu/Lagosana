from typing import Any, Dict, Optional


def snake_to_camel(snake_str: str) -> str:
    """
    스네이크 케이스(SNAKE_CASE)를 카멜 케이스(camelCase)로 변환하는 함수

    Args:
        snake_str (str): 변환할 스네이크 케이스 문자열

    Returns:
        str: 카멜 케이스 문자열
    """
    components = snake_str.lower().split("_")
    return components[0] + "".join(x.capitalize() for x in components[1:])


def convert_keys_to_camel(data: Dict[str, Any]) -> Dict[str, Any]:
    """
    딕셔너리 키를 모두 CamelCase로 변환하는 함수

    Args:
        data (Dict[str, Any]): 변환할 딕셔너리

    Returns:
        Dict[str, Any]: 키가 CamelCase로 변환된 딕셔너리
    """
    return {snake_to_camel(key): value for key, value in data.items()}


def standard_response(
    success: bool,
    data: Optional[Any] = None,
    error_code: str = "",
    error_message: str = "",
) -> Dict[str, Any]:
    """
    API 응답을 표준화하는 함수

    Args:
        success (bool): 요청 성공 여부
        data (Any, optional): 응답 데이터
        error_code (str, optional): 에러 코드
        error_message (str, optional): 에러 메시지

    Returns:
        Dict[str, Any]: 표준화된 JSON 응답 객체
    """

    # 리스트 데이터도 처리할 수 있도록 변환
    # if isinstance(data, list):
    #     data = [convert_keys_to_camel(row) for row in data] if data else []
    # elif isinstance(data, dict):
    #     data = convert_keys_to_camel(data)

    return {
        "success": success,
        "data": data if data is not None else {},
        "error": {"code": error_code, "message": error_message},
    }
