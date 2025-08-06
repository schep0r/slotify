<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FreeSpinService;
use Illuminate\Console\Command;

class CleanupExpiredFreeSpins extends Command
{
    protected $signature = 'freespins:cleanup';
    protected $description = 'Cleanup expired free spins';

    public function handle(FreeSpinService $freeSpinService)
    {
        $cleaned = $freeSpinService->cleanupExpiredFreeSpins();

        $this->info("Cleaned up {$cleaned} expired free spin records.");
    }
}
