<?php
// api/manager/get_classes_by_teacher.php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../modules/Logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Logger::log("[get_classes_by_teacher] Invalid request method: {$_SERVER['REQUEST_METHOD']} from IP: {$_SERVER['REMOTE_ADDR']}");
    http_response_code(405);
    echo json_encode(['error' => 'Phương thức không hợp lệ']);
    exit;
}

$teacher_id = $_GET['teacher_id'] ?? '';
if (!$teacher_id) {
    Logger::log("[get_classes_by_teacher] Thiếu teacher_id từ IP: {$_SERVER['REMOTE_ADDR']}");
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu teacher_id']);
    exit;
}

$sql = "SELECT class_id, class_name, course_id, semester FROM classes WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    Logger::log("[get_classes_by_teacher] Lỗi prepare SQL: $sql - teacher_id=$teacher_id");
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn']);
    exit;
}
$stmt->bind_param('s', $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode($classes); 