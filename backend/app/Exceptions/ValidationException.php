<?php
declare(strict_types=1);

namespace App\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(private array $errors) {
        parent::__construct('Ошибка валидации');
    }
    public function errors(): array
    {
        return $this->errors;
    }
}