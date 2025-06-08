<?php

namespace App\Services;

use App\Models\Url;

class UrlCleanupService
{
    /**
     * Get all expired URLs
     */
    public function getExpiredUrls(): array
    {
        return Url::expired()->get()->toArray();
    }

    /**
     * Delete all expired URLs
     */
    public function deleteExpiredUrls(): int
    {
        return Url::expired()->delete();
    }

    /**
     * Get the count of expired URLs without loading them
     */
    public function getExpiredUrlsCount(): int
    {
        return Url::expired()->count();
    }
}
