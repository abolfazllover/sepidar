<?php

namespace Ahmadi\LaravelSepidar\Resources;

class CurrencyResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('Currencies');
    }
}
