<?php

namespace Tests\Unit\Services;

use App\Models\Url;
use App\Services\UrlShortenerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class UrlShortenerServiceTest extends TestCase
{
    use RefreshDatabase;

    private UrlShortenerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UrlShortenerService();
    }

    public function test_shorten_url_creates_url_with_generated_short_code(): void
    {
        $originalUrl = 'https://example.com';

        $url = $this->service->shortenUrl($originalUrl);

        $this->assertInstanceOf(Url::class, $url);
        $this->assertEquals($originalUrl, $url->original_url);
        $this->assertNotEmpty($url->short_code);
        $this->assertNull($url->expires_at);
        $this->assertEquals(0, $url->click_count);
    }

    public function test_shorten_url_with_expiration_date(): void
    {
        $originalUrl = 'https://example.com';
        $expiresAt = Carbon::now()->addDays(7);

        $url = $this->service->shortenUrl($originalUrl, $expiresAt);

        $this->assertEquals($expiresAt->toDateTimeString(), $url->expires_at->toDateTimeString());
    }

    public function test_shorten_url_with_custom_alias(): void
    {
        $originalUrl = 'https://example.com';
        $customAlias = 'my-custom-alias';

        $url = $this->service->shortenUrl($originalUrl, null, $customAlias);

        $this->assertEquals($customAlias, $url->short_code);
    }

    public function test_shorten_url_throws_exception_for_duplicate_custom_alias(): void
    {
        $originalUrl = 'https://example.com';
        $customAlias = 'duplicate-alias';

        // Create first URL with custom alias
        $this->service->shortenUrl($originalUrl, null, $customAlias);

        // Try to create second URL with same alias
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom alias is already in use');

        $this->service->shortenUrl('https://another.com', null, $customAlias);
    }

    public function test_shorten_url_custom_alias_case_insensitive_check(): void
    {
        $originalUrl = 'https://example.com';
        $customAlias = 'MyAlias';

        // Create first URL with custom alias
        $this->service->shortenUrl($originalUrl, null, $customAlias);

        // Try to create second URL with same alias in different case
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom alias is already in use');

        $this->service->shortenUrl('https://another.com', null, 'myalias');
    }

    public function test_get_original_url_returns_url_and_increments_count(): void
    {
        $url = Url::create([
            'original_url' => 'https://example.com',
            'short_code' => 'test123',
            'click_count' => 5
        ]);

        $result = $this->service->getOriginalUrl('test123');

        $this->assertInstanceOf(Url::class, $result);
        $this->assertEquals('https://example.com', $result->original_url);
        $this->assertEquals(6, $result->fresh()->click_count);
    }

    public function test_get_original_url_returns_null_for_nonexistent_code(): void
    {
        $result = $this->service->getOriginalUrl('nonexistent');

        $this->assertNull($result);
    }

    public function test_get_original_url_returns_null_for_expired_url(): void
    {
        Url::create([
            'original_url' => 'https://example.com',
            'short_code' => 'expired123',
            'expires_at' => Carbon::now()->subDay()
        ]);

        $result = $this->service->getOriginalUrl('expired123');

        $this->assertNull($result);
    }

    public function test_find_url_by_id_returns_correct_url(): void
    {
        $url = Url::create([
            'original_url' => 'https://example.com',
            'short_code' => 'test123'
        ]);

        $result = $this->service->findUrlById($url->id);

        $this->assertInstanceOf(Url::class, $result);
        $this->assertEquals($url->id, $result->id);
    }

    public function test_find_url_by_id_returns_null_for_nonexistent_id(): void
    {
        $result = $this->service->findUrlById(999999);

        $this->assertNull($result);
    }
}
