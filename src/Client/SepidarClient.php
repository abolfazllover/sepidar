<?php

namespace Ahmadi\LaravelSepidar\Client;

use Ahmadi\LaravelSepidar\Contracts\SepidarClientInterface;
use Ahmadi\LaravelSepidar\Crypto\AesEncrypter;
use Ahmadi\LaravelSepidar\Crypto\RsaEncrypter;
use Ahmadi\LaravelSepidar\Exceptions\SepidarApiException;
use Ahmadi\LaravelSepidar\Support\CredentialStore;
use Ahmadi\LaravelSepidar\Support\DeviceSerial;
use Ahmadi\LaravelSepidar\Support\PublicKeyResolver;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SepidarClient implements SepidarClientInterface
{
    protected CredentialStore $store;

    protected ?string $token = null;

    protected ?string $publicKeyXml = null;

    protected ?int $integrationId = null;

    protected ?string $deviceSerial = null;

    protected ?string $generationVersion = null;

    protected bool $bootstrapped = false;

    public function __construct(
        protected array $config,
        ?CredentialStore $store = null,
    ) {
        $this->store = $store ?? new CredentialStore(
            $config['credentials_path'],
            $config['legacy_credentials_path'] ?? null,
        );

        $this->hydrateFromStore();
    }

    /**
     * اتصال خودکار: ثبت دستگاه (در صورت نیاز) + ورود + آماده‌سازی توکن.
     */
    public function connect(): self
    {
        $this->bootstrap(force: true);

        return $this;
    }

    public function configure(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        if (! empty($config['device_serial'])) {
            $this->deviceSerial = $config['device_serial'];
        }

        if (! empty($config['generation_version'])) {
            $this->generationVersion = (string) $config['generation_version'];
        }

        return $this;
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('get', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('put', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('delete', $endpoint, ['json' => $data]);
    }

    public function getPublic(string $endpoint, array $query = []): array
    {
        return $this->send('get', $endpoint, ['query' => $query], secured: false);
    }

    public function postPublic(string $endpoint, array $data = []): array
    {
        return $this->send('post', $endpoint, ['json' => $data], secured: false);
    }

    public function registerDevice(?string $serial = null): array
    {
        $serial = $serial ?? $this->deviceSerial ?? $this->config['device_serial'] ?? null;

        if (! $serial) {
            throw SepidarApiException::configuration(
                'Device serial is required. Set SEPIDAR_DEVICE_SERIAL or run: php artisan sepidar:setup'
            );
        }

        $integrationId = DeviceSerial::integrationId($serial);
        $key = DeviceSerial::aesKey($serial);
        $iv = AesEncrypter::generateIv();

        $response = $this->postPublic('Devices/Register', [
            'Cypher' => AesEncrypter::encrypt((string) $integrationId, $key, $iv),
            'iv' => base64_encode($iv),
            'IntegrationID' => $integrationId,
        ]);

        $responseIv = $response['IV'] ?? $response['iv'] ?? null;

        if (! $responseIv || ! isset($response['Cypher'])) {
            throw SepidarApiException::configuration('Invalid register device response from Sepidar.');
        }

        $this->deviceSerial = $serial;
        $this->integrationId = $integrationId;
        $this->publicKeyXml = AesEncrypter::decrypt($response['Cypher'], $key, $responseIv);

        $this->store->put([
            'Cypher' => $response['Cypher'],
            'IV' => $responseIv,
            'DeviceTitle' => $response['DeviceTitle'] ?? null,
            'IntegrationID' => $integrationId,
            'device_serial' => $serial,
        ]);

        return [
            'DeviceTitle' => $response['DeviceTitle'] ?? null,
            'IntegrationID' => $integrationId,
            'Cypher' => $response['Cypher'],
            'IV' => $responseIv,
        ];
    }

    public function login(?string $username = null, ?string $password = null): array
    {
        $username = $username ?? $this->config['username'] ?? null;
        $password = $password ?? $this->config['password'] ?? null;

        if (! $username || ! $password) {
            throw SepidarApiException::configuration('SEPIDAR_USERNAME and SEPIDAR_PASSWORD are required.');
        }

        $this->ensureCryptoReady();

        $response = $this->send('post', 'users/login', [
            'json' => [
                'UserName' => $username,
                'PasswordHash' => md5($password),
            ],
        ], secured: 'login');

        $this->token = $response['Token'] ?? '';
        $this->store->put(['Token' => $this->token]);

        return $response;
    }

    public function authenticate(): self
    {
        return $this->connect();
    }

    public function isAuthorized(): bool
    {
        if (! $this->token) {
            return false;
        }

        try {
            $this->ensureCryptoReady();

            return $this->send('get', 'IsAuthorized', secured: true) === true;
        } catch (SepidarApiException) {
            return false;
        }
    }

    public function setDeviceSerial(string $serial): self
    {
        $this->deviceSerial = $serial;
        $this->integrationId = DeviceSerial::integrationId($serial);

        return $this;
    }

    public function getIntegrationId(): ?int
    {
        return $this->integrationId;
    }

    public function getGenerationVersion(): string
    {
        return $this->resolveGenerationVersion();
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $this->bootstrap();

        return $this->send($method, $endpoint, $options, secured: true);
    }

    protected function bootstrap(bool $force = false): void
    {
        if ($this->bootstrapped && ! $force) {
            return;
        }

        $this->validateConfig();

        if (! $this->store->isRegistered()) {
            $this->registerDevice();
        }

        $this->hydrateFromStore();
        $this->ensureCryptoReady();

        if (! $this->token || ! $this->isAuthorized()) {
            $this->login();
        }

        $this->bootstrapped = true;
    }

    protected function validateConfig(): void
    {
        $required = ['base_url', 'username', 'password', 'generation_version', 'device_serial'];

        foreach ($required as $key) {
            if ($key === 'device_serial' && $this->store->isRegistered()) {
                continue;
            }

            $value = $this->config[$key] ?? null;

            if ($key === 'device_serial' && empty($value)) {
                $value = $this->store->get('device_serial') ?? $this->deviceSerial;
            }

            if (empty($value)) {
                throw SepidarApiException::configuration(
                    "SEPIDAR_{$this->envKey($key)} is required. Run: php artisan sepidar:setup"
                );
            }
        }
    }

    protected function envKey(string $key): string
    {
        return match ($key) {
            'base_url' => 'BASE_URL',
            'device_serial' => 'DEVICE_SERIAL',
            'generation_version' => 'GENERATION_VERSION',
            default => strtoupper($key),
        };
    }

    protected function hydrateFromStore(): void
    {
        $this->deviceSerial = $this->config['device_serial']
            ?? $this->store->get('device_serial')
            ?? $this->deviceSerial;

        $this->integrationId = $this->store->get('IntegrationID')
            ?? ($this->deviceSerial ? DeviceSerial::integrationId($this->deviceSerial) : null)
            ?? $this->integrationId;

        $this->token = $this->store->get('Token') ?? $this->token;

        $this->generationVersion = $this->config['generation_version']
            ?? $this->store->get('GenerationVersion')
            ?? $this->generationVersion;
    }

    protected function ensureCryptoReady(): void
    {
        if (! $this->integrationId) {
            throw SepidarApiException::configuration('Device is not registered. Run: php artisan sepidar:setup');
        }

        if (! $this->publicKeyXml) {
            $this->publicKeyXml = PublicKeyResolver::fromStore($this->store);
        }
    }

    protected function send(string $method, string $endpoint, array $options = [], bool|string $secured = true): array|bool
    {
        $url = $this->url($endpoint);
        $request = $this->http($secured);

        $payload = $options['json'] ?? null;
        $query = $options['query'] ?? [];

        $response = match ($method) {
            'get' => $request->get($url, $query),
            'post' => $request->post($url, $payload ?? []),
            'put' => $request->put($url, $payload ?? []),
            'delete' => $request->delete($url, $payload ?? []),
            default => throw SepidarApiException::configuration("Unsupported HTTP method [{$method}]."),
        };

        $this->logRequest($method, $endpoint, $options, $response);

        if ($response->failed()) {
            throw SepidarApiException::fromResponse($response);
        }

        $json = $response->json();

        if (is_bool($json) || is_string($json) || is_int($json) || is_float($json)) {
            return $json;
        }

        return $json ?? [];
    }

    protected function http(bool|string $secured): PendingRequest
    {
        $request = Http::baseUrl($this->apiBaseUrl())
            ->timeout($this->config['timeout'] ?? 30)
            ->acceptJson()
            ->asJson();

        if (! ($this->config['verify_ssl'] ?? false)) {
            $request = $request->withoutVerifying();
        }

        if ($secured === false) {
            return $request;
        }

        return $request->withHeaders($this->buildSepidarHeaders($secured === true));
    }

    protected function buildSepidarHeaders(bool $withAuthorization): array
    {
        $arbitraryCode = (string) Str::uuid();

        $headers = [
            'GenerationVersion' => $this->resolveGenerationVersion(),
            'IntegrationID' => (string) $this->integrationId,
            'ArbitraryCode' => $arbitraryCode,
            'EncArbitraryCode' => RsaEncrypter::encryptArbitraryCode($arbitraryCode, $this->publicKeyXml),
        ];

        if ($withAuthorization && $this->token) {
            $headers['Authorization'] = 'Bearer '.$this->token;
        }

        return $headers;
    }

    protected function resolveGenerationVersion(): string
    {
        if ($this->generationVersion) {
            return (string) $this->generationVersion;
        }

        $result = $this->getPublic('General/GenerationVersion');
        $this->generationVersion = (string) ($result['GenerationVersion'] ?? '101');
        $this->store->put(['GenerationVersion' => $this->generationVersion]);

        return $this->generationVersion;
    }

    protected function apiBaseUrl(): string
    {
        $baseUrl = rtrim($this->config['base_url'], '/');

        return str_ends_with($baseUrl, '/api') ? $baseUrl : $baseUrl.'/api';
    }

    protected function url(string $endpoint): string
    {
        return '/'.ltrim($endpoint, '/');
    }

    protected function logRequest(string $method, string $endpoint, array $options, Response $response): void
    {
        if (! ($this->config['log_requests'] ?? false)) {
            return;
        }

        Log::channel($this->config['log_channel'] ?? 'stack')->info('Sepidar API Request', [
            'method' => strtoupper($method),
            'endpoint' => $endpoint,
            'options' => $options,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);
    }
}
