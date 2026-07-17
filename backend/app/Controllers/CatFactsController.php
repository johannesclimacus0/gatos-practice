<?php

namespace App\Controllers;

use App\Controller;
use App\Http\Request;
use App\Services\CatFactApi\CatFactGeneratorService;
use App\Services\LibreTranslateApi\TranslatorService;

class CatFactsController extends Controller
{
    public function __construct(
        private CatFactGeneratorService $catFactGeneratorService,
        private TranslatorService $translatorService
    ) {}

    public function get(Request $request): void
    {
        $amount = max(1, min(10, (int) $request->route('amount', 1)));
        $length = max(1, min(500, (int) $request->route('length', 180)));

        $facts = $this->catFactGeneratorService->generateFacts($amount, $length);
        if ($facts === null) {
            $this->json(['error' => 'Ошибка на стороне Cat Fact API'], 503);
            return;
        }

        $texts = [];
        foreach ($facts as $fact) {
            if (!isset($fact['fact']) || !is_string($fact['fact'])) {
                continue;
            }
            $texts[] = $fact['fact'];
        }

        if (empty($texts)) {
            $this->json(['error' => ''], 400);
            return;
        }

        $translated = $this->translatorService->translateBatch($texts);

        if ($translated === null) {

            $this->json(['error' => 'Translation service error'], 502);
            return;
        }

        $this->json(['facts'  => $translated, 'length' => $length]);
    }
}
