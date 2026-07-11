<?php

namespace Ahmadi\LaravelSepidar\Resources;

class AuthResource extends Resource
{
    public function login(?string $username = null, ?string $password = null): array
    {
        return $this->client->login($username, $password);
    }

    public function isAuthorized(): bool
    {
        return $this->client->isAuthorized();
    }
}
