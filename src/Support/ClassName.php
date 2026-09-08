<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Support;

final class ClassName
{
    /**
     * @return class-string|null
     */
    public static function fromFile(string $file): ?string
    {
        $contents = (string) file_get_contents($file);

        if (! preg_match('/^\s*namespace\s+([^;]+);/m', $contents, $namespace)) {
            return null;
        }

        if (! preg_match('/^\s*(?:final\s+|abstract\s+|readonly\s+)*class\s+(\w+)/m', $contents, $class)) {
            return null;
        }

        /** @var class-string $name */
        $name = trim($namespace[1]) . '\\' . $class[1];

        return $name;
    }
}
