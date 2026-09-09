<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Tag;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\BrokenResource\Pages;
use RuntimeException;

/**
 * A resource whose form cannot be evaluated outside a request.
 */
class BrokenResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $slug = 'broken';

    public static function form(Schema $schema): Schema
    {
        throw new RuntimeException('needs an authenticated user');
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBroken::route('/'),
        ];
    }
}
