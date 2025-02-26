<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>라고사나 AI 통계</title>
    <link href="css/lagosana.css?v=1.0.2" rel="stylesheet">
    <link rel='stylesheet' href='/assets/tabulator/tabulator.min.css'>
    <script type='text/javascript' src='/assets/tabulator/tabulator.min.js'></script>
    <script>
        let _table = null;

        // 기간별 통계 데이터 조회
        function getStatistics() {
            var startDate = document.getElementById("startDate").value;
            var endDate = document.getElementById("endDate").value;

            if (!startDate || !endDate) {
                alert("조회 기간을 선택해주세요.");
                return;
            }

            if (startDate > endDate) {
                alert("시작일이 종료일보다 늦을 수 없습니다.");
                return;
            }

            var url = "http://localhost:8088/chat-hist/?start_date=" + startDate + "&end_date=" + endDate;
            
            // 로딩 표시
            _table.setData([]);
            // _table.setLoading("데이터를 불러오는 중...");

            fetch(url)
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(function (data) {
                    _table.setData(data.data);
                })
                .catch(function (error) {
                    console.error('Error:', error);
                    alert('데이터 조회 중 오류가 발생했습니다.');
                })
                .finally(function () {
                    _table.clearLoading();
                });
        }

        // 날짜 형식 포매터
        function formatDate(cell) {
            const date = new Date(cell.getValue());
            return date.toLocaleDateString('ko-KR');
        }

        // 숫자 형식 포매터
        function formatNumber(cell) {
            return cell.getValue().toLocaleString('ko-KR');
        }

        // 모든 리소스가 로드된 후 실행
        window.addEventListener("load", function () {
            // 테이블 생성
            _table = new Tabulator("#divTable", {
                layout: "fitColumns",
                placeholder: "데이터가 없습니다.",
                columns: [
                    { title: "날짜", field: "create_dt", width: 120, formatter: formatDate },
                    { title: "사용자 ID", field: "user_id", width: 150 },
                    { title: "사용 건수", field: "thread_cnt", width: 120, formatter: formatNumber, hozAlign: "right" },
                    { title: "API 사용 건수", field: "api_user_cnt", width: 150, formatter: formatNumber, hozAlign: "right" },
                    { title: "응답 소요시간(초)", field: "res_term_sum", formatter: formatNumber, hozAlign: "right" },
                ],
                pagination: true,
                paginationSize: 10,
                paginationSizeSelector: [10, 20, 50, 100],
            });

            // 초기 날짜 설정
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            document.getElementById("startDate").value = firstDay.toISOString().split('T')[0];
            document.getElementById("endDate").value = lastDay.toISOString().split('T')[0];
        });
    </script>
</head>
<body>
    <div class="statistics-container">
        <div class="statistics-header">
            <h1>라고사나 AI 통계</h1>
        </div>
        
        <div class="search-container">
            <div class="search-form">
                <label for="startDate">시작일</label>
                <input type="date" id="startDate" />
                <label for="endDate">종료일</label>
                <input type="date" id="endDate" />
                <button class="search-button" onclick="getStatistics()">조회</button>
            </div>
        </div>

        <div id="divTable"></div>
    </div>
</body>
</html>