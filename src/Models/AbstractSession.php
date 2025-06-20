<?php

namespace TNM\USSD\Models;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractSession extends Model
{
    protected $table = 'ussd_sessions';

    protected $guarded = [];
}
