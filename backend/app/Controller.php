<?php

namespace App;

class Controller
{
    protected function json(array $data, int $status = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}