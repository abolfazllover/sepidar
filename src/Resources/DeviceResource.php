<?php

namespace Ahmadi\LaravelSepidar\Resources;

class DeviceResource extends Resource
{
    public function register(?string $serial = null): array
    {
        return $this->client->registerDevice($serial);
    }
}
