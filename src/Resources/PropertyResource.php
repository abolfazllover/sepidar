<?php

namespace Ahmadi\LaravelSepidar\Resources;

class PropertyResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('properties');
    }
}
