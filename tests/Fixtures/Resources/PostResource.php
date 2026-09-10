<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Post;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\PostResource\Pages;

class PostResource extends Resource
{
    use Translatable;

    protected static ?string $model = Post::class;

    public static function getNavigationBadge(): ?string
    {
        return (string) Post::query()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label(__('doctor-fixture.missing_label')),
            TextInput::make('slug')->label(__('filament-panels::pages/dashboard.title')),
            TextInput::make('tab')->label(__('doctor-fixture.tabs.' . 'all')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
