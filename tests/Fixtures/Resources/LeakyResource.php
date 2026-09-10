<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Tag;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\LeakyResource\Pages;

/**
 * Opted out of tenancy although Tag belongs to a team.
 */
class LeakyResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $slug = 'leaky';

    protected static bool $isScopedToTenant = false;

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
        return ['index' => Pages\ListLeaky::route('/')];
    }
}
