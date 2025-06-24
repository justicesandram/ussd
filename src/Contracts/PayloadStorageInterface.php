<?php

namespace TNM\USSD\Contracts;

use Illuminate\Support\Collection;
use TNM\USSD\Models\Payload;
use TNM\USSD\Models\Session;

interface PayloadStorageInterface
{
    public function create(Session $session, string $key, $value): Payload;
    
    public function getByKey(Session $session, string $key): ?Payload;
    
    public function getAllForSession(Session $session): Collection;
}
