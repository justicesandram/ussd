<?php

namespace TNM\USSD\Observers;

use Session;
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
        $hsn = HistoricalSessionNumber::where('id', $sessionNumber->getKey())->first();
        if ($hsn) {
            $hsn->update($sessionNumber->only(['msisdn', 'session_id', 'ussd_session', 'last_screen']));
            return;
        }

        $refreshedSessionNumber = SessionNumber::find($sessionNumber->getKey());

        if (!$refreshedSessionNumber) {
            return;
        }

        HistoricalSessionNumber::create([
            'msisdn' => $refreshedSessionNumber->msisdn,
            'session_id' => $refreshedSessionNumber->id,
            'ussd_session' => $refreshedSessionNumber->ussd_session,
            'last_screen' => $refreshedSessionNumber->last_screen
        ]);
    }
}
