<?php

namespace Ahmadi\LaravelSepidar\Tests;

use Ahmadi\LaravelSepidar\SepidarManager;
use Ahmadi\LaravelSepidar\SepidarServiceProvider;
use Ahmadi\LaravelSepidar\Support\CredentialStore;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected string $credentialsPath;

    protected function setUp(): void
    {
        $this->credentialsPath = sys_get_temp_dir().'/sepidar-test-'.uniqid().'.json';

        parent::setUp();

        $store = new CredentialStore($this->credentialsPath);
        $store->put([
            'Cypher' => 'ot1IPo2Nid1XBl1py4lmThwi7FkxH2jNukEcAwY0Tj9WRWNwPuom4gi8dh/bzkVTFHFnvq9AW8P6iJJwV6n5zKXrTTaFMYYK5lESsywiOR6/ooe48/DbnVrLZnKiztD+WweVDlRajlya6D+zph1lCqvvF2WkxkxnlhbNNaZT6yVdGWA6N4k/g5m44oPzabDy7iLyczIPzsCDaWJeFgaDNg==',
            'IV' => 'ELsf59p/7kBcDAh7o4dQrw==',
            'device_serial' => '100079d4',
            'IntegrationID' => 1000,
            'GenerationVersion' => '111',
        ]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->credentialsPath)) {
            unlink($this->credentialsPath);
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SepidarServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('sepidar.base_url', 'http://sepidar.test');
        $app['config']->set('sepidar.username', 'admin');
        $app['config']->set('sepidar.password', 'secret');
        $app['config']->set('sepidar.generation_version', '111');
        $app['config']->set('sepidar.credentials_path', $this->credentialsPath ?? sys_get_temp_dir().'/sepidar-test.json');
    }

    protected function samplePublicKeyXml(): string
    {
        return <<<'XML'
<RSAKeyValue>
<Modulus>yUMZ3QUs5c0dMJp0mAWzZFlzATNmuxol0N+NlOY1TZXW/q8YCrdWzhV6iLANJe6wEblDugUQgYK/Ue30ld8T7Q==</Modulus>
<Exponent>AQAB</Exponent>
</RSAKeyValue>
XML;
    }

    protected function manager(): SepidarManager
    {
        return $this->app->make(SepidarManager::class);
    }
}
