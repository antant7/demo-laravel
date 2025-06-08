<?php

namespace App\Http\Controllers;

use App\Services\UrlShortenerService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectController extends Controller
{
    public function __construct(
        private UrlShortenerService $urlShortenerService
    ) {}

    public function redirectToUrl(string $shortCode): RedirectResponse
    {
        $url = $this->urlShortenerService->getOriginalUrl($shortCode);

        if (!$url) {
            throw new NotFoundHttpException('Link not found or expired');
        }

        return redirect($url->original_url);
    }
}
