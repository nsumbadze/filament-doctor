<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\TagResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\TagResource;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;
}
