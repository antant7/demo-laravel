<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UrlShortenerService;
use App\Services\UrlValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="URLs",
 *     description="URL shortening operations"
 * )
 */
class UrlController extends Controller
{
    public function __construct(
        private UrlShortenerService $urlShortenerService,
        private UrlValidationService $urlValidationService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/shorten",
     *     summary="Create short link",
     *     tags={"URLs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="url", type="string", format="url", example="https://example.com"),
     *             @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, example="2055-01-01T23:59:59Z"),
     *             @OA\Property(property="custom_alias", type="string", nullable=true, example="my-custom-link", description="Custom alias for short link (a-zA-Z0-9-_)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Short link created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="original_url", type="string"),
     *             @OA\Property(property="short_code", type="string"),
     *             @OA\Property(property="short_url", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="expires_at", type="string", format="date-time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid data")
     * )
     */
    public function shortenUrl(Request $request): JsonResponse
    {
        $data = $request->all();

        // Validate URL data
        $errors = $this->urlValidationService->validateUrlData($data);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $expiresAt = $this->urlValidationService->parseExpiresAt($data['expires_at'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        $customAlias = $data['custom_alias'] ?? null;

        try {
            $url = $this->urlShortenerService->shortenUrl($data['url'], $expiresAt, $customAlias);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'id' => $url->id,
            'original_url' => $url->original_url,
            'short_code' => $url->short_code,
            'short_url' => $request->getSchemeAndHttpHost() . '/' . $url->short_code,
            'created_at' => $url->created_at->toISOString(),
            'expires_at' => $url->expires_at?->toISOString()
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/urls/{id}/stats",
     *     summary="Get link statistics",
     *     tags={"URLs"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Link statistics",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="original_url", type="string"),
     *             @OA\Property(property="short_code", type="string"),
     *             @OA\Property(property="click_count", type="integer"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="expires_at", type="string", format="date-time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Link not found")
     * )
     */
    public function getUrlStats(int $id): JsonResponse
    {
        $url = $this->urlShortenerService->findUrlById($id);

        if (!$url) {
            return response()->json(['error' => 'URL not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'id' => $url->id,
            'original_url' => $url->original_url,
            'short_code' => $url->short_code,
            'click_count' => $url->click_count,
            'created_at' => $url->created_at->toISOString(),
            'expires_at' => $url->expires_at?->toISOString()
        ]);
    }
}
