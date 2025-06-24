<?php

namespace TNM\USSD\Contracts;

use Illuminate\Support\Collection;
use TNM\USSD\Models\Session;

interface SessionStorageInterface
{
    public function findBySessionId(string $sessionId): ?Session;

    public function findByPhoneNumber(string $phone): Collection;

    public function notCreated(string $sessionId): bool;

    public function track(string $sessionId, string $state, string $msisdn): Session;

    public function updateSession(Session $session, array $data): Session;

    public function recentSessionByPhone(string $phone): ?Session;

    public function hasRecentSessionByPhone(string $phone): bool;

    public function mark(int|Session $session, string $state): Session; 
    public function updateSessionId(Session $session, string $newSessionId): Session;
}
