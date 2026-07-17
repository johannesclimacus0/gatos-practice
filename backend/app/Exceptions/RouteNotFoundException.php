<?php

namespace App\Exceptions;

class RouteNotFoundException extends \Exception
{
    protected $message = 'Страница не найдена';
}
