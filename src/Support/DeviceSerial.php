<?php

namespace Ahmadi\LaravelSepidar\Support;

class DeviceSerial
{
    public static function integrationId(string $serial): int
    {
        return (int) substr($serial, 0, 4);
    }

    public static function aesKey(string $serial): string
    {
        return $serial.$serial;
    }
}
