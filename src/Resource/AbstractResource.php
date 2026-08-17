<?php

declare(strict_types=1);

namespace JanMuran\SpeedwebApiSdk\Resource;

use JanMuran\SpeedwebApiSdk\Http\HttpClient;

abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClient $http,
    ) {
    }
}
