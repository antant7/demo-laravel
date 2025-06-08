<?php

namespace Tests\Unit\Services;

use App\Services\UrlConstants;
use PHPUnit\Framework\TestCase; // This is fine for pure unit tests

class UrlConstantsTest extends TestCase
{
    public function test_base_chars_constant_has_correct_length(): void
    {
        $this->assertEquals(UrlConstants::BASE, strlen(UrlConstants::BASE_CHARS));
    }

    public function test_base_chars_contains_expected_characters(): void
    {
        $baseChars = UrlConstants::BASE_CHARS;

        // Check that it contains lowercase letters
        $this->assertStringContainsString('a', $baseChars);
        $this->assertStringContainsString('z', $baseChars);

        // Check that it contains uppercase letters
        $this->assertStringContainsString('A', $baseChars);
        $this->assertStringContainsString('Z', $baseChars);

        // Check that it contains digits
        $this->assertStringContainsString('0', $baseChars);
        $this->assertStringContainsString('9', $baseChars);
    }

    public function test_base_chars_has_unique_characters(): void
    {
        $baseChars = UrlConstants::BASE_CHARS;
        $uniqueChars = array_unique(str_split($baseChars));

        $this->assertEquals(strlen($baseChars), count($uniqueChars));
    }

    public function test_base_chars_contains_only_alphanumeric_characters(): void
    {
        $baseChars = UrlConstants::BASE_CHARS;

        // Check that BASE_CHARS contains only a-zA-Z0-9 characters
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $baseChars,
            'BASE_CHARS should contain only alphanumeric characters (a-zA-Z0-9)');
    }

    public function test_base_constant_equals_62(): void
    {
        $this->assertEquals(62, UrlConstants::BASE);
    }
}
