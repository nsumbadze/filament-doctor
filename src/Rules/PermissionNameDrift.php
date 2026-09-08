<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Nsumbadze\Doctor\Support\Inspector;
use Spatie\Permission\Models\Permission;
use Throwable;

/**
 * Policies that check permission names nobody ever creates (a generator that
 * produces "Create:Post" while the policy asks for "create_post") deny
 * everyone forever without an error anywhere.
 */
class PermissionNameDrift extends AbstractRule
{
    public function id(): string
    {
        return 'permission-name-drift';
    }

    public function description(): string
    {
        return 'Permission names checked in policies that do not exist in the permission store';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        $known = $this->knownPermissions();

        if ($known === null) {
            return;
        }

        foreach ($inspector->phpFilesIn((array) config('filament-doctor.paths.policies', [])) as $file) {
            foreach ($this->permissionsIn($file) as [$name, $line]) {
                if (in_array($name, $known, true)) {
                    continue;
                }

                yield $this->finding("{$file}::{$name}", "Permission \"{$name}\" is checked here but no such permission exists; the check always fails.", null, $line)
                    ->withFile($file);
            }
        }
    }

    /**
     * @return array<int, string>|null null when no permission source is available
     */
    protected function knownPermissions(): ?array
    {
        $source = config('filament-doctor.permissions');

        if (is_callable($source)) {
            return array_values(array_map('strval', (array) $source()));
        }

        if (is_array($source)) {
            return array_values(array_map('strval', $source));
        }

        if (! class_exists(Permission::class)) {
            return null;
        }

        try {
            return Permission::query()->pluck('name')->map(fn ($name): string => (string) $name)->all();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array{0: string, 1: int}>
     */
    protected function permissionsIn(string $file): array
    {
        $lines = file($file);

        if ($lines === false) {
            return [];
        }

        $found = [];

        foreach ($lines as $index => $line) {
            if (! preg_match_all('/->(?:can|cannot|hasPermissionTo|checkPermissionTo|hasAnyPermission|hasAllPermissions)\(\s*([\'"])([^\'"]+)\1/', $line, $matches)) {
                continue;
            }

            foreach ($matches[2] as $name) {
                $found[] = [$name, $index + 1];
            }
        }

        return $found;
    }
}
