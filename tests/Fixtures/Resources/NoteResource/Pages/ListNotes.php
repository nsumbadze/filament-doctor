<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources\NoteResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\NoteResource;

class ListNotes extends ListRecords
{
    protected static string $resource = NoteResource::class;
}
