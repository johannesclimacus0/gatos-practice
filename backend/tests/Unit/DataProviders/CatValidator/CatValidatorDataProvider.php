<?php

namespace Tests\Unit\DataProviders\CatValidator;

class CatValidatorDataProvider
{
    public static function emptyValues(): array
    {
        return [
            'empty string' => [''],
            'one space' => [' '],
            'several spaces' => ['     '],
        ];
    }
    public static function invalidTypes(): array
    {
        return [
            'null' => [null],
            'integer' => [123],
            'boolean' => [false],
            'array' => [[]],
            'object' => [new \stdClass()],
        ];
    }
}