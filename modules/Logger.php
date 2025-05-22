<?php
// modules/Logger.php
class Logger {
    public static function log($message) {
        error_log("[" . date('Y-m-d H:i:s') . "] $message\n", 3, __DIR__ . '/../logs/error.log');
    }
}