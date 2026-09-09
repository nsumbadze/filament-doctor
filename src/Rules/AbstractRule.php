<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Nsumbadze\Doctor\Contracts\Rule;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Severity;
use Nsumbadze\Doctor\Support\Paths;
use ReflectionClass;

abstract class AbstractRule implements Rule
{
    /**
     * @param  class-string|null  $class  used to attach file and line information
     */
    protected function finding(string $subject, string $message, ?string $class = null, ?int $line = null): Finding
    {
        $file = null;

        if ($class !== null && class_exists($class)) {
            $reflection = new ReflectionClass($class);
            $file = $reflection->getFileName() ?: null;
            $line ??= $reflection->getStartLine() ?: null;
        }

        return new Finding($this->id(), Severity::Error, $subject, $message, $file, $line);
    }

    /**
     * Paths inside subjects must not depend on the machine, or the baseline
     * would not survive a checkout elsewhere.
     */
    protected function relativePath(string $file): string
    {
        return Paths::relative($file);
    }

    /**
     * Line-based scanners skip comment lines; a mention inside a docblock is
     * not a call.
     */
    protected function isCommentLine(string $line): bool
    {
        $trimmed = ltrim($line);

        return str_starts_with($trimmed, '//')
            || str_starts_with($trimmed, '#')
            || str_starts_with($trimmed, '/*')
            || str_starts_with($trimmed, '*');
    }
}
