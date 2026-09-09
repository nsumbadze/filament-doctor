<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Support;

final class Paths
{
    /**
     * A path relative to the project root, or unchanged when it lies outside.
     */
    public static function relative(string $file): string
    {
        $base = rtrim(base_path(), '/') . '/';

        return str_starts_with($file, $base) ? substr($file, strlen($base)) : $file;
    }
}
