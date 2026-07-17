<?php

namespace App\Services;

class CsrfService
{
    private const SESSION_KEY = 'csrf_token';

    public function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public function isValid(?string $token): bool
    {
        return is_string($token) && $token !== '' && hash_equals($this->token(), $token);
    }
}
