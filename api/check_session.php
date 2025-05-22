<?php
// api/check_session.php
require_once __DIR__ . '/utils.php';

// Kiểm tra trạng thái session, trả về cả user_id, username, role nếu hợp lệ
checkSessionAndRespond(true);

Response::json(["logged_in" => false]);