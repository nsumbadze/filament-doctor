<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor;

/**
 * One problem found by one rule on one subject (class, file, column…).
 */
final class Finding
{
    public function __construct(
        public readonly string $rule,
        public readonly Severity $severity,
        public readonly string $subject,
        public readonly string $message,
        public readonly ?string $file = null,
        public readonly ?int $line = null,
    ) {}

    public function withSeverity(Severity $severity): self
    {
        return new self($this->rule, $severity, $this->subject, $this->message, $this->file, $this->line);
    }

    public function withFile(string $file, ?int $line = null): self
    {
        return new self($this->rule, $this->severity, $this->subject, $this->message, $file, $line ?? $this->line);
    }

    /**
     * Stable identity used by the baseline: rule + subject, never the message
     * (messages may change wording between versions).
     */
    public function fingerprint(): string
    {
        return hash('sha256', $this->rule . '|' . $this->subject);
    }

    public function docsUrl(): string
    {
        return "https://github.com/nsumbadze/filament-doctor/blob/main/docs/rules/{$this->rule}.md";
    }

    /**
     * @return array{rule: string, severity: string, subject: string, message: string, file: ?string, line: ?int, docs: string}
     */
    public function toArray(): array
    {
        return [
            'rule' => $this->rule,
            'severity' => $this->severity->value,
            'subject' => $this->subject,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'docs' => $this->docsUrl(),
        ];
    }
}
