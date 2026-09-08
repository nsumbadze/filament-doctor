<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Nsumbadze\Doctor\Support\Inspector;

/**
 * ->searchable() / ->sortable() on a JSON column compiles to
 * `lower(column::text) LIKE '%…%'`, which no index can serve: a full table scan
 * on every keystroke.
 */
class JsonColumnSearchable extends AbstractRule
{
    public function id(): string
    {
        return 'json-column-searchable';
    }

    public function description(): string
    {
        return 'Searchable or sortable table columns backed by JSON attributes';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->resources() as $resource) {
            $table = $inspector->table($resource);

            if ($table === null) {
                continue;
            }

            $model = $inspector->model($resource);

            foreach ($table->getColumns() as $column) {
                $name = $column->getName();

                if (str_contains($name, '.') || ! $inspector->isJsonAttribute($model, $name)) {
                    continue;
                }

                if ($column->isSearchable()) {
                    yield $this->finding("{$resource}::{$name}", "Column \"{$name}\" is searchable but stored as JSON; searches scan the whole table. Search a per-locale expression (e.g. name->>'en') with a custom searchUsing() and index it.", $resource, $inspector->methodLine($resource, 'table'));
                }

                if ($column->isSortable()) {
                    yield $this->finding("{$resource}::{$name}:sort", "Column \"{$name}\" is sortable but stored as JSON; sorting casts every row to text.", $resource, $inspector->methodLine($resource, 'table'));
                }
            }
        }
    }
}
