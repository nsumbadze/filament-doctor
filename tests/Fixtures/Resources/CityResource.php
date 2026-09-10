<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Category;
use Nsumbadze\Doctor\Tests\Fixtures\Resources\CityResource\Pages;

/**
 * Shared lookup data: opted out of tenancy, model has no tenant relationship.
 */
class CityResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $slug = 'cities';

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
        return ['index' => Pages\ListCities::route('/')];
    }
}
