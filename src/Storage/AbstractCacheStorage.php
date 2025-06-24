<?php
namespace TNM\USSD\Storage;

use DateInterval;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;

abstract class AbstractCacheStorage
{
    protected Repository $cache;
    private int $universalTtl = 0;
    public function __construct()
    {
        $this->cache = Cache::store(config('ussd.storage.cache_store', 'file'));
        $this->universalTtl = (int) config('ussd.storage.cache_universal_ttl_days', 0);
    }

    protected function getUniversalTtl(): ?DateInterval
    {
        if ($this->universalTtl <= 0) {
            return null;
        }

        $interval = new DateInterval("P{$this->universalTtl}D"); // PnD format for DateInterval

        return $interval;
    }

    protected function getStore(): Repository
    {
        if (!$this->cache) {
            return (new static())->cache;
        }
        return $this->cache;
    }
}