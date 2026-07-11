<?php

namespace Ahmadi\LaravelSepidar\Support;

use Ahmadi\LaravelSepidar\Crypto\AesEncrypter;
use Ahmadi\LaravelSepidar\Exceptions\SepidarException;

class PublicKeyResolver
{
    public static function fromStore(CredentialStore $store): string
    {
        $serial = $store->get('device_serial');
        $cypher = $store->get('Cypher');
        $iv = $store->get('IV');

        if (! $serial || ! $cypher || ! $iv) {
            throw new SepidarException('Device is not registered yet. Run: php artisan sepidar:setup');
        }

        return AesEncrypter::decrypt($cypher, DeviceSerial::aesKey($serial), $iv);
    }

    public static function modulus(string $publicKeyXml): string
    {
        return (string) (new \SimpleXMLElement($publicKeyXml))->Modulus;
    }

    public static function exponent(string $publicKeyXml): string
    {
        return (string) (new \SimpleXMLElement($publicKeyXml))->Exponent;
    }
}
