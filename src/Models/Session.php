<?php

namespace TNM\USSD\Models;

use TNM\USSD\Models\Payload;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends AbstractSession
{
    protected $table = 'ussd_sessions';

    protected $guarded = [];

    public function payload(): HasMany
    {
        return $this->hasMany(Payload::class);
    }
}
