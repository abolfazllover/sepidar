<?php

namespace Ahmadi\LaravelSepidar\Crypto;

use Ahmadi\LaravelSepidar\Exceptions\SepidarException;
use SimpleXMLElement;

class RsaEncrypter
{
    /**
     * رمزنگاری متن با کلید عمومی RSA (PKCS#1 v1.5) — خروجی Base64.
     */
    public static function encrypt(string $plaintext, string $publicKeyXml): string
    {
        $pem = self::publicKeyPemFromXml($publicKeyXml);

        $publicKey = openssl_pkey_get_public($pem);

        if ($publicKey === false) {
            throw new SepidarException('Invalid Sepidar RSA public key.');
        }

        $encrypted = '';
        $success = openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

        if (! $success) {
            throw new SepidarException('RSA encryption failed: '.openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    public static function publicKeyPemFromXml(string $xml): string
    {
        $document = new SimpleXMLElement($xml);

        $modulus = base64_decode((string) $document->Modulus, true);
        $exponent = base64_decode((string) $document->Exponent, true);

        if ($modulus === false || $exponent === false) {
            throw new SepidarException('Unable to parse Sepidar RSA public key XML.');
        }

        $der = self::encodePublicKeyDer($modulus, $exponent);

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($der), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    private static function encodePublicKeyDer(string $modulus, string $exponent): string
    {
        $modulus = self::encodeAsn1Integer($modulus);
        $exponent = self::encodeAsn1Integer($exponent);
        $rsaPublicKey = self::encodeAsn1Sequence($modulus.$exponent);

        $algorithmIdentifier = hex2bin('300D06092A864886F70D0101010500');
        $bitString = "\x00".$rsaPublicKey;

        return self::encodeAsn1Sequence(
            $algorithmIdentifier
            .self::encodeAsn1BitString($bitString)
        );
    }

    private static function encodeAsn1Integer(string $value): string
    {
        if ($value !== '' && (ord($value[0]) & 0x80)) {
            $value = "\x00".$value;
        }

        return "\x02".self::encodeLength(strlen($value)).$value;
    }

    private static function encodeAsn1BitString(string $value): string
    {
        return "\x03".self::encodeLength(strlen($value)).$value;
    }

    private static function encodeAsn1Sequence(string $value): string
    {
        return "\x30".self::encodeLength(strlen($value)).$value;
    }

    private static function encodeLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($bytes)).$bytes;
    }
}
