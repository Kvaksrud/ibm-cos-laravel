<?php

namespace Kvaksrud\IbmCos\Objects;

use Spatie\LaravelData\Data;

class CosResponse extends Data
{
    public function __construct(
        public bool $success,
        public int $code,
        public object|null $data,
        public array|null $headers,
        public string|null $message
    )
    {
    }
}
