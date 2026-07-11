<?php

namespace Ahmadi\LaravelSepidar\Support;

class CredentialStore
{
    public function __construct(
        protected string $path,
        protected ?string $legacyPath = null,
    ) {
        $this->bootstrapFromLegacy();
    }

    public function all(): array
    {
        if (! is_file($this->path)) {
            return [];
        }

        $contents = file_get_contents($this->path);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        return json_decode($contents, true) ?? [];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function put(array $data): void
    {
        $directory = dirname($this->path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
            $this->path,
            json_encode(array_merge($this->all(), $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function flush(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function isRegistered(): bool
    {
        return $this->has('Cypher') && $this->has('IV') && $this->has('device_serial');
    }

    protected function bootstrapFromLegacy(): void
    {
        if (is_file($this->path) || ! $this->legacyPath || ! is_file($this->legacyPath)) {
            return;
        }

        $legacy = json_decode(file_get_contents($this->legacyPath), true);

        if (! is_array($legacy)) {
            return;
        }

        $this->put([
            'Cypher' => $legacy['Cypher'] ?? null,
            'IV' => $legacy['IV'] ?? $legacy['iv'] ?? null,
            'DeviceTitle' => $legacy['DeviceTitle'] ?? null,
            'Token' => $legacy['Token'] ?? null,
            'GenerationVersion' => $legacy['GenerationVersion'] ?? null,
            'IntegrationID' => $legacy['IntegrationID'] ?? null,
            'device_serial' => $legacy['device_serial'] ?? null,
        ]);
    }
}
