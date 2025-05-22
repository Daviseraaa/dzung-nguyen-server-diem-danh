<?php
// modules/Auth.php
require_once __DIR__ . '/../config/config.php';

class Auth {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT user_id, username, password, role, student_id, teacher_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            Logger::log("Login failed for username: $username");
            return false;
        }

        return $user;
    }
}