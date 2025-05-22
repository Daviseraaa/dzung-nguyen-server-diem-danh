<?php
// api/manager/manual_attendance_submit.php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../modules/Logger.php'; // Bổ sung logger

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Logger::log("[manual_attendance_submit] Invalid request method: {$_SERVER['REQUEST_METHOD']} from IP: {$_SERVER['REMOTE_ADDR']}");
    http_response_code(405);
    echo json_encode(['error' => 'Phương thức không hợp lệ']);
    exit;
}

// Lấy và kiểm tra dữ liệu đầu vào
$data = json_decode(file_get_contents('php://input'), true);
$class_id = $data['class_id'] ?? '';
$date = $data['date'] ?? '';
$students = $data['students'] ?? [];

if (!$class_id || !$date || !is_array($students) || count($students) === 0) {
    Logger::log("[manual_attendance_submit] Thiếu dữ liệu đầu vào: " . json_encode($data));
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu dữ liệu đầu vào']);
    exit;
}

// Lấy room từ class_schedules theo class_id và date
$sql = "SELECT room FROM class_schedules WHERE class_id = ? AND ? BETWEEN start_date AND end_date AND day_of_week = ? LIMIT 1";
$day_of_week = date('l', strtotime($date));
$stmt = $conn->prepare($sql);
if (!$stmt) {
    Logger::log("[manual_attendance_submit] Lỗi prepare SQL (room): $sql - Params: [$class_id, $date, $day_of_week]");
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn phòng học']);
    exit;
}
$stmt->bind_param('sss', $class_id, $date, $day_of_week);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$room = $row['room'] ?? null;
$stmt->close();

if (!$room) {
    Logger::log("[manual_attendance_submit] Không tìm thấy phòng học cho class_id=$class_id, date=$date");
    http_response_code(400);
    echo json_encode(['error' => 'Không tìm thấy phòng học cho ngày này']);
    exit;
}

// Ghi điểm danh cho từng sinh viên
$success = 0;
$success_students = [];
$failed_students = [];
foreach ($students as $sv) {
    $student_id = $sv['student_id'] ?? '';
    if (!$student_id) continue;
    // Kiểm tra đã có bản ghi chưa (tránh trùng lặp)
    $sql = "SELECT attendance_id FROM attendance WHERE student_id = ? AND class_id = ? AND DATE(checkin_time) = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        Logger::log("[manual_attendance_submit] Lỗi prepare SQL (check exist): $sql - Params: [$student_id, $class_id, $date]");
        $failed_students[] = [
            'student_id' => $student_id,
            'reason' => 'Lỗi truy vấn kiểm tra điểm danh'
        ];
        continue;
    }
    $stmt->bind_param('sss', $student_id, $class_id, $date);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $failed_students[] = [
            'student_id' => $student_id,
            'reason' => 'Đã có thông tin điểm danh'
        ];
        continue; // Đã có điểm danh
    }
    $stmt->close();
    // Ghi mới điểm danh
    $sql = "INSERT INTO attendance (student_id, class_id, room, checkin_time, status) VALUES (?, ?, ?, ?, 'on_time')";
    $stmt = $conn->prepare($sql);
    $checkin_time = $date . ' 08:00:00'; // Giả định giờ checkin mặc định
    if (!$stmt) {
        Logger::log("[manual_attendance_submit] Lỗi prepare SQL (insert): $sql - Params: [$student_id, $class_id, $room, $checkin_time]");
        $failed_students[] = [
            'student_id' => $student_id,
            'reason' => 'Lỗi ghi dữ liệu'
        ];
        continue;
    }
    if ($stmt->bind_param('ssss', $student_id, $class_id, $room, $checkin_time) && $stmt->execute()) {
        $success++;
        $success_students[] = $student_id;
    } else {
        Logger::log("[manual_attendance_submit] Lỗi execute insert: Params: [$student_id, $class_id, $room, $checkin_time]");
        $failed_students[] = [
            'student_id' => $student_id,
            'reason' => 'Lỗi ghi dữ liệu'
        ];
    }
    $stmt->close();
}
$conn->close();

// Trả về kết quả
if ($success > 0) {
    $msg = '';
    if (count($success_students) > 0) {
        $msg .= 'Điểm danh thành công: ' . implode(', ', $success_students) . "\n";
    }
    if (count($failed_students) > 0) {
        $msg .= 'Điểm danh không thành công: ' .
            implode(', ', array_map(function($f) { return $f['student_id'] . '. ' . $f['reason']; }, $failed_students));
    }
    echo json_encode([
        'success' => true,
        'success_students' => $success_students,
        'failed_students' => $failed_students,
        'message' => trim($msg)
    ]);
} else {
    $msg = 'Điểm danh không thành công: ' .
        implode(', ', array_map(function($f) { return $f['student_id'] . '. ' . $f['reason']; }, $failed_students));
    Logger::log("[manual_attendance_submit] Không có sinh viên nào điểm danh thành công. Lỗi: $msg");
    http_response_code(400);
    echo json_encode([
        'error' => $msg,
        'success_students' => $success_students,
        'failed_students' => $failed_students
    ]);
} 