<?php

namespace Ahmadi\LaravelSepidar\Resources;

class SaleTypeResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('SaleTypes');
    }
}
