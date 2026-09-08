<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Note;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\NoteResource\Pages;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotes::route('/'),
        ];
    }
}
