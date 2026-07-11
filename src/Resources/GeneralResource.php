<?php

namespace Ahmadi\LaravelSepidar\Resources;

class GeneralResource extends Resource
{
    public function generationVersion(): array
    {
        return $this->client->getPublic('General/GenerationVersion');
    }
}
