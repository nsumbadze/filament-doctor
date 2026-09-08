<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor\Rules;

use Nsumbadze\Doctor\Contracts\Rule;
use Nsumbadze\Doctor\Finding;
use Nsumbadze\Doctor\Severity;
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
}
