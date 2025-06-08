<?php

namespace Tests\Unit\Services;

use App\Models\Url;
use App\Services\UrlCleanupService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlCleanupServiceTest extends TestCase
{
    use RefreshDatabase;

    private UrlCleanupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UrlCleanupService();

        // Clear all URLs before each test
        Url::truncate();
    }

    public function test_get_expired_urls_returns_only_expired_urls(): void
    {
        // Create expired URLs
        Url::create([
            'original_url' => 'https://expired1.com',
            'short_code' => 'exp1',
            'expires_at' => Carbon::now()->subDay()
        ]);

        Url::create([
            'original_url' => 'https://expired2.com',
            'short_code' => 'exp2',
            'expires_at' => Carbon::now()->subHour()
        ]);

        // Create non-expired URL
        Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $expiredUrls = $this->service->getExpiredUrls();

        $this->assertCount(2, $expiredUrls);
        $this->assertEquals('exp1', $expiredUrls[0]['short_code']);
        $this->assertEquals('exp2', $expiredUrls[1]['short_code']);
    }

    public function test_get_expired_urls_returns_empty_array_when_no_expired_urls(): void
    {
        // Create only non-expired URLs
        Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $expiredUrls = $this->service->getExpiredUrls();

        $this->assertEmpty($expiredUrls);
    }

    public function test_delete_expired_urls_removes_only_expired_urls(): void
    {
        // Create expired URL
        $expiredUrl = Url::create([
            'original_url' => 'https://expired.com',
            'short_code' => 'expired',
            'expires_at' => Carbon::now()->subDay()
        ]);

        // Create non-expired URL
        $validUrl = Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $deletedCount = $this->service->deleteExpiredUrls();

        $this->assertEquals(1, $deletedCount);
        $this->assertNull(Url::find($expiredUrl->id));
        $this->assertNotNull(Url::find($validUrl->id));
    }

    public function test_delete_expired_urls_returns_zero_when_no_expired_urls(): void
    {
        // Create only non-expired URL
        Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $deletedCount = $this->service->deleteExpiredUrls();

        $this->assertEquals(0, $deletedCount);
    }

    public function test_get_expired_urls_count_returns_correct_count(): void
    {
        // Create expired URLs
        Url::create([
            'original_url' => 'https://expired1.com',
            'short_code' => 'exp1',
            'expires_at' => Carbon::now()->subDay()
        ]);

        Url::create([
            'original_url' => 'https://expired2.com',
            'short_code' => 'exp2',
            'expires_at' => Carbon::now()->subHour()
        ]);

        // Create non-expired URL
        Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $count = $this->service->getExpiredUrlsCount();

        $this->assertEquals(2, $count);
    }

    public function test_get_expired_urls_count_returns_zero_when_no_expired_urls(): void
    {
        // Ensure database is clean
        Url::truncate();

        // Create only non-expired URL
        Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $count = $this->service->getExpiredUrlsCount();

        $this->assertEquals(0, $count);
    }
}
