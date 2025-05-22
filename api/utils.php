<?php
// api/utils.php

/**
 * Lấy URL dashboard dựa trên vai trò người dùng.
 * @param string $role Vai trò của người dùng (admin, manager, student)
 * @return string URL dashboard tương ứng
 */
function getDashboardUrl($role) {
    $map = [
        'admin' => '/server_diem_danh/public/admin/adminDashboard.html',
        'manager' => '/server_diem_danh/public/manager/managerDashboard.html',
        'student' => '/server_diem_danh/public/student/studentDashboard.html'
    ];
    return $map[$role] ?? '/';
}

/**
 * Kiểm tra session và trả về dữ liệu JSON.
 * @param bool $includeRole Có bao gồm thông tin role trong response không
 * @return void
 */
function checkSessionAndRespond($includeRole = false) {
    require_once __DIR__ . '/../modules/Session.php';
    require_once __DIR__ . '/../modules/Response.php';

    Session::start();

    if (Session::check()) {
        $response = [
            "logged_in" => true,
            "redirect" => getDashboardUrl($_SESSION['role'])
        ];
        if ($includeRole) {
            $response["role"] = $_SESSION['role'];
            $response["user_id"] = $_SESSION['user_id'];
            $response["username"] = $_SESSION['username'];
            if (isset($_SESSION['teacher_id'])) {
                $response["teacher_id"] = $_SESSION['teacher_id'];
            }
        }
        Response::json($response);
        exit;
    }
}