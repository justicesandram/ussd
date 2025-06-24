<?php

namespace TNM\USSD\Storage;

use InvalidArgumentException;
use TNM\USSD\Contracts\PayloadStorageInterface;
use TNM\USSD\Contracts\SessionStorageInterface;
use TNM\USSD\Storage\CacheSessionNumberStorage;
use TNM\USSD\Storage\CacheTransactionTrailStorage;
use TNM\USSD\Storage\DatabaseSessionNumberStorage;
use TNM\USSD\Contracts\SessionNumberStorageInterface;
use TNM\USSD\Storage\DatabaseTransactionTrailStorage;
use TNM\USSD\Contracts\TransactionTrailStorageInterface;

class StorageManager
{
    private string $storageDriver;

    public function __construct()
    {
        $this->storageDriver = config('ussd.storage.driver');
    }
    public function sessionStorage(): SessionStorageInterface
    {
        return match ($this->storageDriver) {
            'database' => new DatabaseSessionStorage(),
            'cache' => new CacheSessionStorage(),
            default => throw new InvalidArgumentException("Invalid storage driver: '$this->storageDriver'.")
        };
    }

    public function payloadStorage(): PayloadStorageInterface
    {
        return match ($this->storageDriver) {
            'database' => new DatabasePayloadStorage(),
            'cache' => new CachePayloadStorage(),
            default => throw new InvalidArgumentException("Invalid storage driver: '$this->storageDriver'.")
        };
    }

    public function transactionTrailStorage(): TransactionTrailStorageInterface
    {
        return match ($this->storageDriver) {
            'database' => new DatabaseTransactionTrailStorage(),
            'cache' => new CacheTransactionTrailStorage(),
            default => throw new InvalidArgumentException("Invalid storage driver: '$this->storageDriver'.")
        };
    }

    public function sessionNumberStorage(): SessionNumberStorageInterface
    {
        return match ($this->storageDriver) {
            'database' => new DatabaseSessionNumberStorage(),
            'cache' => new CacheSessionNumberStorage(),
            default => throw new InvalidArgumentException("Invalid storage driver: '$this->storageDriver'.")
        };
    }
}
