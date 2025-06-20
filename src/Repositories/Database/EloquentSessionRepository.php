<?php
namespace TNM\USSD\Repositories\Database;

use Exception;
use TNM\USSD\Models\Payload;
use TNM\USSD\Models\Session;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EloquentSessionRepository
{
    public static function recentSessionByPhone(string $phone): ?Session
    {
        return Session::where('msisdn', $phone)
            ->where(
                'updated_at',
                '>=',
                now()->subMinutes(config('ussd.session.last_activity_minutes'))
            )
            ->latest()->first();
    }

    public static function hasRecentSessionByPhone(string $phone): bool
    {
        return !!static::recentSessionByPhone($phone);
    }

    public function updateSessionUid(Session $session, string $sessionUid): Session
    {
        $session->update(['session_uid' => $sessionUid]);

        return $session;
    }

    public static function findBySessionUid(string $sessionUid): ?Session
    {
        return Session::where('session_uid', $sessionUid)->first();
    }

    public static function findByPhoneNumber(string $phone): Collection
    {
        return Session::where('msisdn', $phone)->get();
    }

    public static function notCreated(string $session): bool
    {
        return Session::where('session_uid', $session)->doesntExist();
    }

    public static function track(string $sessionUid, string $state, string $msisdn): Session
    {
        return Session::create([
            'session_uid' => $sessionUid,
            'state' => $state,
            'msisdn' => $msisdn
        ]);
    }

    public function payload(int $id): HasMany
    {
        return $this->find($id)->hasMany(Payload::class);
    }

    public function mark(int|Session $session, string $state): Session
    {
        if (is_int($session)) {
            $session = $this->find($session);
        }

        $session->update(['state' => $state]);

        return $session;
    }

    public function addPayload(int $id, string $key, $value)
    {
        $value = is_array($value) ? json_encode($value) : $value;

        $this->payload($id)->create(['key' => $key, 'value' => $value]);
    }

    public function getPayload(int $id, string $key): ?string
    {
        return $this->payload($id)->where('key', $key)->latest()->first()->{'value'};
    }

    public function getPayloads(int $id): Collection
    {
        return $this->payload($id)->get();
    }

    public function setLocale(int $id, string $locale): ?Session
    {
        app()->setLocale($locale);
        $session = $this->find($id);
        $session->update(['locale' => $locale]);
        return $session;
    }

    public function getLocale(int $id): string
    {
        return $this->find($id)->{'locale'};
    }

    public function find(int $id): ?Session
    {
        $session = Session::find($id);

        if (!$session) {
            throw new Exception("Session with ID {$id} not found.");
        }

        return $session;
    }
}