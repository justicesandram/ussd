<?php

namespace TNM\USSD\Models;

class TransactionTrail extends AbstractTransactionTrail
{
    protected $fillable = [
        'session_uid',
        'message',
        'response'
    ];
}
