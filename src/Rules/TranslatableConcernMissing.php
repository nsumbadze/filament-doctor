<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Nsumbadze\Doctor\Support\Inspector;

/**
 * A model that uses Spatie's HasTranslations must have the Translatable concern
 * on the resource AND on every Create / Edit / List / View page, otherwise the
 * form binds the raw translations array and renders "[object Object]".
 */
class TranslatableConcernMissing extends AbstractRule
{
    public function id(): string
    {
        return 'translatable-concern-missing';
    }

    public function description(): string
    {
        return 'Translatable models whose resource or pages lack the Translatable concern';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        foreach ($inspector->resources() as $resource) {
            if (! $this->usesTranslations($inspector->model($resource))) {
                continue;
            }

            if (! $this->hasTranslatableConcern($inspector->traitsOf($resource))) {
                yield $this->finding($resource, 'Model is translatable but the resource lacks the Translatable concern.', $resource);
            }

            foreach ($inspector->pages($resource) as $name => $page) {
                if ($inspector->isIgnored($page) || $this->hasTranslatableConcern($inspector->traitsOf($page))) {
                    continue;
                }

                yield $this->finding($page, "Page \"{$name}\" of a translatable resource lacks the Translatable concern; forms will show [object Object].", $page);
            }
        }
    }

    /**
     * @param  class-string  $model
     */
    protected function usesTranslations(string $model): bool
    {
        foreach (class_uses_recursive($model) as $trait) {
            if (class_basename($trait) === 'HasTranslations') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $traits
     */
    protected function hasTranslatableConcern(array $traits): bool
    {
        foreach ($traits as $trait) {
            if (class_basename($trait) === 'Translatable') {
                return true;
            }
        }

        return false;
    }
}
