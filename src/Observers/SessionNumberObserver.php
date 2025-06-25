<?php

namespace TNM\USSD\Observers;

use TNM\USSD\Models\SessionNumber;
use TNM\USSD\Models\HistoricalSessionNumber;

class SessionNumberObserver
{
    public function created(SessionNumber $sessionNumber)
    {
        self::createorUpdateHistoricalSessionNumber($sessionNumber);
    }

    public function updated(SessionNumber $sessionNumber)
    {
        self::createorUpdateHistoricalSessionNumber($sessionNumber);
    }

    private static function createorUpdateHistoricalSessionNumber(SessionNumber $sessionNumber)
    {
        HistoricalSessionNumber::updateOrCreate(
            ['session_id' => $sessionNumber->session_id],
            $sessionNumber->only(['msisdn', 'ussd_session', 'last_screen'])
        );
    }
}
