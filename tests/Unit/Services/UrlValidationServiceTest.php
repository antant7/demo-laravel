<?php

namespace Tests\Unit\Services;

use App\Services\UrlValidationService;
use Carbon\Carbon;
use Tests\TestCase; // Change from PHPUnit\Framework\TestCase
use Illuminate\Foundation\Testing\RefreshDatabase;

class UrlValidationServiceTest extends TestCase // Change base class
{
    use RefreshDatabase; // Add if needed for database interactions

    private UrlValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UrlValidationService();
    }

    public function test_validate_url_data_passes_with_valid_data(): void
    {
        $data = [
            'url' => 'https://example.com',
            'expires_at' => '2025-12-31T23:59:59Z',
            'custom_alias' => 'valid-alias_123'
        ];

        $errors = $this->service->validateUrlData($data);

        $this->assertEmpty($errors);
    }

    public function test_validate_url_data_fails_with_invalid_url(): void
    {
        $data = ['url' => 'not-a-valid-url'];

        $errors = $this->service->validateUrlData($data);

        $this->assertNotEmpty($errors);
        $this->assertContains('The url field must be a valid URL.', $errors);
    }

    public function test_validate_url_data_fails_with_missing_url(): void
    {
        $data = [];

        $errors = $this->service->validateUrlData($data);

        $this->assertNotEmpty($errors);
        $this->assertContains('The url field is required.', $errors);
    }

    public function test_validate_url_data_fails_with_invalid_expires_at_format(): void
    {
        $data = [
            'url' => 'https://example.com',
            'expires_at' => '2025-12-31 23:59:59' // Wrong format
        ];

        $errors = $this->service->validateUrlData($data);

        $this->assertNotEmpty($errors);
        $this->assertContains('Date must be in ISO 8601 format (example: 2025-12-31T23:59:59Z)', $errors);
    }

    public function test_validate_url_data_fails_with_invalid_custom_alias_characters(): void
    {
        $data = [
            'url' => 'https://example.com',
            'custom_alias' => 'invalid@alias!' // Invalid characters
        ];

        $errors = $this->service->validateUrlData($data);

        $this->assertNotEmpty($errors);
        $this->assertContains('Custom alias can only contain characters a-zA-Z0-9-_', $errors);
    }

    public function test_validate_url_data_fails_when_custom_alias_can_be_decoded_as_id(): void
    {
        $data = [
            'url' => 'https://example.com',
            'custom_alias' => 'K8mQpF' // Contains only BASE_CHARS
        ];

        $errors = $this->service->validateUrlData($data);

        $this->assertNotEmpty($errors);
        $this->assertContains('Custom alias cannot be decoded as ID', $errors);
    }

    public function test_parse_expires_at_returns_null_for_null_input(): void
    {
        $result = $this->service->parseExpiresAt(null);

        $this->assertNull($result);
    }

    public function test_parse_expires_at_returns_carbon_instance_for_valid_date(): void
    {
        $dateString = '2025-12-31T23:59:59Z';

        $result = $this->service->parseExpiresAt($dateString);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2025-12-31 23:59:59', $result->format('Y-m-d H:i:s'));
    }

    public function test_parse_expires_at_throws_exception_for_invalid_date(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect date format');

        $this->service->parseExpiresAt('invalid-date');
    }
}
