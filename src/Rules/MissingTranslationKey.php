<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Filament\Panel;
use Illuminate\Support\Facades\Lang;
use Nsumbadze\Doctor\Support\Inspector;
use ReflectionClass;

/**
 * Translation keys referenced in resources and pages that do not exist for a
 * configured locale render as the literal key in the UI ("auth.failed").
 */
class MissingTranslationKey extends AbstractRule
{
    public function id(): string
    {
        return 'missing-translation-key';
    }

    public function description(): string
    {
        return 'Translation keys used in resources and pages that no locale defines';
    }

    public function check(Panel $panel, Inspector $inspector): iterable
    {
        /** @var array<int, string> $locales */
        $locales = (array) config('filament-doctor.locales', [config('app.locale', 'en')]);

        $classes = [];

        foreach ($inspector->resources() as $resource) {
            $classes[] = $resource;

            foreach ($inspector->pages($resource) as $page) {
                $classes[] = $page;
            }
        }

        foreach (array_unique($classes) as $class) {
            $file = (new ReflectionClass($class))->getFileName();

            if ($file === false) {
                continue;
            }

            foreach ($this->keysIn($file) as [$key, $line]) {
                foreach ($locales as $locale) {
                    if (Lang::has($key, $locale, fallback: false)) {
                        continue;
                    }

                    yield $this->finding("{$class}::{$key}@{$locale}", "Translation key \"{$key}\" is missing for locale \"{$locale}\".", $class, $line);
                }
            }
        }
    }

    /**
     * @return array<int, array{0: string, 1: int}>
     */
    protected function keysIn(string $file): array
    {
        $lines = file($file);

        if ($lines === false) {
            return [];
        }

        $keys = [];

        foreach ($lines as $index => $line) {
            if (! preg_match_all('/\b(?:__|trans|trans_choice)\(\s*([\'"])([^\'"]+)\1/', $line, $matches)) {
                continue;
            }

            foreach ($matches[2] as $key) {
                if (! str_contains($key, '.') && ! str_contains($key, '::')) {
                    continue;
                }

                if (str_contains($key, ' ')) {
                    continue;
                }

                $keys[] = [$key, $index + 1];
            }
        }

        return $keys;
    }
}
