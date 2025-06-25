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
        // HistoricalSessionNumber::updateOrCreate(
        //     ['id' => $sessionNumber->getKey()],
        //     $sessionNumber->only(['msisdn', 'session_id', 'ussd_session', 'last_screen'])
        // );
    }
}
