<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->string('original_url', 2048);
            $table->string('short_code', 20)->unique();
            $table->integer('click_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->index('short_code');
        });

        // Set auto-increment start value to 10000 for better URL demonstration
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER SEQUENCE urls_id_seq RESTART WITH 10000');
        }

        // Insert test data
        DB::table('urls')->insert([
            [
                'original_url' => 'https://www.google.com',
                'short_code' => 'google',
                'click_count' => 15,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDays(30)
            ],
            [
                'original_url' => 'https://github.com/laravel/laravel',
                'short_code' => 'laravel',
                'click_count' => 8,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addDays(60)
            ],
            [
                'original_url' => 'https://stackoverflow.com/questions/tagged/php',
                'short_code' => 'phpso',
                'click_count' => 23,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => null // Never expires
            ],
            [
                'original_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'short_code' => 'rick',
                'click_count' => 42,
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
                'expires_at' => now()->addDays(7)
            ],
            [
                'original_url' => 'https://docs.laravel.com/11.x',
                'short_code' => 'docs',
                'click_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'expires_at' => now()->addYear()
            ],
            [
                'original_url' => 'https://www.example.com/old-page',
                'short_code' => 'expired1',
                'click_count' => 5,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(3),
                'expires_at' => now()->subDays(2) // Expired 2 days ago
            ],
            [
                'original_url' => 'https://old-news.com/article/123',
                'short_code' => 'oldnews',
                'click_count' => 12,
                'created_at' => now()->subWeeks(2),
                'updated_at' => now()->subDays(7),
                'expires_at' => now()->subDays(1) // Expired yesterday
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};
