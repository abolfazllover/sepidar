<?php

namespace Ahmadi\LaravelSepidar\Resources;

class CustomerGroupingResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('CustomerGroupings');
    }
}
