<?php

namespace TNM\USSD\Storage;

use TNM\USSD\Models\Payload;
use TNM\USSD\Models\Session;
use Illuminate\Support\Collection;
use TNM\USSD\Contracts\PayloadStorageInterface;

class DatabasePayloadStorage implements PayloadStorageInterface
{
    public function create(Session $session, string $key, $value): Payload
    {
        $value = is_array($value) ? json_encode($value) : $value;
        $model = $session->payload()->create(['key' => $key, 'value' => $value]);
        return Payload::find($model->getKey());
    }

    public function getByKey(Session $session, string $key): ?Payload
    {
        return $session->payload()->where('key', $key)->latest()->first();
    }

    public function getAllForSession(Session $session): Collection
    {
        return $session->payload()->get();
    }
}
