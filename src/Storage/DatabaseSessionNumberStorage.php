<?php

namespace TNM\USSD\Storage;

use Illuminate\Support\Collection;
use TNM\USSD\Models\SessionNumber;
use TNM\USSD\Contracts\SessionNumberStorageInterface;

class DatabaseSessionNumberStorage implements SessionNumberStorageInterface
{
    public function updateOrCreate(array $conditions, array $data): SessionNumber
    {
        return SessionNumber::updateOrCreate($conditions, $data);
    }

    public function findByMsisdn(string $msisdn): Collection
    {
        return SessionNumber::where('msisdn', $msisdn)->get();
    }

    public function findBySessionId(string $sessionId): ?SessionNumber
    {
        return SessionNumber::where('session_id', $sessionId)->first();
    }
}