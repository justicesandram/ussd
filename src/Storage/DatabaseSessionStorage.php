<?php

namespace TNM\USSD\Storage;

use TNM\USSD\Models\Session;
use Illuminate\Support\Collection;
use TNM\USSD\Models\HistoricalSession;
use TNM\USSD\Contracts\SessionStorageInterface;

class DatabaseSessionStorage implements SessionStorageInterface
{
    public function findBySessionId(string $sessionId): ?Session
    {
        return Session::where('session_uid', $sessionId)->first();
    }

    public function findByPhoneNumber(string $phone): Collection
    {
        return Session::where('msisdn', $phone)->get();
    }

    public function notCreated(string $sessionId): bool
    {
        return Session::where('session_uid', $sessionId)->doesntExist();
    }

    public function track(string $sessionId, string $state, string $msisdn): Session
    {

        $session = Session::create([
            'session_uid' => $sessionId,
            'state' => $state,
            'msisdn' => $msisdn
        ]);
        HistoricalSession::updateOrCreate(
            ['id' => $session->getKey()],
            $session->only(['session_uid', 'state', 'locale', 'msisdn'])
        );
        return $session;
    }
    public function mark(int|Session $session, string $state): Session
    {
        if (is_int($session)) {
            $session = Session::find($session);
        }

        $session->update(['state' => $state]);

        return $session;
    }
    public function updateSession(Session $session, array $data): Session
    {
        $session->update($data);
        return $session;
    }

    public function recentSessionByPhone(string $phone): ?Session
    {
        return Session::where('msisdn', $phone)
            ->where(
                'updated_at',
                '>=',
                now()->subMinutes(config('ussd.session.last_activity_minutes'))
            )
            ->latest()->first();
    }

    public function hasRecentSessionByPhone(string $phone): bool
    {
        return !!$this->recentSessionByPhone($phone);
    }

    public function updateSessionId(Session $session, string $newSessionId): Session
    {
        return $this->updateSession($session, ['session_uid' => $newSessionId]);
    }
}
