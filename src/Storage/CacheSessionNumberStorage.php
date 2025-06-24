<?php

namespace TNM\USSD\Storage;

use Illuminate\Support\Collection;
use TNM\USSD\Models\SessionNumber;
use Illuminate\Support\Facades\Cache;
use TNM\USSD\Contracts\SessionNumberStorageInterface;

class CacheSessionNumberStorage implements SessionNumberStorageInterface
{
    private function getStore()
    {
        return Cache::store(config('ussd.storage.cache_store', 'file'));
    }

    private function getMsisdnKey(string $msisdn): string
    {
        return "ussd:session_number:msisdn:{$msisdn}";
    }

    private function getSessionIdKey(string $sessionId): string
    {
        return "ussd:session_number:session_id:{$sessionId}";
    }

    private function getRecordKey(string $msisdn, string $ussdSession): string
    {
        return "ussd:session_number:record:{$msisdn}:{$ussdSession}";
    }

    public function updateOrCreate(array $conditions, array $data): SessionNumber
    {
        $msisdn = $conditions['msisdn'] ?? null;
        $ussdSession = $conditions['ussd_session'] ?? null;

        if (!$msisdn || !$ussdSession) {
            throw new \InvalidArgumentException('msisdn and ussd_session are required in conditions');
        }

        $recordKey = $this->getRecordKey($msisdn, $ussdSession);
        $existingData = $this->getStore()->get($recordKey);

        if ($existingData) {
            $sessionNumber = $this->arrayToSessionNumber($existingData);
            $sessionNumber->fill($data);
        } else {
            $sessionNumber = new SessionNumber();
            $sessionNumber->fill(array_merge($conditions, $data));
            $sessionNumber->id = uniqid();
            $sessionNumber->created_at = now();
        }

        $sessionNumber->updated_at = now();

        $this->storeSessionNumber($sessionNumber);

        return $sessionNumber;
    }

    public function findByMsisdn(string $msisdn): Collection
    {
        $sessionNumberIds = $this->getStore()->get($this->getMsisdnKey($msisdn), []);

        return collect($sessionNumberIds)->map(function ($recordKey) {
            $data = $this->getStore()->get($recordKey);
            return $data ? $this->arrayToSessionNumber($data) : null;
        })->filter();
    }

    public function findBySessionId(string $sessionId): ?SessionNumber
    {
        $recordKey = $this->getStore()->get($this->getSessionIdKey($sessionId));

        if (!$recordKey) {
            return null;
        }

        $data = $this->getStore()->get($recordKey);

        return $data ? $this->arrayToSessionNumber($data) : null;
    }

    private function storeSessionNumber(SessionNumber $sessionNumber): void
    {
        $recordKey = $this->getRecordKey($sessionNumber->msisdn, $sessionNumber->ussd_session);

        $this->getStore()->forever($recordKey, $sessionNumber->toArray());

        // index by msisdn
        $msisdnSessionNumbers = $this->getStore()->get($this->getMsisdnKey($sessionNumber->msisdn), []);
        if (!in_array($recordKey, $msisdnSessionNumbers)) {
            $msisdnSessionNumbers[] = $recordKey;
            $this->getStore()->forever($this->getMsisdnKey($sessionNumber->msisdn), $msisdnSessionNumbers);
        }

        // index by session_id
        if ($sessionNumber->session_id) {
            $this->getStore()->forever($this->getSessionIdKey(
                $sessionNumber->session_id
            ), $recordKey);
        }
    }

    private function arrayToSessionNumber(array $data): SessionNumber
    {
        $sessionNumber = new SessionNumber();
        $sessionNumber->forceFill($data);
        $sessionNumber->exists = true;

        return $sessionNumber;
    }
}