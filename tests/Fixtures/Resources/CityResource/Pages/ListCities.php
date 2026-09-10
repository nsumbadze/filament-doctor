<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\CityResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\CityResource;

class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;
}
