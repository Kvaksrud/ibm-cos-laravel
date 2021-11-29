<?php

namespace Kvaksrud\IbmCos;

class Cos {
    public const REGEX_STORAGE_ACCOUNT = '/^([A-z]|[0-9]|-){1,32}$/';
    public const REGEX_CONTAINER_VAULT = Cos::REGEX_STORAGE_ACCOUNT;
    public const REGEX_CONTAINER = Cos::REGEX_STORAGE_ACCOUNT;

    public const REGEX_STORAGE_ACCOUNT_METADATA = '/^([a-z]|[0-9]|-){1,20}$/';

    /**
     * Validates if storage account id is valid
     *
     * @param $id
     * @return bool
     */
    public static function isValidStorageAccountId($id): bool
    {
        if(preg_match(Cos::REGEX_STORAGE_ACCOUNT,$id) === 0)
            return false;
        return true;
    }

    /**
     * Validates if container name is valid
     *
     * @param $name
     * @return bool
     */
    public static function isValidContainerName($name): bool
    {
        if(preg_match(Cos::REGEX_CONTAINER,$name) === 0)
            return false;
        return true;
    }

    /**
     * Validates if container vault name is valid
     *
     * @param $name
     * @return bool
     */
    public static function isValidContainerVaultName($name): bool
    {
        if(preg_match(Cos::REGEX_CONTAINER_VAULT,$name) === 0)
            return false;
        return true;
    }


}
