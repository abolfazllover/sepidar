<?php

namespace Ahmadi\LaravelSepidar\Resources;

class AdministrativeDivisionResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('AdministrativeDivisions');
    }
}
