<?php
/**
 * monitor.php
 * -----------
 * iot_db.sensor_data 테이블의 최신 50건을 조회하여
 * 실시간 모니터링 HTML 페이지로 렌더링한다.
 *
 * 배포 위치: /var/www/html/monitor.php
 * 접속 URL : http://<VM_IP>/monitor.php
 *
 * 페이지는 30초마다 자동 새로고침된다.
 */

// ── DB 접속 정보 (환경에 맞게 수정) ──────────────────────────
$db_host = "localhost";
$db_user = "root";
$db_pass = "linux";           // MySQL 비밀번호
$db_name = "iot_db";

// ── DB 연결 ───────────────────────────────────────────────────
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("<h2 style='color:red'>DB 연결 실패: " . $conn->connect_error . "</h2>");
}
$conn->set_charset("utf8mb4");

// ── 최신 50건 조회 ────────────────────────────────────────────
$sql    = "SELECT id, temperature, humidity, pressure, recorded_at
             FROM sensor_data
            ORDER BY id DESC
            LIMIT 50";
$result = $conn->query($sql);

// ── 차트용 데이터 준비 ────────────────────────────────────────
$labels = [];
$temps  = [];
$hums   = [];
$pres   = [];
$rows   = [];

if ($result && $result->num_rows > 0) {
    // 역순으로 다시 담아 시간 순서대로 차트 표시
    $tmp = [];
    while ($row = $result->fetch_assoc()) {
        $tmp[] = $row;
    }
    $rows = $tmp;
    foreach (array_reverse($tmp) as $r) {
        $labels[] = date("H:i:s", strtotime($r["recorded_at"]));
        $temps[]  = $r["temperature"];
        $hums[]   = $r["humidity"];
        $pres[]   = $r["pressure"];
    }
}

$conn->close();

// JSON 인코딩
$j_labels = json_encode($labels);
$j_temps  = json_encode($temps);
$j_hums   = json_encode($hums);
$j_pres   = json_encode($pres);

$total = count($rows);
$now   = date("Y-m-d H:i:s");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="30">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Sensor Real-Time Monitor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0f1117;
            color: #e0e0e0;
            padding: 24px;
        }
        h1 { font-size: 1.6rem; margin-bottom: 4px; }
        .meta { font-size: 0.85rem; color: #888; margin-bottom: 20px; }
        .cards {
            display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 28px;
        }
        .card {
            flex: 1; min-width: 180px;
            background: #1e2130;
            border-radius: 10px;
            padding: 18px 22px;
        }
        .card .label { font-size: 0.8rem; color: #aaa; text-transform: uppercase; }
        .card .value { font-size: 2rem; font-weight: bold; margin-top: 6px; }
        .card.temp  .value { color: #ff6b6b; }
        .card.hum   .value { color: #48dbfb; }
        .card.pres  .value { color: #ffd32a; }

        .chart-wrapper {
            background: #1e2130;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 28px;
        }
        .chart-wrapper h2 { font-size: 1rem; margin-bottom: 14px; color: #ccc; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #1e2130;
            border-radius: 10px;
            overflow: hidden;
        }
        thead { background: #2a2d3e; }
        th, td {
            padding: 10px 14px;
            text-align: center;
            font-size: 0.88rem;
            border-bottom: 1px solid #2a2d3e;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #252840; }
        th { color: #aaa; font-weight: 600; }
    </style>
</head>
<body>
<h1>IoT Sensor Real-Time Monitor</h1>
<p class="meta">
    최신 <?= $total ?>건 표시 &nbsp;|&nbsp;
    마지막 갱신: <?= $now ?> &nbsp;|&nbsp;
    30초마다 자동 새로고침
</p>

<?php if ($total > 0):
    $latest = $rows[0]; ?>
<!-- ── 최신 값 카드 ───────────────────────────────────────── -->
<div class="cards">
    <div class="card temp">
        <div class="label">온도 (Temperature)</div>
        <div class="value"><?= $latest['temperature'] ?> °C</div>
    </div>
    <div class="card hum">
        <div class="label">습도 (Humidity)</div>
        <div class="value"><?= $latest['humidity'] ?> %</div>
    </div>
    <div class="card pres">
        <div class="label">기압 (Pressure)</div>
        <div class="value"><?= $latest['pressure'] ?> hPa</div>
    </div>
</div>

<!-- ── 온도 차트 ──────────────────────────────────────────── -->
<div class="chart-wrapper">
    <h2>온도 추이</h2>
    <canvas id="tempChart" height="80"></canvas>
</div>

<!-- ── 습도 / 기압 차트 ───────────────────────────────────── -->
<div class="chart-wrapper">
    <h2>습도 &amp; 기압 추이</h2>
    <canvas id="humPresChart" height="80"></canvas>
</div>

<!-- ── 데이터 테이블 ──────────────────────────────────────── -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>온도 (°C)</th>
            <th>습도 (%)</th>
            <th>기압 (hPa)</th>
            <th>측정 시각</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= $r['temperature'] ?></td>
            <td><?= $r['humidity'] ?></td>
            <td><?= $r['pressure'] ?></td>
            <td><?= $r['recorded_at'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
const labels  = <?= $j_labels ?>;
const temps   = <?= $j_temps  ?>;
const hums    = <?= $j_hums   ?>;
const pres    = <?= $j_pres   ?>;

const chartOpts = (yLabel) => ({
    responsive: true,
    plugins: { legend: { labels: { color: '#ccc' } } },
    scales: {
        x: { ticks: { color: '#888', maxTicksLimit: 10 }, grid: { color: '#2a2d3e' } },
        y: { ticks: { color: '#888' }, grid: { color: '#2a2d3e' }, title: { display: true, text: yLabel, color: '#aaa' } }
    }
});

// 온도 차트
new Chart(document.getElementById('tempChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: '온도 (°C)',
            data: temps,
            borderColor: '#ff6b6b',
            backgroundColor: 'rgba(255,107,107,0.15)',
            tension: 0.3,
            fill: true,
            pointRadius: 3,
        }]
    },
    options: chartOpts('°C')
});

// 습도 + 기압 차트
new Chart(document.getElementById('humPresChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: '습도 (%)',
                data: hums,
                borderColor: '#48dbfb',
                backgroundColor: 'rgba(72,219,251,0.1)',
                tension: 0.3,
                fill: false,
                pointRadius: 3,
                yAxisID: 'y',
            },
            {
                label: '기압 (hPa)',
                data: pres,
                borderColor: '#ffd32a',
                backgroundColor: 'rgba(255,211,42,0.1)',
                tension: 0.3,
                fill: false,
                pointRadius: 3,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#ccc' } } },
        scales: {
            x:  { ticks: { color: '#888', maxTicksLimit: 10 }, grid: { color: '#2a2d3e' } },
            y:  { ticks: { color: '#48dbfb' }, grid: { color: '#2a2d3e' }, position: 'left',
                  title: { display: true, text: '습도 (%)', color: '#48dbfb' } },
            y1: { ticks: { color: '#ffd32a' }, grid: { drawOnChartArea: false }, position: 'right',
                  title: { display: true, text: '기압 (hPa)', color: '#ffd32a' } }
        }
    }
});
</script>

<?php else: ?>
<p style="color:#f66; margin-top:20px;">데이터가 없습니다. injector.py 를 먼저 실행하세요.</p>
<?php endif; ?>
</body>
</html>
