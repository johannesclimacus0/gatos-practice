<?php
declare(strict_types=1);

namespace App\Exceptions;

class CatNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('no gato :(');
    }
}