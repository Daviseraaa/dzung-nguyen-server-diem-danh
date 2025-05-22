<?php
// api/student/studentAttendanceSummary.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../modules/Session.php';
require_once __DIR__ . '/../../modules/Response.php';

Session::start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    Response::json(["error" => "Unauthorized"], 403);
    exit;
}

$student_id = $_SESSION['student_id'] ?? '';
if (empty($student_id)) {
    Response::json(["error" => "Invalid session"], 400);
    exit;
}

// Lấy danh sách lớp mà sinh viên đã đăng ký
$stmt = $conn->prepare("
    SELECT c.class_id, c.class_name
    FROM class_registrations cr
    JOIN classes c ON cr.class_id = c.class_id
    WHERE cr.student_id = ?
");
if (!$stmt) {
    Response::json(["error" => "Database error", "details" => $conn->error], 500);
    exit;
}
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$classes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Lấy lịch sử chuyên cần cho từng lớp
$summary = [];
$currentDate = date('Y-m-d');
foreach ($classes as $class) {
    $class_id = $class['class_id'];
    $stmt = $conn->prepare("
        SELECT cs.day_of_week, cs.start_time, cs.end_time, cs.room, cs.start_date, cs.end_date
        FROM class_schedules cs
        WHERE cs.class_id = ?
    ");
    $stmt->bind_param("s", $class_id);
    $stmt->execute();
    $schedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $sessions = [];
    foreach ($schedules as $schedule) {
        $dayOfWeek = date('w', strtotime($schedule['start_date']));
        $currentDay = strtotime($schedule['start_date']);
        while ($currentDay <= strtotime($currentDate) && $currentDay <= strtotime($schedule['end_date'])) {
            if (date('w', $currentDay) == array_search($schedule['day_of_week'], ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])) {
                $sessionDate = date('Y-m-d', $currentDay);
                $stmt = $conn->prepare("
                    SELECT status
                    FROM attendance
                    WHERE student_id = ? AND class_id = ? AND DATE(checkin_time) = ? AND TIME(checkin_time) BETWEEN ? AND ?
                ");
                $startTime = $schedule['start_time'];
                $endTime = $schedule['end_time'];
                $stmt->bind_param("sssss", $student_id, $class_id, $sessionDate, $startTime, $endTime); // Sửa thành 'sssss'
                $stmt->execute();
                $status = $stmt->get_result()->fetch_assoc()['status'] ?? null;
                $sessions[] = ['date' => $sessionDate, 'status' => $status ? ($status === 'on_time' ? 'Đi học' : 'Muộn') : 'Vắng'];
                $stmt->close();
            }
            $currentDay = strtotime('+1 day', $currentDay);
        }
    }
    $summary[] = ['class_id' => $class_id, 'class_name' => $class['class_name'], 'sessions' => $sessions];
}

Response::json(["success" => true, "summary" => $summary]);
?>