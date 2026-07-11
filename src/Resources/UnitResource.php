<?php

namespace Ahmadi\LaravelSepidar\Resources;

class UnitResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('Units');
    }
}
