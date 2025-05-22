<?php
// Include file cấu hình (đã tải các biến từ .env)
include_once '../../config/config.php';

// Thiết lập header cho API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

function writeLog($message, $type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = "../logs/" . $type . ".log";
    error_log("$timestamp - $message\n", 3, $logFile);
}

// Hàm ghi log lỗi
function logError($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("$timestamp - $message\n", 3, "../logs/error.log");
}

// Hàm trả về lỗi và dừng chương trình
function sendError($message, $httpCode = 400, $detail = null) {
    http_response_code($httpCode);
    $response = ["error" => $message];
    if ($detail) {
        $response["detail"] = $detail;
    }
    logError($message . ($detail ? " - Detail: $detail" : ""));
    die(json_encode($response));
}

// Nhận dữ liệu từ ESP32
$data = json_decode(file_get_contents('php://input'), true);
$rfid_uid = $data['rfid_uid'] ?? '';
$room = $data['room'] ?? '';

// Kiểm tra dữ liệu đầu vào
if (empty($rfid_uid)) {
    sendError("Thiếu UID thẻ RFID");
}
if (empty($room)) {
    sendError("Thiếu thông tin phòng");
}
// Làm sạch dữ liệu (tránh ký tự không hợp lệ)
$rfid_uid = filter_var($rfid_uid, FILTER_SANITIZE_STRING);
$room = filter_var($room, FILTER_SANITIZE_STRING);

// Truy vấn thông tin sinh viên
$sql = "SELECT student_id, full_name FROM students WHERE rfid_uid = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    sendError("Lỗi chuẩn bị truy vấn", 500, $conn->error);
}

if (!$stmt->bind_param("s", $rfid_uid)) {
    sendError("Lỗi ràng buộc tham số", 500, $stmt->error);
}

if (!$stmt->execute()) {
    sendError("Lỗi thực thi truy vấn", 500, $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    sendError("Lỗi nhận kết quả", 500, $stmt->error);
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $student_id = $row['student_id'];
    $full_name = strtoupper($row['full_name']);

    // Ghi dữ liệu điểm danh
    $sql = "INSERT INTO attendance (student_id, checkin_time, room, class_id, status) VALUES (?, NOW(), ?, NULL, NULL)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendError("Lỗi chuẩn bị truy vấn điểm danh", 500, $conn->error);
    }

    if (!$stmt->bind_param("ss", $student_id, $room)) {
        sendError("Lỗi ràng buộc tham số điểm danh", 500, $stmt->error);
    }

    if (!$stmt->execute()) {
        sendError("Lỗi ghi dữ liệu điểm danh", 500, $stmt->error);
    }

    // Phản hồi thành công
    http_response_code(200);
    echo json_encode([
        "full_name" => $full_name,
        "student_id" => $student_id,
        "message" => "ĐIỂM DANH THÀNH CÔNG"
    ]);
} else {
    sendError("SINH VIEN KHONG TON TAI", 404);
}

// Đóng tài nguyên
$stmt->close();
$conn->close();
?> 