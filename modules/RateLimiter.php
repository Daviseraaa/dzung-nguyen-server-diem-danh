<?php
// modules/RateLimiter.php
class RateLimiter {
    private static $maxAttempts = 5;
    private static $lockoutDuration = 300; // 5 phút

    public static function check($key) {
        $rateLimitKey = 'rate_limit_' . md5($key);
        $attempts = $_SESSION[$rateLimitKey] ?? 0;

        if ($attempts >= self::$maxAttempts) {
            return false;
        }

        return true;
    }

    public static function increment($key) {
        $rateLimitKey = 'rate_limit_' . md5($key);
        $_SESSION[$rateLimitKey] = ($_SESSION[$rateLimitKey] ?? 0) + 1;
        return self::$maxAttempts - $_SESSION[$rateLimitKey];
    }

    public static function reset($key) {
        $rateLimitKey = 'rate_limit_' . md5($key);
        unset($_SESSION[$rateLimitKey]);
    }
}