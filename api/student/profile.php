<?php
// api/student/profile.php
require_once __DIR__ . '/../../modules/Session.php';
require_once __DIR__ . '/../../modules/Response.php';
require_once __DIR__ . '/../../config/config.php';

// Khởi động session
Session::start();

// Kiểm tra quyền sinh viên
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    Response::json(["error" => "Unauthorized"], 403);
    exit;
}

// Lấy student_id từ session
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    Response::json(["success" => false, "error" => "Session does not contain student_id"], 401);
    exit;
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    Response::json(["success" => false, "error" => "Database connection failed"], 500);
    exit;
}

try {
    // Truy vấn chỉ các trường cần thiết: student_id, full_name, rfid_uid
    $stmt = $conn->prepare("SELECT student_id, full_name, rfid_uid FROM students WHERE student_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        Response::json(["success" => false, "error" => "Sinh viên không tồn tại"], 404);
    }

    $student = $result->fetch_assoc();
    Response::json(["success" => true, "student" => $student], 200);

} catch (Exception $e) {
    Response::json(["success" => false, "error" => "Lỗi server: " . $e->getMessage()], 500);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>