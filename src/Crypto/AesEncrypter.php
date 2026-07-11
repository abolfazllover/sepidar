<?php

namespace Ahmadi\LaravelSepidar\Crypto;

use Ahmadi\LaravelSepidar\Exceptions\SepidarException;
use RuntimeException;

class AesEncrypter
{
    private const CIPHER = 'AES-128-CBC';

    public static function encrypt(string $plaintext, string $key, string $iv): string
    {
        $encrypted = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            self::normalizeKey($key),
            OPENSSL_RAW_DATA,
            self::normalizeIv($iv)
        );

        if ($encrypted === false) {
            throw new SepidarException('AES encryption failed: '.openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    public static function decrypt(string $cypherBase64, string $key, string $ivBase64): string
    {
        $decrypted = openssl_decrypt(
            base64_decode($cypherBase64, true) ?: '',
            self::CIPHER,
            self::normalizeKey($key),
            OPENSSL_RAW_DATA,
            self::normalizeIv(base64_decode($ivBase64, true) ?: $ivBase64)
        );

        if ($decrypted === false) {
            throw new SepidarException('AES decryption failed: '.openssl_error_string());
        }

        return $decrypted;
    }

    public static function generateIv(): string
    {
        return random_bytes(openssl_cipher_iv_length(self::CIPHER));
    }

    private static function normalizeKey(string $key): string
    {
        $length = strlen($key);

        if ($length === 16) {
            return $key;
        }

        if ($length > 16) {
            return substr($key, 0, 16);
        }

        return str_pad($key, 16, "\0");
    }

    private static function normalizeIv(string $iv): string
    {
        $required = openssl_cipher_iv_length(self::CIPHER);

        if (strlen($iv) === $required) {
            return $iv;
        }

        if (strlen($iv) > $required) {
            return substr($iv, 0, $required);
        }

        throw new RuntimeException('Invalid IV length for AES-128-CBC.');
    }
}
