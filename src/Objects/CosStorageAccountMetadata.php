<?php

namespace Kvaksrud\IbmCos\Objects;

use Exception;
use Spatie\LaravelData\Data;
use function PHPUnit\Framework\throwException;

class CosStorageAccountMetadata extends Data
{
    public function __construct(
        public string $key,
        public string $value,
    )
    {

    }
}
