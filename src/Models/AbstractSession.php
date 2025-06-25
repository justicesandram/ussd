<?php

namespace TNM\USSD\Models;

use TNM\USSD\Models\Payload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class AbstractSession extends Model
{
    protected $guarded = [];

    public function payload(): HasMany
    {
        return $this->hasMany(Payload::class);
    }
}
