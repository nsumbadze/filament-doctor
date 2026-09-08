<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\ProductResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\ProductResource;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
}
