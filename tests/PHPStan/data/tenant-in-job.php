<?php

declare(strict_types=1);

namespace TenantInJobData;

use Filament\Actions\Imports\Importer;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedJob implements ShouldQueue
{
    public function handle(): mixed
    {
        return Filament::getTenant();
    }
}

abstract class ProductImporter extends Importer
{
    public function resolveRecord(): mixed
    {
        return Filament::getTenantId();
    }
}

class Controller
{
    public function show(): mixed
    {
        return Filament::getTenant();
    }
}
