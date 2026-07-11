<?php

namespace Ahmadi\LaravelSepidar\Tests\Unit;

use Ahmadi\LaravelSepidar\Crypto\AesEncrypter;
use Ahmadi\LaravelSepidar\Crypto\RsaEncrypter;
use Ahmadi\LaravelSepidar\Support\CredentialStore;
use Ahmadi\LaravelSepidar\Support\DeviceSerial;
use Ahmadi\LaravelSepidar\Support\PublicKeyResolver;
use Ahmadi\LaravelSepidar\Tests\TestCase;
use Illuminate\Support\Str;

class CryptoTest extends TestCase
{
    public function test_aes_encryption_roundtrip(): void
    {
        $key = '10017ff310017ff3';
        $iv = AesEncrypter::generateIv();

        $encrypted = AesEncrypter::encrypt('1001', $key, $iv);
        $decrypted = AesEncrypter::decrypt($encrypted, $key, base64_encode($iv));

        $this->assertSame('1001', $decrypted);
    }

    public function test_decrypt_production_register_payload(): void
    {
        $serial = '100079d4';
        $cypher = 'ot1IPo2Nid1XBl1py4lmThwi7FkxH2jNukEcAwY0Tj9WRWNwPuom4gi8dh/bzkVTFHFnvq9AW8P6iJJwV6n5zKXrTTaFMYYK5lESsywiOR6/ooe48/DbnVrLZnKiztD+WweVDlRajlya6D+zph1lCqvvF2WkxkxnlhbNNaZT6yVdGWA6N4k/g5m44oPzabDy7iLyczIPzsCDaWJeFgaDNg==';
        $iv = 'ELsf59p/7kBcDAh7o4dQrw==';

        $xml = AesEncrypter::decrypt($cypher, DeviceSerial::aesKey($serial), $iv);

        $this->assertStringContainsString('<RSAKeyValue>', $xml);
    }

    public function test_public_key_resolver_from_store(): void
    {
        $store = new CredentialStore($this->credentialsPath);
        $xml = PublicKeyResolver::fromStore($store);

        $this->assertStringContainsString('<Modulus>', $xml);
    }

    public function test_uuid_is_packed_to_sixteen_bytes(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $packed = RsaEncrypter::packUuid($uuid);

        $this->assertSame(16, strlen($packed));
    }

    public function test_rsa_encrypts_packed_uuid(): void
    {
        $store = new CredentialStore($this->credentialsPath);
        $xml = PublicKeyResolver::fromStore($store);
        $encrypted = RsaEncrypter::encryptArbitraryCode((string) Str::uuid(), $xml);

        $this->assertNotEmpty($encrypted);
    }
}
