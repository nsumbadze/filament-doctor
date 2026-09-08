<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Jobs;

use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncCatalogJob implements ShouldQueue
{
    public function handle(): void
    {
        $tenant = Filament::getTenant();

        unset($tenant);
    }
}
