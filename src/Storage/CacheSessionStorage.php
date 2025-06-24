<?php

namespace TNM\USSD\Storage;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use TNM\USSD\Contracts\SessionStorageInterface;
use TNM\USSD\Models\Session;

class CacheSessionStorage implements SessionStorageInterface
{
    private function getStore()
    {
        return Cache::store(config('ussd.storage.cache_store'));
    }

    private function getSessionKey(string $sessionId): string
    {
        return "ussd:session:{$sessionId}";
    }

    private function getPhoneKey(string $phone): string
    {
        return "ussd:phone:{$phone}";
    }

    public function findBySessionId(string $sessionId): ?Session
    {
        $data = $this->getStore()->get($this->getSessionKey($sessionId));
        return $data ? $this->arrayToSession($data) : null;
    }

    public function findByPhoneNumber(string $phone): Collection
    {
        $sessionIds = $this->getStore()->get($this->getPhoneKey($phone), []);
        $sessions = collect();

        foreach ($sessionIds as $sessionId) {
            $session = $this->findBySessionId($sessionId);
            if ($session) {
                $sessions->push($session);
            }
        }

        return $sessions;
    }
    public function mark(int|Session $session, string $state): Session
    {
        if (is_int($session)) {
            $session = $this->findBySessionId((string) $session);
        }

        return $this->updateSession($session, ['state' => $state]);
    }

    public function notCreated(string $sessionId): bool
    {
        return !$this->getStore()->has($this->getSessionKey($sessionId));
    }

    public function track(string $sessionId, string $state, string $msisdn): Session
    {
        $session = new Session([
            'session_uid' => $sessionId,
            'state' => $state,
            'msisdn' => $msisdn,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->storeSession($session);
        $this->addSessionToPhone($msisdn, $sessionId);

        return $session;
    }

    public function updateSession(Session $session, array $data): Session
    {
        $session->fill($data);
        $session->updated_at = now();
        $this->storeSession($session);

        return $session;
    }

    public function recentSessionByPhone(string $phone): ?Session
    {
        $sessions = $this->findByPhoneNumber($phone);
        $cutoff = now()->subMinutes(config('ussd.session.last_activity_minutes'));

        return $sessions
            ->filter(fn($session) => $session->updated_at >= $cutoff)
            ->sortByDesc('updated_at')
            ->first();
    }

    public function hasRecentSessionByPhone(string $phone): bool
    {
        return !!$this->recentSessionByPhone($phone);
    }

    public function updateSessionId(Session $session, string $newSessionId): Session
    {
        $oldSessionId = $session->session_uid;

        // Remove old session
        $this->getStore()->forget($this->getSessionKey($oldSessionId));

        // Update session ID and store
        $session->session_uid = $newSessionId;
        $this->storeSession($session);

        // Update phone mapping
        $phoneSessionIds = $this->getStore()->get($this->getPhoneKey($session->msisdn), []);
        $phoneSessionIds = array_diff($phoneSessionIds, [$oldSessionId]);
        $phoneSessionIds[] = $newSessionId;
        $this->getStore()->forever($this->getPhoneKey($session->msisdn), $phoneSessionIds);

        return $session;
    }

    private function storeSession(Session $session): void
    {
        $this->getStore()->forever(
            $this->getSessionKey($session->session_uid),
            $session->toArray()
        );
    }

    private function addSessionToPhone(string $phone, string $sessionId): void
    {
        $phoneSessionIds = $this->getStore()->get($this->getPhoneKey($phone), []);
        $phoneSessionIds[] = $sessionId;
        $this->getStore()->forever($this->getPhoneKey($phone), $phoneSessionIds);
    }

    private function arrayToSession(array $data): Session
    {
        $session = new Session();
        $session->forceFill($data);
        $session->exists = true;

        return $session;
    }
}
