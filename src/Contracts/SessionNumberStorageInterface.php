<?php

namespace TNM\USSD\Contracts;

use Illuminate\Support\Collection;
use TNM\USSD\Models\SessionNumber;

interface SessionNumberStorageInterface
{
    public function updateOrCreate(array $conditions, array $data): SessionNumber;

    public function findByMsisdn(string $msisdn): Collection;

    public function findBySessionId(string $sessionId): ?SessionNumber;
}