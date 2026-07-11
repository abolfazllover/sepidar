<?php

namespace Ahmadi\LaravelSepidar\Resources;

class BankResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('banks/');
    }
}
