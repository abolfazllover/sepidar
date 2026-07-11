# Laravel Sepidar

پکیج لارavel برای اتصال ساده به **API نرم‌افزار سپیدar**.

## نصب

```bash
composer require ahmadi/laravel-sepidar
php artisan vendor:publish --tag=sepidar-config
```

## تنظیمات `.env`

```env
SEPIDAR_BASE_URL=http://192.168.20.15:7373
SEPIDAR_USERNAME=admin
SEPIDAR_PASSWORD=your_password
SEPIDAR_GENERATION_VERSION=111
SEPIDAR_DEVICE_SERIAL=100079d4
```

سریال ۸ کاراکتری را از نرم‌افزار سپیدar → بخش «سفارش‌گیری» → تعریف دستگاه بگیرید.

## راه‌اندازی

```bash
php artisan sepidar:setup
```

دستور به‌صورت تعاملی **هر ۵ مورد** را می‌پرسد (یا از `.env` پیش‌فرض می‌گیرد):

```bash
php artisan sepidar:setup \
  --url=http://192.168.20.15:7373 \
  --username=admin \
  --password=secret \
  --version=111 \
  --serial=100079d4
```

پکیج خودکار Register، ذخیره credentials و Login را انجام می‌دهد.

### مهاجرت از spidar.json قدیمی

```env
SEPIDAR_LEGACY_JSON_PATH=D:/path/to/spidar.json
```

## استفاده

```php
use Ahmadi\LaravelSepidar\Facades\Sepidar;

$customers = Sepidar::customers()->all();
$items = Sepidar::items()->all();
```

## APIها

`customers()` · `items()` · `quotations()` · `invoices()` · `receipts()` · `currencies()` · `stocks()` · `saleTypes()` · `priceNoteItems()` · `administrativeDivisions()` · `customerGroupings()` · `units()` · `properties()` · `banks()` · `bankAccounts()`

MIT
