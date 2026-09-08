<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Actions\Imports\ImportColumn;
use Filament\Panel;
use Nsumbadze\Doctor\Support\Inspector;
use Throwable;

/**
 * Filament's Importer::fillRecord() assigns every mapped column onto the model.
 * A column that is not a real attribute (brand_name, parent_slug…) ends up in
 * an INSERT and fails with "column does not exist" unless fillRecord() is
 * overridden.
 */
class ImportTransientColumn extends AbstractRule
{
    public function id(): string
    {
        return 'import-transient-column';
    }

    public function description(): string
    {
        return 'Importer columns that are not model attributes while fillRecord() is not overridden';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->importers() as $importer) {
            if ($inspector->overrides($importer, 'fillRecord')) {
                continue;
            }

            $model = $importer::getModel();
            $columns = $inspector->columnsOf($model);

            if ($columns === null) {
                continue;
            }

            try {
                $importColumns = $importer::getColumns();
            } catch (Throwable) {
                continue;
            }

            foreach ($importColumns as $column) {
                $name = $column->getName();

                if (in_array($name, $columns, true) || $this->isRelationshipColumn($column)) {
                    continue;
                }

                yield $this->finding("{$importer}::{$name}", "Import column \"{$name}\" is not a column of {$model}; fillRecord() will try to save it. Override fillRecord() or map it to a real attribute.", $importer, $inspector->methodLine($importer, 'getColumns'));
            }
        }
    }

    protected function isRelationshipColumn(ImportColumn $column): bool
    {
        try {
            return $column->getRelationshipName() !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
