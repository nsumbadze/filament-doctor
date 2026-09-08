<?php

declare(strict_types=1);

namespace Nsumbadze\Doctor;

use Illuminate\Support\Facades\File;

/**
 * A JSON list of finding fingerprints that are known and accepted, so an
 * existing project can adopt the doctor without fixing everything first.
 */
final class Baseline
{
    public function __construct(private readonly string $path) {}

    public static function fromConfig(): self
    {
        return new self((string) config('filament-doctor.baseline', base_path('filament-doctor-baseline.json')));
    }

    public function exists(): bool
    {
        return File::exists($this->path);
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<int, string>
     */
    public function fingerprints(): array
    {
        if (! $this->exists()) {
            return [];
        }

        $decoded = json_decode((string) File::get($this->path), true);

        if (! is_array($decoded)) {
            return [];
        }

        $entries = $decoded['findings'] ?? [];

        return array_values(array_filter(array_map(
            fn (mixed $entry): ?string => is_array($entry) ? ($entry['fingerprint'] ?? null) : (is_string($entry) ? $entry : null),
            is_array($entries) ? $entries : [],
        )));
    }

    /**
     * @param  array<int, Finding>  $findings
     * @return array<int, Finding>
     */
    public function filter(array $findings): array
    {
        $known = array_flip($this->fingerprints());

        return array_values(array_filter(
            $findings,
            fn (Finding $finding): bool => ! isset($known[$finding->fingerprint()]),
        ));
    }

    /**
     * @param  array<int, Finding>  $findings
     */
    public function write(array $findings): void
    {
        $entries = array_map(fn (Finding $finding): array => [
            'fingerprint' => $finding->fingerprint(),
            'rule' => $finding->rule,
            'subject' => $finding->subject,
        ], $findings);

        File::put($this->path, (string) json_encode([
            'generated_at' => now()->toIso8601String(),
            'findings' => $entries,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
