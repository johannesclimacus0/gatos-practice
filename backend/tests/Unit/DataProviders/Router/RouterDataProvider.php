<?php

namespace Tests\Unit\DataProviders\Router;

class RouterDataProvider
{
    public static function pageNotFoundCases(): array
    {
        return [
            'зарегистрированный метод с неизвестным путем' => ['DELETE', '/pong'],
            'неизвестный путь GET' => ['GET', '/missing'],
            'неизвестный путь POST' => ['POST', '/missing'],
        ];
    }
}