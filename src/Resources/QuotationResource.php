<?php

namespace Ahmadi\LaravelSepidar\Resources;

class QuotationResource extends Resource
{
    public function all(?string $fromDate = null, ?string $toDate = null): array
    {
        $query = array_filter([
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);

        return $this->client->get('Quotations', $query);
    }

    public function find(int $id): array
    {
        return $this->client->get("Quotations/{$id}");
    }

    public function create(array $data): array
    {
        return $this->client->post('Quotations/', $data);
    }

    public function createBatch(array $quotations): array
    {
        return $this->client->post('Quotations/Batch/', $quotations);
    }

    public function close(int $quotationId): array
    {
        return $this->client->post("Quotations/{$quotationId}/Close/");
    }

    public function closeBatch(array $quotationIds): array
    {
        return $this->client->post('Quotations/Close/Batch', $quotationIds);
    }

    public function unclose(int $quotationId): array
    {
        return $this->client->post("Quotations/{$quotationId}/UnClose/");
    }

    public function uncloseBatch(array $quotationIds): array
    {
        return $this->client->post('Quotations/UnClose/Batch', $quotationIds);
    }

    public function delete(int $quotationId): array
    {
        return $this->client->delete("Quotations/{$quotationId}");
    }

    public function deleteBatch(array $quotationIds): array
    {
        return $this->client->delete('Quotations/Batch', $quotationIds);
    }
}
