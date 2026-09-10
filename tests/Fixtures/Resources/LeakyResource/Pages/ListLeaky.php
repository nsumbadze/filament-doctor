<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\LeakyResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\LeakyResource;

class ListLeaky extends ListRecords
{
    protected static string $resource = LeakyResource::class;
}
