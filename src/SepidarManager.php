<?php

namespace Ahmadi\LaravelSepidar;

use Ahmadi\LaravelSepidar\Client\SepidarClient;
use Ahmadi\LaravelSepidar\Resources\AdministrativeDivisionResource;
use Ahmadi\LaravelSepidar\Resources\AuthResource;
use Ahmadi\LaravelSepidar\Resources\BankAccountResource;
use Ahmadi\LaravelSepidar\Resources\BankResource;
use Ahmadi\LaravelSepidar\Resources\CurrencyResource;
use Ahmadi\LaravelSepidar\Resources\CustomerGroupingResource;
use Ahmadi\LaravelSepidar\Resources\CustomerResource;
use Ahmadi\LaravelSepidar\Resources\DeviceResource;
use Ahmadi\LaravelSepidar\Resources\GeneralResource;
use Ahmadi\LaravelSepidar\Resources\InvoiceResource;
use Ahmadi\LaravelSepidar\Resources\ItemResource;
use Ahmadi\LaravelSepidar\Resources\PriceNoteItemResource;
use Ahmadi\LaravelSepidar\Resources\PropertyResource;
use Ahmadi\LaravelSepidar\Resources\QuotationResource;
use Ahmadi\LaravelSepidar\Resources\ReceiptResource;
use Ahmadi\LaravelSepidar\Resources\SaleTypeResource;
use Ahmadi\LaravelSepidar\Resources\StockResource;
use Ahmadi\LaravelSepidar\Resources\UnitResource;

class SepidarManager
{
    public function __construct(
        protected SepidarClient $client
    ) {
    }

    public function client(): SepidarClient
    {
        return $this->client;
    }

    public function connect(): SepidarClient
    {
        return $this->client->connect();
    }

    public function devices(): DeviceResource
    {
        return new DeviceResource($this->client);
    }

    public function auth(): AuthResource
    {
        return new AuthResource($this->client);
    }

    public function general(): GeneralResource
    {
        return new GeneralResource($this->client);
    }

    public function administrativeDivisions(): AdministrativeDivisionResource
    {
        return new AdministrativeDivisionResource($this->client);
    }

    public function customerGroupings(): CustomerGroupingResource
    {
        return new CustomerGroupingResource($this->client);
    }

    public function customers(): CustomerResource
    {
        return new CustomerResource($this->client);
    }

    public function units(): UnitResource
    {
        return new UnitResource($this->client);
    }

    public function properties(): PropertyResource
    {
        return new PropertyResource($this->client);
    }

    public function stocks(): StockResource
    {
        return new StockResource($this->client);
    }

    public function items(): ItemResource
    {
        return new ItemResource($this->client);
    }

    public function saleTypes(): SaleTypeResource
    {
        return new SaleTypeResource($this->client);
    }

    public function priceNoteItems(): PriceNoteItemResource
    {
        return new PriceNoteItemResource($this->client);
    }

    public function currencies(): CurrencyResource
    {
        return new CurrencyResource($this->client);
    }

    public function quotations(): QuotationResource
    {
        return new QuotationResource($this->client);
    }

    public function invoices(): InvoiceResource
    {
        return new InvoiceResource($this->client);
    }

    public function banks(): BankResource
    {
        return new BankResource($this->client);
    }

    public function bankAccounts(): BankAccountResource
    {
        return new BankAccountResource($this->client);
    }

    public function receipts(): ReceiptResource
    {
        return new ReceiptResource($this->client);
    }
}
