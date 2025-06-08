<?php

namespace Tests\Unit\Models;

use App\Models\Url;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class UrlTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear all URLs before each test to ensure clean state
        Url::truncate();
    }

    public function test_url_can_be_created_with_required_fields(): void
    {
        $url = Url::create([
            'original_url' => 'https://example.com',
            'short_code' => 'abc123',
            'click_count' => 0
        ]);

        $this->assertInstanceOf(Url::class, $url);
        $this->assertEquals('https://example.com', $url->original_url);
        $this->assertEquals('abc123', $url->short_code);
        $this->assertEquals(0, $url->click_count);
    }

    public function test_is_expired_returns_false_when_expires_at_is_null(): void
    {
        $url = new Url(['expires_at' => null]);

        $this->assertFalse($url->isExpired());
    }

    public function test_is_expired_returns_true_when_expires_at_is_past(): void
    {
        $url = new Url(['expires_at' => Carbon::now()->subDay()]);

        $this->assertTrue($url->isExpired());
    }

    public function test_is_expired_returns_false_when_expires_at_is_future(): void
    {
        $url = new Url(['expires_at' => Carbon::now()->addDay()]);

        $this->assertFalse($url->isExpired());
    }

    public function test_increment_click_count_increases_count_by_one(): void
    {
        $url = Url::create([
            'original_url' => 'https://example.com',
            'short_code' => 'abc123',
            'click_count' => 5
        ]);

        $url->incrementClickCount();

        $this->assertEquals(6, $url->fresh()->click_count);
    }

    public function test_not_expired_scope_excludes_expired_urls(): void
    {
        // Clear any existing data
        Url::truncate();

        // Create expired URL
        Url::create([
            'original_url' => 'https://expired.com',
            'short_code' => 'expired',
            'expires_at' => Carbon::now()->subDay()
        ]);

        // Create non-expired URL
        Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        // Create URL without expiration
        Url::create([
            'original_url' => 'https://permanent.com',
            'short_code' => 'permanent',
            'expires_at' => null
        ]);

        $notExpiredUrls = Url::notExpired()->get();

        $this->assertCount(2, $notExpiredUrls);
        $this->assertTrue($notExpiredUrls->contains('short_code', 'valid'));
        $this->assertTrue($notExpiredUrls->contains('short_code', 'permanent'));
        $this->assertFalse($notExpiredUrls->contains('short_code', 'expired'));
    }

    public function test_expired_scope_includes_only_expired_urls(): void
    {
        // Clear any existing data
        Url::truncate();

        // Create expired URL
        Url::create([
            'original_url' => 'https://expired.com',
            'short_code' => 'expired',
            'expires_at' => Carbon::now()->subDay()
        ]);

        // Create non-expired URL
        Url::create([
            'original_url' => 'https://valid.com',
            'short_code' => 'valid',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $expiredUrls = Url::expired()->get();

        $this->assertCount(1, $expiredUrls);
        $this->assertEquals('expired', $expiredUrls->first()->short_code);
    }
}
