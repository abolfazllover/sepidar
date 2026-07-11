<?php

namespace Ahmadi\LaravelSepidar\Tests;

use Ahmadi\LaravelSepidar\SepidarManager;
use Ahmadi\LaravelSepidar\SepidarServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SepidarServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('sepidar.base_url', 'http://sepidar.test');
        $app['config']->set('sepidar.generation_version', '101');
        $app['config']->set('sepidar.device_serial', '10017ff3');
        $app['config']->set('sepidar.integration_id', 1001);
        $app['config']->set('sepidar.public_key', $this->samplePublicKeyXml());
        $app['config']->set('sepidar.username', 'test');
        $app['config']->set('sepidar.password', 'secret');
        $app['config']->set('sepidar.cache_token', false);
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
