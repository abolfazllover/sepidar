<?php

namespace Ahmadi\LaravelSepidar\Facades;

use Ahmadi\LaravelSepidar\SepidarManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Ahmadi\LaravelSepidar\Client\SepidarClient connect()
 * @method static \Ahmadi\LaravelSepidar\Client\SepidarClient client()
 * @method static \Ahmadi\LaravelSepidar\Resources\DeviceResource devices()
 * @method static \Ahmadi\LaravelSepidar\Resources\AuthResource auth()
 * @method static \Ahmadi\LaravelSepidar\Resources\GeneralResource general()
 * @method static \Ahmadi\LaravelSepidar\Resources\AdministrativeDivisionResource administrativeDivisions()
 * @method static \Ahmadi\LaravelSepidar\Resources\CustomerGroupingResource customerGroupings()
 * @method static \Ahmadi\LaravelSepidar\Resources\CustomerResource customers()
 * @method static \Ahmadi\LaravelSepidar\Resources\UnitResource units()
 * @method static \Ahmadi\LaravelSepidar\Resources\PropertyResource properties()
 * @method static \Ahmadi\LaravelSepidar\Resources\StockResource stocks()
 * @method static \Ahmadi\LaravelSepidar\Resources\ItemResource items()
 * @method static \Ahmadi\LaravelSepidar\Resources\SaleTypeResource saleTypes()
 * @method static \Ahmadi\LaravelSepidar\Resources\PriceNoteItemResource priceNoteItems()
 * @method static \Ahmadi\LaravelSepidar\Resources\CurrencyResource currencies()
 * @method static \Ahmadi\LaravelSepidar\Resources\QuotationResource quotations()
 * @method static \Ahmadi\LaravelSepidar\Resources\InvoiceResource invoices()
 * @method static \Ahmadi\LaravelSepidar\Resources\BankResource banks()
 * @method static \Ahmadi\LaravelSepidar\Resources\BankAccountResource bankAccounts()
 * @method static \Ahmadi\LaravelSepidar\Resources\ReceiptResource receipts()
 *
 * @see \Ahmadi\LaravelSepidar\SepidarManager
 */
class Sepidar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SepidarManager::class;
    }
}
