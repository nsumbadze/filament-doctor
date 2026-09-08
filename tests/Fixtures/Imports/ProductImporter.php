<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Tests\Fixtures\Imports;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Nsumbadze\Doctor\Tests\Fixtures\Models\Product;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping(),
            ImportColumn::make('category_name'),
            ImportColumn::make('category')->relationship(resolveUsing: 'name'),
        ];
    }

    public function resolveRecord(): ?Product
    {
        return new Product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Done';
    }
}
