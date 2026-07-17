<?php

namespace App\Services\LibreTranslateApi;

use App\Services\AbstractGuzzleApiService;

class TranslatorService extends AbstractGuzzleApiService
{

    protected function baseUrl(): string
    {
        return 'https://api.mymemory.translated.net';
    }
    public function translateBatch(array $texts, string $target = 'ru', string $source = 'en'): ?array
    {
        $texts = array_filter($texts, fn($t) => is_string($t) && trim($t) !== '');
        if (empty($texts)) {
            return [];
        }

        $limitPerBatch = 5;
        $results = [];

        foreach (array_chunk($texts, $limitPerBatch) as $chunk) {
            foreach ($chunk as $text) {
                $data = [
                    'q' => $text,
                    'langpair' => "{$source}|{$target}",
                ];

                $result = $this->get('/get', $data);

                if (!$result || !isset($result['responseData']['translatedText'])) {
                    return null;
                }

                $results[] = $result['responseData']['translatedText'];
            }
            usleep(500000);
        }

        return $results;
    }
}