<?php
// modules/Session.php
class Session {
    private static $timeoutDuration = 600; // 10 phút

    public static function start() {
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.gc_maxlifetime', self::$timeoutDuration);
        session_start();
    }

    public static function check() {
        if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
            return false;
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > self::$timeoutDuration) {
            self::destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function setUser($user) {
        session_regenerate_id(true);
        $_SESSION = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'student_id' => $user['student_id'],
            'last_activity' => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        if (isset($user['teacher_id'])) {
            $_SESSION['teacher_id'] = $user['teacher_id'];
        }
    }

    public static function destroy() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }
}