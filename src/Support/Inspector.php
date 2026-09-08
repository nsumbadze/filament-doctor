<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Support;

use Filament\Actions\Imports\Importer;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Livewire\Component;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Read-only access to a panel's resources, schemas and the database for rules.
 * Everything expensive (evaluating a form or table) is memoised per resource.
 */
class Inspector
{
    /** @var array<class-string, Schema|null> */
    protected array $forms = [];

    /** @var array<class-string, Table|null> */
    protected array $tables = [];

    /** @var array<string, array<int, string>> */
    protected array $columnListings = [];

    /**
     * @param  array<int, class-string>  $ignored
     */
    public function __construct(
        protected Panel $panel,
        protected bool $useDatabase = true,
        protected array $ignored = [],
    ) {}

    public function panel(): Panel
    {
        return $this->panel;
    }

    public function usesDatabase(): bool
    {
        return $this->useDatabase;
    }

    /**
     * @return array<int, class-string<resource>>
     */
    public function resources(): array
    {
        return array_values(array_filter(
            $this->panel->getResources(),
            fn (string $resource): bool => ! $this->isIgnored($resource),
        ));
    }

    /**
     * @param  class-string  $class
     */
    public function isIgnored(string $class): bool
    {
        return in_array($class, $this->ignored, true);
    }

    /**
     * @param  class-string<resource>  $resource
     * @return array<string, class-string> page name => page class
     */
    public function pages(string $resource): array
    {
        $pages = [];

        foreach ($resource::getPages() as $name => $registration) {
            $pages[$name] = $registration->getPage();
        }

        return $pages;
    }

    /**
     * @param  class-string<resource>  $resource
     * @return class-string<Model>
     */
    public function model(string $resource): string
    {
        return $resource::getModel();
    }

    /**
     * @param  class-string<resource>  $resource
     */
    public function form(string $resource): ?Schema
    {
        if (array_key_exists($resource, $this->forms)) {
            return $this->forms[$resource];
        }

        $pages = $this->pages($resource);
        $page = $pages['create'] ?? $pages['edit'] ?? $pages['index'] ?? null;

        if ($page === null || ! is_subclass_of($page, HasSchemas::class) || ! is_subclass_of($page, Component::class)) {
            return $this->forms[$resource] = null;
        }

        try {
            /** @var HasSchemas&Component $livewire */
            $livewire = new $page;

            return $this->forms[$resource] = $resource::form(Schema::make($livewire));
        } catch (Throwable) {
            return $this->forms[$resource] = null;
        }
    }

    /**
     * @param  class-string<resource>  $resource
     */
    public function table(string $resource): ?Table
    {
        if (array_key_exists($resource, $this->tables)) {
            return $this->tables[$resource];
        }

        $listPage = $this->pages($resource)['index'] ?? null;

        if ($listPage === null || ! is_subclass_of($listPage, HasTable::class)) {
            return $this->tables[$resource] = null;
        }

        try {
            /** @var HasTable $livewire */
            $livewire = new $listPage;

            return $this->tables[$resource] = $resource::table(Table::make($livewire));
        } catch (Throwable) {
            return $this->tables[$resource] = null;
        }
    }

    /**
     * Whether a class overrides a method inherited from a framework class.
     *
     * @param  class-string  $class
     */
    public function overrides(string $class, string $method): bool
    {
        if (! method_exists($class, $method)) {
            return false;
        }

        $declaring = (new ReflectionMethod($class, $method))->getDeclaringClass()->getName();

        return ! str_starts_with($declaring, 'Filament\\');
    }

    /**
     * Source code of one method, or null when it is not declared on $class.
     *
     * @param  class-string  $class
     */
    public function methodSource(string $class, string $method): ?string
    {
        if (! $this->overrides($class, $method)) {
            return null;
        }

        $reflection = new ReflectionMethod($class, $method);
        $file = $reflection->getFileName();

        if ($file === false) {
            return null;
        }

        $lines = file($file);

        if ($lines === false) {
            return null;
        }

        $start = (int) $reflection->getStartLine() - 1;
        $length = (int) $reflection->getEndLine() - $start;

        return implode('', array_slice($lines, $start, $length));
    }

    /**
     * @param  class-string  $class
     */
    public function methodLine(string $class, string $method): ?int
    {
        if (! method_exists($class, $method)) {
            return null;
        }

        return (new ReflectionMethod($class, $method))->getStartLine() ?: null;
    }

    /**
     * @param  class-string  $class
     * @return array<int, string> fully qualified trait names, recursively
     */
    public function traitsOf(string $class): array
    {
        return array_values(class_uses_recursive($class));
    }

    /**
     * @param  class-string<Model>  $model
     */
    public function isJsonAttribute(string $model, string $attribute): bool
    {
        /** @var Model $instance */
        $instance = new $model;

        $cast = $instance->getCasts()[$attribute] ?? null;

        if (is_string($cast)) {
            $base = strtolower((string) preg_replace('/:.*$/', '', $cast));

            if (in_array($base, ['array', 'json', 'object', 'collection', 'encrypted:array', 'encrypted:json', 'encrypted:object', 'encrypted:collection'], true)) {
                return true;
            }

            if (str_contains($cast, 'AsArrayObject') || str_contains($cast, 'AsCollection') || str_contains($cast, 'AsEncryptedArrayObject') || str_contains($cast, 'AsEncryptedCollection')) {
                return true;
            }
        }

        if (! $this->useDatabase) {
            return false;
        }

        try {
            $type = SchemaFacade::connection($instance->getConnectionName())->getColumnType($instance->getTable(), $attribute);
        } catch (Throwable) {
            return false;
        }

        return in_array(strtolower($type), ['json', 'jsonb'], true);
    }

    /**
     * @param  class-string<Model>  $model
     * @return array<int, string>|null null when the database is unavailable
     */
    public function columnsOf(string $model): ?array
    {
        if (! $this->useDatabase) {
            return null;
        }

        /** @var Model $instance */
        $instance = new $model;
        $key = $instance->getConnectionName() . '.' . $instance->getTable();

        if (isset($this->columnListings[$key])) {
            return $this->columnListings[$key];
        }

        try {
            return $this->columnListings[$key] = SchemaFacade::connection($instance->getConnectionName())->getColumnListing($instance->getTable());
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Concrete classes found in the given directories, optionally filtered by parent.
     *
     * @param  array<int, string>  $paths
     * @param  class-string|null  $subclassOf
     * @return array<int, class-string>
     */
    public function classesIn(array $paths, ?string $subclassOf = null): array
    {
        $classes = [];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $class = ClassName::fromFile($file->getPathname());

                if ($class === null || ! class_exists($class) || $this->isIgnored($class)) {
                    continue;
                }

                if ($subclassOf !== null && ! is_subclass_of($class, $subclassOf)) {
                    continue;
                }

                if ((new ReflectionClass($class))->isAbstract()) {
                    continue;
                }

                $classes[] = $class;
            }
        }

        sort($classes);

        return $classes;
    }

    /**
     * @return array<int, class-string<Importer>>
     */
    public function importers(): array
    {
        /** @var array<int, class-string<Importer>> $importers */
        $importers = $this->classesIn((array) config('filament-doctor.paths.importers', []), Importer::class);

        return $importers;
    }

    /**
     * PHP files under the given directories.
     *
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    public function phpFilesIn(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);

        return $files;
    }
}
