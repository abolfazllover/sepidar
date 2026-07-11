<?php

namespace Ahmadi\LaravelSepidar\Resources;

class ItemResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('Items');
    }

    public function image(int $itemId): string
    {
        $response = $this->client->get("Items/{$itemId}/Image/");

        return is_string($response) ? $response : ($response['image'] ?? '');
    }

    public function inventories(): array
    {
        return $this->client->get('Items/Inventories/');
    }
}
