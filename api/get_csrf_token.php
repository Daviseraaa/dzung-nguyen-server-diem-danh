<?php
// api/get_csrf_token.php
require_once __DIR__ . '/../modules/Session.php';
require_once __DIR__ . '/../modules/CSRF.php';
require_once __DIR__ . '/../modules/Response.php';

// Khởi động session
Session::start();

// Tạo và trả về CSRF token
Response::json(["token" => CSRF::generate()]);