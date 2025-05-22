<?php
// api/student/studentAttendance.php
require_once __DIR__ . '/../../config/config.php'; // Kết nối database
require_once __DIR__ . '/../../modules/Session.php';
require_once __DIR__ . '/../../modules/Response.php';

// Khởi động session
Session::start();

// Kiểm tra quyền sinh viên
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    Response::json(["error" => "Unauthorized"], 403);
    exit;
}

// Lấy student_id từ session
$student_id = $_SESSION['student_id'] ?? '';
if (empty($student_id)) {
    Response::json(["error" => "Invalid session"], 400);
    exit;
}

// Xử lý các action
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'rooms':
        // Sử dụng prepared statement thay vì $conn->query()
        $stmt = $conn->prepare("SELECT DISTINCT room FROM attendance WHERE student_id = ?");
        if (!$stmt) {
            Response::json(["error" => "Database error", "details" => $conn->error], 500);
            exit;
        }
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rooms = $result->fetch_all(MYSQLI_ASSOC);
        $rooms = array_column($rooms, 'room');
        Response::json(["success" => true, "rooms" => $rooms]);
        $stmt->close();
        break;

    default:
        // Xử lý lấy lịch sử điểm danh (mặc định)
        $filters = json_decode(file_get_contents('php://input'), true) ?? [];
        $query = "SELECT attendance_id, student_id, checkin_time, room 
                  FROM attendance 
                  WHERE student_id = ?";
        $params = [$student_id];
        $types = "s";

        if (!empty($filters['date'])) {
            $query .= " AND DATE(checkin_time) = ?";
            $params[] = $filters['date'];
            $types .= "s";
        }
        if (!empty($filters['startTime']) && !empty($filters['endTime'])) {
            $startTime = strtotime($filters['startTime']);
            $endTime = strtotime($filters['endTime']);
            if ($startTime === false || $endTime === false || $startTime >= $endTime) {
                Response::json(["error" => "Khoảng thời gian không hợp lệ"], 400);
                exit;
            }
            $query .= " AND TIME(checkin_time) BETWEEN ? AND ?";
            $params[] = $filters['startTime'];
            $params[] = $filters['endTime'];
            $types .= "ss";
        }
        if (!empty($filters['room'])) {
            $query .= " AND room = ?";
            $params[] = $filters['room'];
            $types .= "s";
        }

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            Response::json(["error" => "Database error", "details" => $conn->error], 500);
            exit;
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = $result->fetch_all(MYSQLI_ASSOC);

        Response::json(["success" => true, "attendance" => $attendance]);
        $stmt->close();
        break;
}
?>