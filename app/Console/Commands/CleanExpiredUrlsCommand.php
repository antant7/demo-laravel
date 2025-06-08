<?php

namespace App\Console\Commands;

use App\Services\UrlCleanupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CleanExpiredUrlsCommand extends Command
{
    protected $signature = 'app:clean-expired-urls {--dry-run : Show the number of URLs to be deleted without actually deleting them}';
    protected $description = 'Removes expired URLs from the database';

    public function __construct(
        private UrlCleanupService $urlCleanupService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $lockKey = 'clean-expired-urls-lock';

        // Lock the command to prevent concurrent execution
        if (Cache::has($lockKey)) {
            $this->error('The command is already running in another process.');
            return self::FAILURE;
        }

        Cache::put($lockKey, true, 300); // 5 minutes lock

        try {
            $isDryRun = $this->option('dry-run');

            $this->info('Cleaning Expired URLs');
            $this->newLine();

            // Get expired URLs through service
            $expiredUrls = $this->urlCleanupService->getExpiredUrls();
            $count = count($expiredUrls);

            if ($count === 0) {
                $this->info('No expired URLs found.');
                return self::SUCCESS;
            }

            if ($isDryRun) {
                $this->warn(sprintf('Found %d expired URLs for deletion.', $count));

                // Show details of expired URLs
                $headers = ['ID', 'Short Code', 'Expiration Date', 'Click Count'];
                $rows = array_map(function($url) {
                    return [
                        $url['id'],
                        $url['short_code'],
                        $url['expires_at'],
                        $url['click_count']
                    ];
                }, array_slice($expiredUrls, 0, 10)); // Show only first 10

                $this->table($headers, $rows);

                if ($count > 10) {
                    $this->warn(sprintf('... and %d more URLs', $count - 10));
                }

                return self::SUCCESS;
            }

            // Deletion confirmation
            if (!$this->confirm(sprintf('Are you sure you want to delete %d expired URLs?', $count), false)) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }

            // Delete expired URLs through service
            $deletedCount = $this->urlCleanupService->deleteExpiredUrls();

            $this->info(sprintf('Successfully deleted %d expired URLs.', $deletedCount));

            return self::SUCCESS;
        } finally {
            // Always release the lock
            Cache::forget($lockKey);
        }
    }
}
