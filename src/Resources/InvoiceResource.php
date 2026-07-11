<?php

namespace Ahmadi\LaravelSepidar\Resources;

class InvoiceResource extends Resource
{
    public function all(): array
    {
        return $this->client->get('invoices/');
    }

    public function find(int $id): array
    {
        return $this->client->get("invoices/{$id}");
    }

    public function create(array $data): array
    {
        return $this->client->post('invoices/', $data);
    }

    public function createBatch(array $invoices): array
    {
        return $this->client->post('Invoices/Batch/', $invoices);
    }

    public function createFromQuotation(int $quotationId): array
    {
        return $this->client->post('Invoices/BasedOnQuotation/', [
            'QuatationID' => $quotationId,
        ]);
    }
}
