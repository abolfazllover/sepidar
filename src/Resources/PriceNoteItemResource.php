<?php

namespace Ahmadi\LaravelSepidar\Resources;

class PriceNoteItemResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('PriceNoteItems');
    }
}
