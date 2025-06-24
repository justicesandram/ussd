<?php

namespace TNM\USSD\Storage;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use TNM\USSD\Contracts\PayloadStorageInterface;
use TNM\USSD\Models\Payload;
use TNM\USSD\Models\Session;

class CachePayloadStorage implements PayloadStorageInterface
{
    private function getStore()
    {
        return Cache::store(config('ussd.storage.cache_store'));
    }

    private function getPayloadKey(string $sessionId, string $key): string
    {
        return "ussd:payload:{$sessionId}:{$key}";
    }

    private function getSessionPayloadsKey(string $sessionId): string
    {
        return "ussd:session_payloads:{$sessionId}";
    }

    public function create(Session $session, string $key, $value): Payload
    {
        $value = is_array($value) ? json_encode($value) : $value;

        $payload = new Payload([
            'session_id' => $session->id ?? $session->session_id,
            'key' => $key,
            'value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getStore()->forever(
            $this->getPayloadKey($session->session_id, $key),
            $payload->toArray()
        );

        // Add to session payloads list
        $sessionPayloads = $this->getStore()->get($this->getSessionPayloadsKey($session->session_id), []);

        $sessionPayloads[$key] = $payload->toArray();

        $this->getStore()->forever($this->getSessionPayloadsKey($session->session_id), $sessionPayloads);

        return $payload;
    }

    public function getByKey(Session $session, string $key): ?Payload
    {
        $data = $this->getStore()->get($this->getPayloadKey($session->session_id, $key));
        return $data ? $this->arrayToPayload($data) : null;
    }

    public function getAllForSession(Session $session): Collection
    {
        $payloads = $this->getStore()->get($this->getSessionPayloadsKey($session->session_id), []);

        return collect($payloads)->map(fn($data) => $this->arrayToPayload($data));
    }

    private function arrayToPayload(array $data): Payload
    {
        $payload = new Payload();
        $payload->forceFill($data);
        $payload->exists = true;

        return $payload;
    }
}
