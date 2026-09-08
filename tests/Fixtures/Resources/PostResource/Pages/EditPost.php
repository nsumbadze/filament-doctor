<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;
}
