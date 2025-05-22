<?php
// server_diem_danh/api/admin/bulk_add_students.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require_once __DIR__ . '/../../config/config.php'; // Kết nối database
require_once __DIR__ . '/../../modules/Session.php';
require_once __DIR__ . '/../../modules/Response.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['students']) || !is_array($data['students'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$students = $data['students'];
$success_count = 0;
$fail_count = 0;
$errors = [];

$conn->begin_transaction();

try {
    foreach ($students as $student) {
        try {
            $stmt = $conn->prepare("INSERT INTO students (student_id, rfid_uid, full_name, class) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $student['student_id'], $student['rfid_uid'], $student['full_name'], $student['class']);
            $stmt->execute();

            $password_hash = password_hash($student['student_id'], PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, student_id) VALUES (?, ?, 'student', ?)");
            $stmt->bind_param("sss", $student['student_id'], $password_hash, $student['student_id']);
            $stmt->execute();

            $success_count++;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $errors[] = "Sinh viên {$student['student_id']} đã tồn tại.";
                $fail_count++;
            } else {
                throw $e;
            }
        }
    }
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Đã import thành công $success_count sinh viên. $fail_count sinh viên không được import do trùng lặp.",
        'errors' => $errors
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>