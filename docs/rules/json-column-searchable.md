# JSON column searchable

Rule id: `json-column-searchable` · Default severity: **error**

## What it catches

A table column backed by a JSON attribute (a `json`/`jsonb` column or an `array`/`json`/`collection` cast) is `searchable()` or `sortable()`.

## Why it matters

Filament compiles the search to `lower(column::text) LIKE '%…%'`, which no index can serve; every keystroke scans the whole table. Sorting casts every row to text.

## How to fix

Search a concrete expression and index it:

```php
TextColumn::make('name')
    ->searchable(query: fn (Builder $query, string $search) => $query->whereRaw("name->>'en' ILIKE ?", ["%{$search}%"]))
```

with a trigram index on `(name->>'en')`.

## Turning it off

```php
'rules' => ['json-column-searchable' => 'off'],
```
