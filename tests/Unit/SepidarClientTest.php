<?php

namespace Ahmadi\LaravelSepidar\Tests\Unit;

use Ahmadi\LaravelSepidar\Client\SepidarClient;
use Ahmadi\LaravelSepidar\Contracts\SepidarClientInterface;
use Ahmadi\LaravelSepidar\SepidarManager;
use Ahmadi\LaravelSepidar\Support\CredentialStore;
use Ahmadi\LaravelSepidar\Support\DeviceSerial;
use Ahmadi\LaravelSepidar\Tests\TestCase;

class SepidarClientTest extends TestCase
{
    public function test_client_is_bound_in_container(): void
    {
        $client = $this->app->make(SepidarClientInterface::class);

        $this->assertInstanceOf(SepidarClient::class, $client);
    }

    public function test_manager_loads_stored_credentials(): void
    {
        $manager = $this->manager();

        $this->assertInstanceOf(SepidarManager::class, $manager);
        $this->assertSame(1000, $manager->client()->getIntegrationId());
        $this->assertSame('111', $manager->client()->getGenerationVersion());
    }

    public function test_integration_id_is_extracted_from_serial(): void
    {
        $this->assertSame(1000, DeviceSerial::integrationId('100079d4'));
    }

    public function test_credential_store_detects_registration(): void
    {
        $store = new CredentialStore($this->credentialsPath);

        $this->assertTrue($store->isRegistered());
        $this->assertSame('100079d4', $store->get('device_serial'));
    }
}
