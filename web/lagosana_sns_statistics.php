<!DOCTYPE html>
<html>
<?php
    $config = parse_ini_file('../lagosana_conf.ini', true);
    $aiStatisticsUrl = $config['FRONT']['aiStatisticsUrl'];
?>
<head>
    <meta charset="UTF-8">
    <title>라고사나 AI 통계</title>
    <link href="css/lagosana.css?v=1.0.24" rel="stylesheet">
    <link rel='stylesheet' href='/assets/tabulator/tabulator.min.css'>
    <script type='text/javascript' src='/assets/tabulator/tabulator.min.js'></script>
    <script type='text/javascript' src='/assets/util/dayjs.min.js'></script>
    <script>
        let _table = null;

        // 기간별 통계 데이터 조회
        function getStatistics() {
            const _API_URL = "<?php echo $aiStatisticsUrl; ?>"; // FastAPI 서버의 URL

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

            var url = _API_URL + "/?start_date=" + startDate + "&end_date=" + endDate;
            
            // 로딩 표시
            // _table.setData([]);
            // _table.setLoading("데이터를 불러오는 중...");

            _table.alert("Loding");

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
                    _table.clearAlert();
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
                    { title: "순위", field: "rnk", width: 100, hozAlign: "center"},
                    { title: "사용자 ID", field: "user_id", width: 200},
                    { title: "포스팅 건수<br/>(기간 합산)", field: "posting_cnt", width: 200, formatter: formatNumber, hozAlign: "right" },
                    { title: "API 사용 건수<br/>(기간 합산)", field: "api_use_cnt", width: 200, formatter: formatNumber, hozAlign: "right" },
                    { title: "응답 소요시간<br/>(평균, 초)", field: "posting_avg", formatter: formatNumber, hozAlign: "right" },
                ],
                pagination: true,
                paginationSize: 20,
                paginationSizeSelector: [10, 20, 50, 100],
            });

            // 초기 날짜 설정
            document.getElementById("startDate").value = dayjs().add(-1, 'month').format('YYYY-MM-DD');
            document.getElementById("endDate").value = dayjs().format('YYYY-MM-DD');

            getStatistics()
        });
    </script>
</head>
<body class="statistics-body">
    <div class="statistics-container">
        <div class="statistics-header">
            <h1>Lagosana AI Solution Usage Status </h1>
        </div>
        
        <div class="search-container">
            <div class="search-form">
                <label for="startDate">From</label>
                <input type="date" id="startDate" />
                <label for="endDate">To</label>
                <input type="date" id="endDate" />
                <button class="search-button" onclick="getStatistics()">조회</button>
            </div>
        </div>

        <div id="divTable"></div>

        <div class="statistics-footer">
            <p>※ API사용 건수란, 하나의 포스팅 당 몇 건의 대화를 주고 받았는지에 대한 수치입니다.</p>
            <p>※ API사용 건수가 1건, 즉 대화 시작만 하고 주고받지 않은 경우에는 통계에서 제하였습니다.</p>
        </div>
</body>
</html>