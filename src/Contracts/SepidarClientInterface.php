<?php

namespace Ahmadi\LaravelSepidar\Contracts;

interface SepidarClientInterface
{
    /**
     * ارسال درخواست GET به API سپیدار.
     */
    public function get(string $endpoint, array $query = []): array;

    /**
     * ارسال درخواست POST به API سپیدار.
     */
    public function post(string $endpoint, array $data = []): array;

    /**
     * ارسال درخواست PUT به API سپیدار.
     */
    public function put(string $endpoint, array $data = []): array;

    /**
     * ارسال درخواست DELETE به API سپیدار.
     */
    public function delete(string $endpoint, array $data = []): array;
}
