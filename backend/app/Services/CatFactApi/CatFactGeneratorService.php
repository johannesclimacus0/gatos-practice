<?php

namespace App\Services\CatFactApi;

use App\Services\AbstractGuzzleApiService;

class CatFactGeneratorService extends AbstractGuzzleApiService
{
    protected function baseUrl(): string
    {
        return 'https://catfact.ninja';
    }

    public function generateFacts(int $amount, int $maxLength = 180): ?array
    {
        $amount = max(1, $amount);
        $maxLength = max(1, $maxLength);
        $facts = [];
        $attempts = 0;
        $maxAttempts = $amount * 3;

        while (count($facts) < $amount && $attempts < $maxAttempts) {
            $attempts++;

            $response = $this->get('/fact', [
                'max_length' => $maxLength,
            ]);

            if ($response === null || !isset($response['fact'])) {
                continue;
            }

            $facts[] = $response;
        }

        return $facts === [] ? null : $facts;
    }
}