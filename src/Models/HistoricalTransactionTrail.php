<?php

namespace TNM\USSD\Models;

class HistoricalTransactionTrail extends AbstractTransactionTrail
{
    protected $fillable = ['id', 'session_uid', 'message', 'response'];
}
