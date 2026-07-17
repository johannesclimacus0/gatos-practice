<?php

namespace App\Validation;

use App\Exceptions\ValidationException;

class CatValidator
{
    private const MAX_LENGTHS = [
        'name' => 40,
        'lang' => 20,
    ];

    public function validateForCreate(array $data): array
    {
        $errors = [];
        $name = $this->validateString($data, 'name', self::MAX_LENGTHS['name'], $errors);
        $lang = $this->validateString($data, 'lang', self::MAX_LENGTHS['lang'], $errors);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
        return [
            'name' => $name,
            'lang' => $lang,
        ];
    }

    public function validateForUpdate(array $data): array
    {
        if (!array_key_exists('name', $data) && !array_key_exists('lang', $data)) {
            throw new ValidationException([
                'data' => 'Нужно передать имя и/или язык',
            ]);
        }
        $errors = [];
        $validated = [];

        if (array_key_exists('name', $data)) {
            $validated['name'] = $this->validateString($data, 'name', self::MAX_LENGTHS['name'], $errors);
        }
        if (array_key_exists('lang', $data)) {
            $validated['lang'] = $this->validateString($data, 'lang', self::MAX_LENGTHS['lang'], $errors);
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $validated;
    }

    private function validateString(array $data, string $field, int $maxLength, array &$errors): string {
        if (!array_key_exists($field, $data)) {
            $errors[$field] = 'Поле обязательно';
            return '';
        }
        if (!is_string($data[$field])) {
            $errors[$field] = 'Значение должно быть строкой';
            return '';
        }

        $value = trim($data[$field]);

        if ($value === '') {
            $errors[$field] = 'Значение не может быть пустым';
            return '';
        }
        if (mb_strlen($value) > $maxLength) {
            $errors[$field] =
                "Максимальная длина: {$maxLength} символов";

            return '';
        }

        return $value;
    }
}