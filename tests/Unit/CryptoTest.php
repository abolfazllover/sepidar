<?php

namespace Ahmadi\LaravelSepidar\Tests\Unit;

use Ahmadi\LaravelSepidar\Crypto\AesEncrypter;
use Ahmadi\LaravelSepidar\Crypto\RsaEncrypter;
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

    public function test_rsa_encryption_with_sample_public_key(): void
    {
        $encrypted = RsaEncrypter::encrypt((string) Str::uuid(), $this->samplePublicKeyXml());

        $this->assertNotEmpty($encrypted);
        $this->assertTrue(base64_decode($encrypted, true) !== false);
    }
}
