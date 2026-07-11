<?php

namespace Ahmadi\LaravelSepidar\Exceptions;

use Illuminate\Http\Client\Response;

class SepidarApiException extends SepidarException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly ?array $responseBody = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function fromResponse(Response $response): self
    {
        $body = $response->json();
        $message = $body['Message'] ?? $body['message'] ?? $body['error'] ?? $response->body() ?: 'Sepidar API request failed';

        return new self(
            message: is_string($message) ? $message : json_encode($message),
            statusCode: $response->status(),
            responseBody: is_array($body) ? $body : null,
        );
    }

    public static function configuration(string $message): self
    {
        return new self($message, 0);
    }
}
