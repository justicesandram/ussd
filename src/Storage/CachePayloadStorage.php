<?php

namespace TNM\USSD\Storage;

use TNM\USSD\Models\Payload;
use TNM\USSD\Models\Session;
use Illuminate\Support\Collection;
use TNM\USSD\Contracts\PayloadStorageInterface;

class CachePayloadStorage extends AbstractCacheStorage implements PayloadStorageInterface
{
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
            'session_id' => $session->getKey(),
            'key' => $key,
            'value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getStore()->put(
            $this->getPayloadKey($session->getKey(), $key),
            $payload->toArray(),
            $this->getUniversalTtl()
        );

        // Add to session payloads list
        $sessionPayloads = $this->getStore()
            ->get($this->getSessionPayloadsKey($session->getKey()), []);

        $sessionPayloads[$key] = $payload->toArray();

        $this->getStore()->put(
            $this->getSessionPayloadsKey($session->getKey()),
            $sessionPayloads,
            $this->getUniversalTtl()
        );

        return $payload;
    }

    public function getByKey(Session $session, string $key): ?Payload
    {
        $data = $this->getStore()->get($this->getPayloadKey($session->getKey(), $key));
        return $data ? $this->arrayToPayload($data) : null;
    }

    public function getAllForSession(Session $session): Collection
    {
        $payloads = $this
            ->getStore()
            ->get($this->getSessionPayloadsKey($session->getKey()), []);

        return collect($payloads)
            ->map(fn($data) => $this->arrayToPayload($data));
    }

    private function arrayToPayload(array $data): Payload
    {
        $payload = new Payload();
        $payload->forceFill($data);
        $payload->exists = true;

        return $payload;
    }
}
