<?php
// api/manager/get_class_attendance_report.php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../modules/Logger.php'; // Bổ sung logger

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Logger::log("[get_class_attendance_report] Invalid request method: {$_SERVER['REQUEST_METHOD']} from IP: {$_SERVER['REMOTE_ADDR']}");
    http_response_code(405);
    echo json_encode(['error' => 'Phương thức không hợp lệ']);
    exit;
}

$class_id = $_GET['class_id'] ?? '';
if (!$class_id) {
    Logger::log("[get_class_attendance_report] Thiếu class_id từ IP: {$_SERVER['REMOTE_ADDR']}");
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu class_id']);
    exit;
}

// Lấy danh sách ngày học
$dates = isset($_GET['dates']) ? $_GET['dates'] : [];
if (!is_array($dates)) {
    $dates = [$dates];
}

// Lấy danh sách sinh viên của lớp
$sql = "SELECT s.student_id, s.full_name FROM class_registrations cr JOIN students s ON cr.student_id = s.student_id WHERE cr.class_id = ? ORDER BY s.full_name ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    Logger::log("[get_class_attendance_report] Lỗi prepare SQL (student list): $sql - class_id=$class_id");
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn sinh viên']);
    exit;
}
$stmt->bind_param('s', $class_id);
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[$row['student_id']] = [
        'student_id' => $row['student_id'],
        'full_name' => $row['full_name'],
        'attendance' => [],
        'total_absent' => 0,
        'total_on_time' => 0,
        'total_late' => 0
    ];
}
$stmt->close();

if (empty($students)) {
    $conn->close();
    echo json_encode([]);
    exit;
}

// Lấy danh sách ngày học nếu chưa truyền
if (empty($dates)) {
    $sql = "SELECT start_date, end_date, day_of_week FROM class_schedules WHERE class_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        Logger::log("[get_class_attendance_report] Lỗi prepare SQL (schedule): $sql - class_id=$class_id");
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi truy vấn lịch học']);
        exit;
    }
    $stmt->bind_param('s', $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dates_map = [];
    $today = new DateTime();
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row['start_date']);
        $end = new DateTime($row['end_date']);
        if ($end > $today) {
            $end = clone $today;
        }
        $dow = $row['day_of_week'];
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
        foreach ($period as $date) {
            if ($date->format('l') === $dow) {
                $dates_map[$date->format('Y-m-d')] = true;
            }
        }
    }
    $dates = array_keys($dates_map);
    sort($dates);
    $stmt->close();
}

// Lấy dữ liệu attendance cho các sinh viên trong lớp, theo các ngày học
if (!empty($dates)) {
    $placeholders = implode(',', array_fill(0, count($dates), '?'));
    $types = str_repeat('s', count($dates) + 1); // class_id + dates
    $params = array_merge([$class_id], $dates);
    $sql = "SELECT student_id, DATE(checkin_time) as date, status FROM attendance WHERE class_id = ? AND DATE(checkin_time) IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        Logger::log("[get_class_attendance_report] Lỗi prepare SQL (attendance): $sql - Params: " . json_encode($params));
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi truy vấn điểm danh']);
        exit;
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sid = $row['student_id'];
        $date = $row['date'];
        $status = $row['status'];
        if (isset($students[$sid])) {
            $students[$sid]['attendance'][$date] = $status;
            if ($status === 'absent') $students[$sid]['total_absent']++;
            if ($status === 'on_time') $students[$sid]['total_on_time']++;
            if ($status === 'late') $students[$sid]['total_late']++;
        }
    }
    $stmt->close();
}
$conn->close();

// Đảm bảo tất cả các ngày học đều có trạng thái (nếu chưa có thì là vắng)
$stt = 1;
foreach ($students as &$sv) {
    foreach ($dates as $date) {
        if (!isset($sv['attendance'][$date])) {
            $sv['attendance'][$date] = 'absent';
            $sv['total_absent']++;
        }
    }
    ksort($sv['attendance']);
    $sv['stt'] = $stt++;
}
unset($sv);

// Sắp xếp lại theo tên (ưu tiên tên cuối cùng, có hỗ trợ tiếng Việt)
function getLastName($fullName) {
    $parts = preg_split('/\s+/', trim($fullName));
    return mb_strtolower(array_pop($parts), 'UTF-8');
}
usort($students, function($a, $b) {
    $nameA = getLastName($a['full_name']);
    $nameB = getLastName($b['full_name']);
    $cmp = strcmp($nameA, $nameB);
    if ($cmp === 0) {
        return strcmp($a['full_name'], $b['full_name']);
    }
    return $cmp;
});
// Gán lại STT sau khi sắp xếp
foreach ($students as $i => &$sv) {
    $sv['stt'] = $i + 1;
}
unset($sv);

echo json_encode(array_values($students)); 