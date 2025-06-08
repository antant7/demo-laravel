<?php

namespace App\Services;

use App\Models\Url;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UrlShortenerService
{
    /**
     * Create shortened URL
     */
    public function shortenUrl(string $originalUrl, ?Carbon $expiresAt = null, ?string $customAlias = null): Url
    {
        // If custom_alias is specified, check its uniqueness
        if ($customAlias !== null) {
            // Check existence in DB case-insensitive
            if (Url::whereRaw('LOWER(short_code) = LOWER(?)', [$customAlias])->exists()) {
                throw new \InvalidArgumentException('Custom alias is already in use');
            }

            // Create URL with custom alias
            return Url::create([
                'original_url' => $originalUrl,
                'short_code' => $customAlias,
                'expires_at' => $expiresAt
            ]);
        }

        // Standard short_code generation logic
        return DB::transaction(function () use ($originalUrl, $expiresAt) {
            // Create URL with temporary shortCode
            $url = Url::create([
                'original_url' => $originalUrl,
                'short_code' => uniqid(),
                'expires_at' => $expiresAt
            ]);

            // Generate shortCode based on ID
            $shortCode = $this->generateShortCodeFromId($url->id);

            // Update with correct shortCode
            $url->update(['short_code' => $shortCode]);

            return $url->fresh();
        });
    }

    /**
     * Get original URL and increment click count
     */
    public function getOriginalUrl(string $shortCode): ?Url
    {
        $url = Url::where('short_code', $shortCode)->notExpired()->first();

        if ($url === null) {
            return null;
        }

        $url->incrementClickCount();

        return $url;
    }

    /**
     * Find URL by ID
     */
    public function findUrlById(int $id): ?Url
    {
        return Url::find($id);
    }

    /**
     * Generates short code based on ID using base division
     * Guarantees uniqueness since each ID is unique
     */
    private function generateShortCodeFromId(int $id): string
    {
        $shortCode = '';
        $base = UrlConstants::BASE;
        $chars = UrlConstants::BASE_CHARS;

        while ($id > 0) {
            $shortCode = $chars[$id % $base] . $shortCode;
            $id = intval($id / $base);
        }

        return $shortCode;
    }
}
