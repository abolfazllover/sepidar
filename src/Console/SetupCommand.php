<?php

namespace Ahmadi\LaravelSepidar\Console;

use Ahmadi\LaravelSepidar\Client\SepidarClient;
use Illuminate\Console\Command;

class SetupCommand extends Command
{
    protected $signature = 'sepidar:setup
                            {--url= : آدرس API سپیدار}
                            {--username= : نام کاربری}
                            {--password= : رمز عبور}
                            {--version= : نسخه API}
                            {--serial= : سریال ۸ کاراکتری دستگاه}';

    protected $description = 'تنظیم و اتصال اولیه به API سپیدار';

    public function handle(SepidarClient $client): int
    {
        $config = config('sepidar');

        $this->info('تنظیمات اتصال به سپیدار');
        $this->newLine();

        $settings = [
            'base_url' => $this->option('url') ?: $this->ask('آدرس API', $config['base_url']),
            'username' => $this->option('username') ?: $this->ask('نام کاربری', $config['username']),
            'password' => $this->option('password') ?: $this->secret('رمز عبور') ?: $config['password'],
            'generation_version' => $this->option('version') ?: $this->ask('نسخه API', $config['generation_version'] ?? '101'),
            'device_serial' => $this->option('serial') ?: $this->ask('سریال دستگاه (۸ کاراکتر)', $config['device_serial']),
        ];

        if (empty($settings['device_serial'])) {
            $this->error('سریال دستگاه الزامی است.');

            return self::FAILURE;
        }

        if (strlen($settings['device_serial']) !== 8) {
            $this->warn('سریال دستگاه معمولاً ۸ کاراکتر است.');
        }

        foreach (['base_url', 'username', 'password', 'generation_version'] as $field) {
            if (empty($settings[$field])) {
                $this->error("فیلد {$field} الزامی است.");

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->line('در حال اتصال...');

        try {
            $client->configure($settings)->connect();

            $this->newLine();
            $this->info('اتصال به سپیدار با موفقیت برقرار شد.');
            $this->table(
                ['تنظیم', 'مقدار'],
                [
                    ['آدرس', $settings['base_url']],
                    ['کاربر', $settings['username']],
                    ['نسخه API', $client->getGenerationVersion()],
                    ['سریال', $settings['device_serial']],
                    ['IntegrationID', $client->getIntegrationId()],
                ]
            );

            $this->newLine();
            $this->comment('این مقادیر را در .env ذخیره کنید:');
            $this->line('SEPIDAR_BASE_URL='.$settings['base_url']);
            $this->line('SEPIDAR_USERNAME='.$settings['username']);
            $this->line('SEPIDAR_PASSWORD='.$settings['password']);
            $this->line('SEPIDAR_GENERATION_VERSION='.$settings['generation_version']);
            $this->line('SEPIDAR_DEVICE_SERIAL='.$settings['device_serial']);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('خطا: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
