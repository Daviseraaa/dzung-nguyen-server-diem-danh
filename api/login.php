<?php
// api/login.php
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../modules/Response.php';
require_once __DIR__ . '/../modules/CSRF.php';
require_once __DIR__ . '/../modules/RateLimiter.php';
require_once __DIR__ . '/../modules/Logger.php';
require_once __DIR__ . '/../modules/Auth.php';

// Thiết lập error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Thiết lập security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Kiểm tra session hiện tại
checkSessionAndRespond(true);

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(["error" => "Method Not Allowed", "details" => "Only POST method is allowed"], 405);
}

// Kiểm tra Content-Type
if (!isset($_SERVER['CONTENT_TYPE'])) {
    Response::json(["error" => "Unsupported Media Type", "details" => "Content-Type must be set"], 415);
}

// Đọc dữ liệu đầu vào
if (stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        Response::json(["error" => "Invalid JSON", "details" => json_last_error_msg()], 400);
    }
} elseif (stripos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false) {
    $input = $_POST;
} else {
    Response::json(["error" => "Unsupported Media Type", "details" => "Content-Type must be application/json or application/x-www-form-urlencoded"], 415);
}

// Kiểm tra CSRF token
if (!isset($input['csrf_token']) || !CSRF::validate($input['csrf_token'])) {
    Response::json(["error" => "Invalid CSRF token", "details" => "CSRF token validation failed"], 403);
}

// Validate và sanitize input
$username = isset($input['username']) ? trim(strip_tags($input['username'])) : null;
$password = $input['password'] ?? null;

if (empty($username) || empty($password)) {
    Response::json(["error" => "Bad Request", "details" => "Username and password are required"], 400);
}

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
if (!RateLimiter::check($ip)) {
    Response::json([
        "error" => "Too Many Requests",
        "details" => "Account temporarily locked. Try again after 5 minutes."
    ], 429);
}

// Xử lý đăng nhập
require_once __DIR__ . '/../config/config.php'; // $conn được định nghĩa trong config.php
$auth = new Auth($conn);
$user = $auth->login($username, $password);

if (!$user) {
    $attemptsRemaining = RateLimiter::increment($ip);
    Response::json([
        "error" => "Unauthorized",
        "details" => "Invalid username or password",
        "attempts_remaining" => $attemptsRemaining
    ], 401);
}

// Đăng nhập thành công
Session::setUser($user);
CSRF::regenerate();
RateLimiter::reset($ip);
Logger::log("User {$user['username']} logged in successfully.");

Response::json([
    "message" => "Login successful",
    "role" => $user['role'],
    "redirect" => getDashboardUrl($user['role'])
], 200);