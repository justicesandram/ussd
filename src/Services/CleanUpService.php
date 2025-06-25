<?php

namespace TNM\USSD\Services;

use Illuminate\Support\Collection;
use TNM\USSD\Models\{
    Session,
    TransactionTrail,
    Payload,
    SessionNumber,
    HistoricalSession,
    HistoricalTransactionTrail,
    HistoricalPayload,
    HistoricalSessionNumber
};

final class CleanUpService implements CleanUpServiceInterface
{
    protected array $models = [
        'sessions' => [Session::class, HistoricalSession::class],
        'trails' => [TransactionTrail::class, HistoricalTransactionTrail::class],
        'payloads' => [Payload::class, HistoricalPayload::class],
        'sessionNumbers' => [SessionNumber::class, HistoricalSessionNumber::class],
    ];

    public function cleanup(int $minutes, bool $archive): array
    {
        $results = [];
        $threshold = now()->subMinutes($minutes);

        foreach ($this->models as $key => [$live, $hist]) {
            $query = $live::where('created_at', '<', $threshold);

            if ($archive) {
                $this->archiveChunk($query, $hist);
            }

            $results[$key] = $query->delete();
        }

        return $results;
    }

    protected function archiveChunk($query, string $histModel): void
    {
        $query->chunkById(1000, function (Collection $records) use ($histModel) {
            $rows = $records->map(function ($m) {
                return tap($m->getAttributes(), function (&$a) {
                    unset($a['id']);
                });
            })->all();

            $histModel::insert($rows);
        });
    }

}
