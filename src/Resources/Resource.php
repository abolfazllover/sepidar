<?php

namespace Ahmadi\LaravelSepidar\Resources;

use Ahmadi\LaravelSepidar\Client\SepidarClient;

abstract class Resource
{
    public function __construct(
        protected readonly SepidarClient $client
    ) {
    }
}
