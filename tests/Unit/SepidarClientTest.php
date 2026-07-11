<?php

namespace Ahmadi\LaravelSepidar\Tests\Unit;

use Ahmadi\LaravelSepidar\Client\SepidarClient;
use Ahmadi\LaravelSepidar\Contracts\SepidarClientInterface;
use Ahmadi\LaravelSepidar\SepidarManager;
use Ahmadi\LaravelSepidar\Support\DeviceSerial;
use Ahmadi\LaravelSepidar\Tests\TestCase;

class SepidarClientTest extends TestCase
{
    public function test_client_is_bound_in_container(): void
    {
        $client = $this->app->make(SepidarClientInterface::class);

        $this->assertInstanceOf(SepidarClient::class, $client);
    }

    public function test_manager_exposes_resources(): void
    {
        $manager = $this->manager();

        $this->assertInstanceOf(SepidarManager::class, $manager);
        $this->assertSame(1001, $manager->client()->getIntegrationId());
    }

    public function test_integration_id_is_extracted_from_serial(): void
    {
        $this->assertSame(1001, DeviceSerial::integrationId('10017ff3'));
        $this->assertSame('10017ff310017ff3', DeviceSerial::aesKey('10017ff3'));
    }
}
