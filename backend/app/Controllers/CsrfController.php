<?php

namespace App\Controllers;

use App\Controller;
use App\Services\CsrfService;

class CsrfController extends Controller
{
    public function __construct(private CsrfService $csrfService){}

    public function token(): void
    {
        $this->json(['token' => $this->csrfService->token()]);
    }
}
