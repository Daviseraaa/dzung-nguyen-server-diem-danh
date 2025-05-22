<?php
// api/manager/get_class_dates.php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../modules/Logger.php'; // Bổ sung logger

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Logger::log("[get_class_dates] Invalid request method: {$_SERVER['REQUEST_METHOD']} from IP: {$_SERVER['REMOTE_ADDR']}");
    http_response_code(405);
    echo json_encode(['error' => 'Phương thức không hợp lệ']);
    exit;
}

$class_id = $_GET['class_id'] ?? '';
if (!$class_id) {
    Logger::log("[get_class_dates] Thiếu class_id từ IP: {$_SERVER['REMOTE_ADDR']}");
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu class_id']);
    exit;
}

// Lấy tất cả các ngày học dựa vào lịch học (class_schedules)
$sql = "SELECT start_date, end_date, day_of_week FROM class_schedules WHERE class_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    Logger::log("[get_class_dates] Lỗi prepare SQL: $sql - class_id=$class_id");
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn']);
    exit;
}
$stmt->bind_param('s', $class_id);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
$today = new DateTime();
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['start_date']);
    $end = new DateTime($row['end_date']);
    // Nếu end_date > hôm nay thì lấy đến hôm nay
    if ($end > $today) {
        $end = clone $today;
    }
    $dow = $row['day_of_week'];
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
    foreach ($period as $date) {
        if ($date->format('l') === $dow) {
            $dates[$date->format('Y-m-d')] = true;
        }
    }
}
$stmt->close();
$conn->close();

// Trả về mảng các ngày học, đã sort tăng dần
$dates = array_keys($dates);
sort($dates);
echo json_encode($dates); 