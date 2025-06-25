<?php

namespace TNM\USSD\Observers;


use TNM\USSD\Models\HistoricalSession;
use TNM\USSD\Models\HistoricalSessionNumber;

class HistoricalSessionObserver
{
    public function created(HistoricalSession $session)
    {
        $this->createHistoricalRecord($session);
    }

    public function updated(HistoricalSession $session)
    {
        $this->createHistoricalRecord($session);
    }

    private function createHistoricalRecord(HistoricalSession $session): void
    {
        HistoricalSessionNumber::updateOrCreate(
            ['id' => $session->getKey()],
            $session->only(['msisdn', 'session_id', 'ussd_session', 'last_screen'])
        );
    }
}
