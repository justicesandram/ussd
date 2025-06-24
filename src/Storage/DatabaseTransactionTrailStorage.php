<?php
namespace TNM\USSD\Storage;

use Illuminate\Support\Collection;
use TNM\USSD\Models\TransactionTrail;
use TNM\USSD\Contracts\TransactionTrailStorageInterface;

class DatabaseTransactionTrailStorage implements TransactionTrailStorageInterface
{
    public function add(string $sessionId, string $message, string $response): TransactionTrail
    {
        return TransactionTrail::create([
            'session_uid' => $sessionId,
            'message' => $message,
            'response' => $response
        ]);
    }

    public function findBySession(string $sessionId): Collection
    {
        return TransactionTrail::where('session_uid', $sessionId)->get();
    }
}
