<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\BrokenResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\BrokenResource;

class ListBroken extends ListRecords
{
    protected static string $resource = BrokenResource::class;
}
