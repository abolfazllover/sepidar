<?php

namespace Ahmadi\LaravelSepidar\Resources;

class BankAccountResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('BankAccounts');
    }
}
