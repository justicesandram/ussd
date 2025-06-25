<?php
namespace TNM\USSD\Storage;

use Illuminate\Support\Collection;
use TNM\USSD\Models\TransactionTrail;
use TNM\USSD\Contracts\TransactionTrailStorageInterface;

class DatabaseTransactionTrailStorage implements TransactionTrailStorageInterface
{
    public function add(string $sessionUid, string $message, string $response): TransactionTrail
    {
        return TransactionTrail::create([
            'session_uid' => $sessionUid,
            'message' => $message,
            'response' => $response
        ]);
    }

    public function findBySession(string $sessionUid): Collection
    {
        return TransactionTrail::where('session_uid', $sessionUid)->get();
    }
}
