<?php

namespace Ahmadi\LaravelSepidar\Resources;

class ReceiptResource extends Resource
{
    public function createBasedOnInvoice(array $data): array
    {
        return $this->client->post('Receipts/BasedOnInvoice/', $data);
    }
}
