<?php

namespace Ahmadi\LaravelSepidar\Support;

class DeviceSerial
{
    /**
     * استخراج IntegrationID از ۴ کاراکتر اول سریال دستگاه.
     * مثال: 10017ff3 → 1001
     */
    public static function integrationId(string $serial): int
    {
        return (int) substr($serial, 0, 4);
    }

    /**
     * کلید AES از الحاق دو بار سریال دستگاه.
     */
    public static function aesKey(string $serial): string
    {
        return $serial.$serial;
    }
}
