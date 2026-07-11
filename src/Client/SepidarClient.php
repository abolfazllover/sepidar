<?php

namespace Ahmadi\LaravelSepidar\Client;

use Ahmadi\LaravelSepidar\Contracts\SepidarClientInterface;
use Ahmadi\LaravelSepidar\Crypto\AesEncrypter;
use Ahmadi\LaravelSepidar\Crypto\RsaEncrypter;
use Ahmadi\LaravelSepidar\Exceptions\SepidarApiException;
use Ahmadi\LaravelSepidar\Support\DeviceSerial;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SepidarClient implements SepidarClientInterface
{
    protected array $config;

    protected ?string $token = null;

    protected ?string $publicKeyXml = null;

    protected ?int $integrationId = null;

    protected ?string $deviceSerial = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->deviceSerial = $config['device_serial'] ?? null;
        $this->integrationId = isset($config['integration_id']) ? (int) $config['integration_id'] : null;
        $this->publicKeyXml = $config['public_key'] ?? null;

        if (($config['cache_token'] ?? true) && $cached = Cache::get($config['token_cache_key'] ?? 'sepidar.jwt_token')) {
            $this->token = $cached;
        }
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

    /**
     * درخواست بدون هدر احراز هویت سپیدار.
     */
    public function getPublic(string $endpoint, array $query = []): array
    {
        return $this->send('get', $endpoint, ['query' => $query], secured: false);
    }

    public function postPublic(string $endpoint, array $data = []): array
    {
        return $this->send('post', $endpoint, ['json' => $data], secured: false);
    }

    /**
     * ثبت دستگاه و دریافت کلید عمومی RSA.
     */
    public function registerDevice(?string $serial = null): array
    {
        $serial = $serial ?? $this->deviceSerial;

        if (! $serial) {
            throw SepidarApiException::configuration('Device serial is required for registration.');
        }

        $integrationId = DeviceSerial::integrationId($serial);
        $key = DeviceSerial::aesKey($serial);
        $iv = AesEncrypter::generateIv();

        $response = $this->postPublic('Devices/Register', [
            'Cypher' => AesEncrypter::encrypt((string) $integrationId, $key, $iv),
            'IV' => base64_encode($iv),
            'IntegrationID' => $integrationId,
        ]);

        $publicKeyXml = AesEncrypter::decrypt($response['Cypher'], $key, $response['IV']);

        $this->deviceSerial = $serial;
        $this->integrationId = $integrationId;
        $this->publicKeyXml = $publicKeyXml;

        return [
            'DeviceTitle' => $response['DeviceTitle'] ?? null,
            'IntegrationID' => $integrationId,
            'PublicKey' => $publicKeyXml,
        ];
    }

    /**
     * ورود و دریافت JWT Token.
     */
    public function login(?string $username = null, ?string $password = null): array
    {
        $username = $username ?? $this->config['username'] ?? null;
        $password = $password ?? $this->config['password'] ?? null;

        if (! $username || ! $password) {
            throw SepidarApiException::configuration('Username and password are required.');
        }

        $this->ensureDeviceIsReady();

        $response = $this->send('post', 'users/login', [
            'json' => [
                'UserName' => $username,
                'PasswordHash' => md5($password),
            ],
        ], secured: 'login');

        $this->setToken($response['Token'] ?? '');

        return $response;
    }

    public function authenticate(): self
    {
        if (! $this->token) {
            $this->login();
        }

        return $this;
    }

    public function isAuthorized(): bool
    {
        $this->ensureDeviceIsReady();

        if (! $this->token) {
            return false;
        }

        $response = $this->send('get', 'IsAuthorized', secured: true);

        return $response === true;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        if ($token && ($this->config['cache_token'] ?? true)) {
            Cache::forever($this->config['token_cache_key'] ?? 'sepidar.jwt_token', $token);
        }

        return $this;
    }

    public function setPublicKey(string $publicKeyXml): self
    {
        $this->publicKeyXml = $publicKeyXml;

        return $this;
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

    public function getPublicKey(): ?string
    {
        return $this->publicKeyXml;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $this->ensureDeviceIsReady();

        if (! $this->token) {
            $this->login();
        }

        return $this->send($method, $endpoint, $options, secured: true);
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
        $this->ensureDeviceIsReady();

        $arbitraryCode = (string) Str::uuid();

        $headers = [
            'GenerationVersion' => (string) ($this->config['generation_version'] ?? '101'),
            'IntegrationID' => (string) $this->integrationId,
            'ArbitraryCode' => $arbitraryCode,
            'EncArbitraryCode' => RsaEncrypter::encrypt($arbitraryCode, $this->publicKeyXml),
        ];

        if ($withAuthorization && $this->token) {
            $headers['Authorization'] = 'Bearer '.$this->token;
        }

        return $headers;
    }

    protected function ensureDeviceIsReady(): void
    {
        if ($this->integrationId === null && $this->deviceSerial) {
            $this->integrationId = DeviceSerial::integrationId($this->deviceSerial);
        }

        if ($this->integrationId === null) {
            throw SepidarApiException::configuration('Integration ID or device serial is required.');
        }

        if (! $this->publicKeyXml) {
            throw SepidarApiException::configuration('Public key is required. Run registerDevice() first or set SEPIDAR_PUBLIC_KEY.');
        }
    }

    protected function apiBaseUrl(): string
    {
        return rtrim($this->config['base_url'], '/').'/api';
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
