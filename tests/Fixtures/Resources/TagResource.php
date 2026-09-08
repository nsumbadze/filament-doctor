<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Tag;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\TagResource\Pages;

/**
 * A resource with nothing wrong: policy with viewAny, searchable relationship
 * select, cached badge, plain columns.
 */
class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('tags-count', 60, fn (): string => (string) Tag::query()->count());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('filament-panels::pages/dashboard.title')),
            Select::make('category_id')->relationship('category', 'name')->searchable()->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
        ];
    }
}
