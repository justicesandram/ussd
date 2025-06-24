<?php

namespace TNM\USSD\Storage;

use InvalidArgumentException;
use TNM\USSD\Contracts\PayloadStorageInterface;
use TNM\USSD\Contracts\SessionStorageInterface;

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
            default => throw new InvalidArgumentException("Invalid storage driver: $this->storageDriver.")
        };
    }

    public function payloadStorage(): PayloadStorageInterface
    {
        return match ($this->storageDriver) {
            'database' => new DatabasePayloadStorage(),
            'cache' => new CachePayloadStorage(),
            default => throw new InvalidArgumentException("Invalid storage driver: $this->storageDriver.")
        };
    }
}
