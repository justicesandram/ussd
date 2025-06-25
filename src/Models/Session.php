<?php

namespace TNM\USSD\Models;

use TNM\USSD\Models\Payload;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends AbstractSession
{
    protected $table = 'ussd_sessions';

    protected $guarded = [];
}
