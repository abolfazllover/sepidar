<?php

namespace Ahmadi\LaravelSepidar\Resources;

class StockResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('Stocks');
    }
}
