# Laravel Sepidar

پکیج لاراول برای ارتباط با **وب‌سرویس نرم‌افزار حسابداری سپیدار** (E-Commerce Web Service v1.0.0 / API 101).

## نصب

```bash
composer require ahmadi/laravel-sepidar
```

```bash
php artisan vendor:publish --tag=sepidar-config
```

## تنظیمات `.env`

```env
SEPIDAR_BASE_URL=http://192.168.1.100:7373
SEPIDAR_GENERATION_VERSION=101
SEPIDAR_DEVICE_SERIAL=10017ff3
SEPIDAR_INTEGRATION_ID=1001
SEPIDAR_PUBLIC_KEY="<RSAKeyValue>...</RSAKeyValue>"
SEPIDAR_USERNAME=your_user
SEPIDAR_PASSWORD=your_password
SEPIDAR_CACHE_TOKEN=true
```

## راه‌اندازی اولیه

### ۱. ثبت دستگاه (یک‌بار)

در نرم‌افزار سپیدار از بخش «سفارش‌گیری» یک دستگاه با سریال ۸ کاراکتری بسازید:

```php
use Ahmadi\LaravelSepidar\Facades\Sepidar;

$result = Sepidar::devices()->register('10017ff3');

// PublicKey را در .env ذخیره کنید:
// SEPIDAR_PUBLIC_KEY="<RSAKeyValue>...</RSAKeyValue>"
```

### ۲. ورود (Login)

```php
Sepidar::auth()->login();

// یا با نام کاربری/رمز جداگانه
Sepidar::auth()->login('username', 'password');
```

توکن JWT به‌صورت خودکار کش می‌شود و در درخواست‌های بعدی استفاده می‌گردد.

## نمونه استفاده

```php
use Ahmadi\LaravelSepidar\Facades\Sepidar;
use Illuminate\Support\Str;

// اطلاعات عمومی
$version = Sepidar::general()->generationVersion();

// مشتریان
$customers = Sepidar::customers()->all();
$customer = Sepidar::customers()->find(1);

Sepidar::customers()->create([
    'GUID' => (string) Str::uuid(),
    'CustomerType' => 1,
    'Name' => 'علی',
    'LastName' => 'احمدی',
    'Addresses' => [],
]);

// کالاها
$items = Sepidar::items()->all();
$inventories = Sepidar::items()->inventories();

// پیش‌فاکتور
$quotation = Sepidar::quotations()->create([
    'GUID' => (string) Str::uuid(),
    'CurrencyRef' => 1,
    'Rate' => 1,
    'ExpirationDate' => now()->addDays(7)->toIso8601String(),
    'CustomerRef' => 1,
    'SaleTypeRef' => 1,
    'DiscountOnCustomer' => 0,
    'Price' => 100000,
    'Discount' => 0,
    'Tax' => 0,
    'Duty' => 0,
    'Addition' => 0,
    'Items' => [/* ... */],
]);

// فاکتور فروش
$invoice = Sepidar::invoices()->createFromQuotation($quotation['ID']);

// رسید دریافت
Sepidar::receipts()->createBasedOnInvoice([
    'Guid' => (string) Str::uuid(),
    'Date' => now()->toIso8601String(),
    'Description' => 'پرداخت آنلاین',
    'Discount' => 0,
    'InvoiceID' => $invoice['InvoiceID'],
    'Draft' => [[
        'Date' => now()->toIso8601String(),
        'Number' => '12345',
        'BankAccountID' => 1,
        'Amount' => $invoice['NetPrice'],
    ]],
]);
```

## APIهای پشتیبانی‌شده

| Resource | متدها |
|----------|--------|
| `devices()` | `register()` |
| `auth()` | `login()`, `isAuthorized()` |
| `general()` | `generationVersion()` |
| `administrativeDivisions()` | `all()` |
| `customerGroupings()` | `all()` |
| `customers()` | `all()`, `find()`, `create()`, `update()` |
| `units()` | `all()` |
| `properties()` | `all()` |
| `stocks()` | `all()` |
| `items()` | `all()`, `image()`, `inventories()` |
| `saleTypes()` | `all()` |
| `priceNoteItems()` | `all()` |
| `currencies()` | `all()` |
| `quotations()` | CRUD + batch + close/unclose |
| `invoices()` | `all()`, `find()`, `create()`, `createBatch()`, `createFromQuotation()` |
| `banks()` | `all()` |
| `bankAccounts()` | `all()` |
| `receipts()` | `createBasedOnInvoice()` |

## امنیت

- ثبت دستگاه: **AES-128-CBC**
- هر درخواست: **GUID یکتا** + **RSA PKCS#1 v1.5**
- ورود: رمز عبور به صورت **MD5**
- API: **JWT Token**

## توسعه

```bash
composer install
composer test
```

## لایسنس

MIT
