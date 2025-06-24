<?php
namespace TNM\USSD\Contracts;

use Illuminate\Support\Collection;
use TNM\USSD\Models\TransactionTrail;

interface TransactionTrailStorageInterface
{
    public function add(string $sessionId, string $message, string $response): TransactionTrail;
    
    public function findBySession(string $sessionId): Collection;
}