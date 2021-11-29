<?php

namespace Kvaksrud\IbmCos;

class Cos {
    public const REGEX_STORAGE_ACCOUNT = '/^([A-z]|[0-9]|-){1,32}$/';
    public const REGEX_STORAGE_ACCOUNT_METADATA = '/^([a-z]|[0-9]|-){1,20}$/';
}
