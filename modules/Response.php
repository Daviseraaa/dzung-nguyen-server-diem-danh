<?php
// modules/Response.php
class Response {
    public static function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
}