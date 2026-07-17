<?php

namespace App\Http;

class Response
{
    public static function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
}