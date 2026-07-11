<?php

namespace Ahmadi\LaravelSepidar\Console;

use Ahmadi\LaravelSepidar\Client\SepidarClient;
use Illuminate\Console\Command;

class SetupCommand extends Command
{
    protected $signature = 'sepidar:setup {--serial= : سریال ۸ کاراکتری دستگاه}';

    protected $description = 'اتصال اولیه به API سپیدar (ثبت دستگاه + ورود)';

    public function handle(SepidarClient $client): int
    {
        $serial = $this->option('serial') ?: $this->ask('سریال دستگاه (۸ کاراکتر)');

        if (! $serial) {
            $this->error('سریال دستگاه الزامی است.');

            return self::FAILURE;
        }

        try {
            $client->setDeviceSerial($serial);
            $client->connect();

            $this->info('اتصال به سپیدar با موفقیت برقرار شد.');
            $this->line('IntegrationID: '.$client->getIntegrationId());
            $this->line('GenerationVersion: '.$client->getGenerationVersion());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('خطا: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
