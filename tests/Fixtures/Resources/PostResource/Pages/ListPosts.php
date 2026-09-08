<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource\Pages;

use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource;

class ListPosts extends ListRecords
{
    use Translatable;

    protected static string $resource = PostResource::class;
}
