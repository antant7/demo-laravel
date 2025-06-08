<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class UrlValidationService
{
    /**
     * Validates data for creating a short link
     */
    public function validateUrlData(array $data): array
    {
        $validator = Validator::make($data, [
            'url' => 'required|url',
            'expires_at' => [
                'nullable',
                'regex:/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(Z|[+-]\d{2}:\d{2})$/'
            ],
            'custom_alias' => [
                'nullable',
                'regex:/^[a-zA-Z0-9_-]+$/'
            ]
        ], [
            'expires_at.regex' => 'Date must be in ISO 8601 format (example: 2025-12-31T23:59:59Z)',
            'custom_alias.regex' => 'Custom alias can only contain characters a-zA-Z0-9-_'
        ]);

        $errors = [];

        if ($validator->fails()) {
            $errors = array_merge($errors, $validator->errors()->all());
        }

        // Additional check for custom_alias decoding to BASE_CHAR
        if (isset($data['custom_alias']) && !empty($data['custom_alias'])) {
            if ($this->canDecodeToId($data['custom_alias'])) {
                $errors[] = 'Custom alias cannot be decoded as ID';
            }
        }

        return $errors;
    }

    /**
     * Checks if a string can be decoded as ID using BASE_CHARS
     */
    private function canDecodeToId(string $alias): bool
    {
        // Check that all alias characters are in BASE_CHARS
        for ($i = 0; $i < strlen($alias); $i++) {
            if (strpos(UrlConstants::BASE_CHARS, $alias[$i]) === false) {
                return false; // Contains characters not from BASE_CHARS, so cannot be decoded
            }
        }

        // If all characters are from BASE_CHARS, decode and check if it exceeds big int limit
        try {
            $decodedId = $this->decodeAliasToId($alias);
            // If we got here without exception, the ID is within int range
            return true;
        } catch (\OverflowException $e) {
            // ID exceeds big int limit, so it's safe to use as custom alias
            return false;
        }
    }

    /**
     * Decode alias to ID using BASE_CHARS (reverse of generateShortCodeFromId)
     * @throws \OverflowException if the decoded value exceeds PHP_INT_MAX
     */
    private function decodeAliasToId(string $alias): int
    {
        $id = 0;
        $base = UrlConstants::BASE;
        $chars = UrlConstants::BASE_CHARS;
        $length = strlen($alias);

        for ($i = 0; $i < $length; $i++) {
            $charPosition = strpos($chars, $alias[$i]);

            // Check for overflow before calculation
            if ($id > (PHP_INT_MAX - $charPosition) / $base) {
                throw new \OverflowException('Decoded ID exceeds big int limit');
            }

            $id = $id * $base + $charPosition;
        }

        return $id;
    }

    /**
     * Parses expiration date from string
     */
    public function parseExpiresAt(?string $expiresAtString): ?Carbon
    {
        if ($expiresAtString === null) {
            return null;
        }

        try {
            return Carbon::parse($expiresAtString);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Incorrect date format');
        }
    }
}
