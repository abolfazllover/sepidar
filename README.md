# Laravel Sepidar

پکیج لاراول برای اتصال ساده به **API نرم‌افزار سپیدar**.

## نصب

```bash
composer require ahmadi/laravel-sepidar
php artisan vendor:publish --tag=sepidar-config
```

## تنظیمات `.env` — فقط ۴ مورد

```env
SEPIDAR_BASE_URL=http://192.168.20.15:7373
SEPIDAR_USERNAME=admin
SEPIDAR_PASSWORD=your_password
SEPIDAR_GENERATION_VERSION=111
```

## راه‌اندازی (یک‌بار)

سریال ۸ کاراکتری را از نرم‌افزار سپیدar بگیرید و اجرا کنید:

```bash
php artisan sepidar:setup
```

یا:

```bash
php artisan sepidar:setup --serial=100079d4
```

پکیج خودکار:
- دستگاه را Register می‌کند
- کلید RSA را ذخیره می‌کند (`storage/app/sepidar/credentials.json`)
- Login می‌کند و Token را cache می‌کند

**بعد از setup دیگر نیازی به PublicKey، Cypher یا IV نیست.**

### مهاجرت از spidar.json قدیمی

```env
SEPIDAR_LEGACY_JSON_PATH=D:/path/to/spidar.json
```

## استفاده

```php
use Ahmadi\LaravelSepidar\Facades\Sepidar;

// اتصال خودکار در اولین درخواست
$customers = Sepidar::customers()->all();
$items = Sepidar::items()->all();

// یا صریح
Sepidar::connect();
```

```php
// ثبت مشتری
Sepidar::customers()->create([
    'GUID' => (string) Str::uuid(),
    'CustomerType' => 1,
    'Name' => 'علی',
    'LastName' => 'احمدی',
    'Addresses' => [],
]);

// پیش‌فاکتور
Sepidar::quotations()->create([/* ... */]);

// فاکتور از پیش‌فاکتور
Sepidar::invoices()->createFromQuotation($quotationId);
```

## APIها

`customers()` · `items()` · `quotations()` · `invoices()` · `receipts()` · `currencies()` · `stocks()` · `saleTypes()` · `priceNoteItems()` · `administrativeDivisions()` · `customerGroupings()` · `units()` · `properties()` · `banks()` · `bankAccounts()`

## توسعه

```bash
composer test
```

MIT
