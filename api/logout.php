<?php
// api/logout.php
require_once __DIR__ . '/../modules/Session.php';
require_once __DIR__ . '/../modules/Logger.php';
require_once __DIR__ . '/../modules/Response.php';

// Thiết lập security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Khởi động session
Session::start();

// Hủy session
Session::destroy();

// Ghi log
Logger::log("User logged out successfully from IP: " . $_SERVER['REMOTE_ADDR']);

// Chuyển hướng
Response::redirect("/server_diem_danh/public/login/login.html");