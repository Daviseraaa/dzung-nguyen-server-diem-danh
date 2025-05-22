<?php
// Include autoloader của Composer
require_once __DIR__ . '/../phpdotenv_lib/vendor/autoload.php';

use Dotenv\Dotenv;

// Tải file .env từ thư mục
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Lấy các biến từ file .env để kết nối cơ sở dữ liệu
$dbHost = $_ENV['DB_HOST'];
$dbUsername = $_ENV['DB_USERNAME'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbName = $_ENV['DB_NAME'];

// Kiểm tra nếu thiếu biến môi trường
if (!$dbHost || !$dbUsername || !$dbName) {
    http_response_code(500);
    echo json_encode(["error" => "Thiếu thông tin cấu hình cơ sở dữ liệu"]);
    exit;
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Kết nối cơ sở dữ liệu thất bại"]);
    exit;
}