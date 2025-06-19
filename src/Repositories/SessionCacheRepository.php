<?php
namespace TNM\USSD\Repositories;

use Carbon\Carbon;
use Exception;
use DateInterval;
use DateTimeImmutable;
use TNM\USSD\Dto\SessionDto;
use Illuminate\Support\Facades\Cache;

final class SessionCacheRepository
{
    public function __construct()
    {
    }

    public function create(SessionDto $data): SessionDto
    {
        if ($data->sessionKey === null) {
            $data->sessionKey = $data::generateKey();
        }

        if ($data->createdAt === null) {
            $data->createdAt = new DateTimeImmutable();
        }

        if (!Cache::forever($data->sessionKey, $data)) {
            throw new Exception("Failed to create session data.");
        }

        $sessionKeyMap = Cache::rememberForever('session_keys_map', function () {
            return [];
        });

        if (!in_array($data->sessionKey, $sessionKeyMap, true)) {
            $sessionKeyMap[] = $data->sessionKey;
            Cache::forever('session_keys_map', $sessionKeyMap);
        }

        return $data;
    }
    public function delete(SessionDto $session): bool
    {
        try {
            $removed = Cache::forget($session->sessionKey);

            $sessionKeyMap = Cache::get('session_keys_map', []);

            $filtered = array_filter(
                $sessionKeyMap,
                fn($key) => $key !== $session->sessionKey
            );

            if (count($filtered) !== count($sessionKeyMap)) {
                Cache::forever('session_keys_map', array_values($filtered));
            }
            return $removed;
        } catch (Exception $e) {
            return false;
        }
    }

    public function exists(string $sessionKey): bool
    {
        return Cache::has($sessionKey);
    }
    public function update(string $sessionKey, array $data): bool
    {
        $existing = $this->get($sessionKey);
        if (!$existing) {
            return false;
        }

        $existing->sessionId = $data['sessionId'] ?? $existing->sessionId;
        $existing->state = $data['state'] ?? $existing->state;
        $existing->payload = $data['payload'] ?? $existing->payload;
        $existing->locale = $data['locale'] ?? $existing->locale;
        $existing->msisdn = $data['msisdn'] ?? $existing->msisdn;


        $now = new DateTimeImmutable();

        $existing->updatedAt = $now;

        if ($existing->createdAt === null) {
            $existing->createdAt = $now;
        }

        Cache::forever($sessionKey, $existing);

        return true;
    }

    public function get(string $sessionKey): ?SessionDto
    {
        $data = Cache::get($sessionKey);

        return !$data instanceof SessionDto ? null : $data;
    }

    public function getAll(): array
    {
        $keys = Cache::get('session_keys_map', []);
        if (empty($keys)) {
            return [];
        }

        $items = Cache::getMultiple($keys);

        return array_values(
            array_filter(
                $items,
                fn($item) => $item instanceof SessionDto
            )
        );
    }

    /**
     * Return the most‐recent session for a given phone,
     * so long as it was updated within the configured window.
     */
    public function recentSessionByPhone(string $phone): ?SessionDto
    {
        $sessions = $this->getAll();

        $minutes = config('ussd.session.last_activity_minutes');
        $threshold = (new DateTimeImmutable())
            ->sub(new DateInterval("PT{$minutes}M"));

        $recent = array_filter(
            $sessions,
            fn(SessionDto $s) =>
            $s->msisdn === $phone
            && $s->updatedAt >= $threshold
        );

        if (empty($recent)) {
            return null;
        }

        usort(
            $recent,
            fn(SessionDto $a, SessionDto $b): int =>
            $b->updatedAt <=> $a->updatedAt
        );

        return $recent[0];
    }

    public function hasRecentSessionByPhone(string $phone): bool
    {
        return $this->recentSessionByPhone($phone) !== null;
    }

    public function updateSessionId(string $sessionKey, string $newSessionId): ?SessionDto
    {
        $success = $this->update($sessionKey, ['sessionId' => $newSessionId]);

        return $success
            ? $this->get($sessionKey)
            : null;
    }

    public function findBySessionId(string $sessionKey): ?SessionDto
    {
        return $this->get($sessionKey);
    }

    /**
     * Get all sessions for a given phone number.
     *
     * @return SessionDto[]
     */
    public function findByPhoneNumber(string $phone): array
    {
        return array_values(array_filter(
            $this->getAll(),
            fn(SessionDto $dto) => $dto->msisdn === $phone
        ));
    }

    /**
     * Does no session exist yet for this sessionKey?
     */
    public function notCreated(string $sessionKey): bool
    {
        return !$this->exists($sessionKey);
    }

    /**
     * Create & track a new session.
     *
     * @param  string  $sessionKey
     * @param  string  $state
     * @param  string  $msisdn
     */
    public function track(int $session, string $state, string $msisdn): SessionDto
    {
        $now = new DateTimeImmutable();

        $dto = new SessionDto(
            sessionKey: SessionDto::generateKey(),
            sessionId: $session,
            state: $state,
            msisdn: $msisdn,
        );

        $dto->createdAt = $now;
        $dto->updatedA = $now;

        return $this->create($dto);
    }

    /**
     * Update just the state on an existing session.
     */
    public function mark(string $sessionKey, string $newState): ?SessionDto
    {
        if (!$this->update($sessionKey, ['state' => $newState])) {
            return null;
        }
        return $this->get($sessionKey);
    }

    public function setLocale(string $sessionKey, string $locale): ?SessionDto
    {
        app()->setLocale($locale);
        if (!$this->update($sessionKey, ['locale' => $locale])) {
            return null;
        }
        return $this->get($sessionKey);
    }

    public function getLocale(string $sessionKey): ?string
    {
        $dto = $this->get($sessionKey);
        return $dto?->locale;
    }
}