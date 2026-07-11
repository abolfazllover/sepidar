<?php

namespace Ahmadi\LaravelSepidar\Resources;

class CustomerResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('Customers');
    }

    public function find(int $customerId): array
    {
        return $this->client->get("Customers/{$customerId}");
    }

    public function create(array $data): array
    {
        return $this->client->post('Customers', $data);
    }

    public function update(int $customerId, array $data): array
    {
        return $this->client->put("Customers/{$customerId}", $data);
    }
}
