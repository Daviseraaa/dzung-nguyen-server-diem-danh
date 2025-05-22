<?php
// modules/CSRF.php
class CSRF {
    public static function generate() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            Logger::log("CSRF validation failed. Input token: " . ($token ?? 'not set') . ", Session token: " . ($_SESSION['csrf_token'] ?? 'not set'));
            return false;
        }
        return true;
    }

    public static function regenerate() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}