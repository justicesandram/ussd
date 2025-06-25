<?php

namespace TNM\USSD\Commands;

use Exception;
use Illuminate\Console\Command;
use TNM\USSD\Models\Payload;
use TNM\USSD\Models\Session;
use TNM\USSD\Models\SessionNumber;
use TNM\USSD\Models\TransactionTrail;
use TNM\USSD\Models\HistoricalSession;
use TNM\USSD\Models\HistoricalSessionNumber;
use TNM\USSD\Models\HistoricalPayload;
use TNM\USSD\Models\HistoricalTransactionTrail;

class CleanUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ussd:clean-up {--m|minutes= : Number of minutes to clear} {--f|force : Force to suppress confirmation} {--a|archive : Archive data before deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old transactions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minutes = $this->option('minutes') ?: 10;

        if (!$this->option('force')) {
            if (!$this->confirm(sprintf("This will delete all session data older than %s minutes ago. Are you sure?", $minutes)))
                return;
        }

        try {
            $shouldArchive = (bool) $this->option('archive');
            
            $sessionQuery = Session::where('created_at', '<', now()->subMinutes($minutes));
            if ($shouldArchive) {
                $sessionQuery->chunkById(1000, function ($records) {
                    $data = $records->map(function ($model) {
                        $attributes = $model->getAttributes();
                        unset($attributes['id']);
                        return $attributes;
                    })->toArray();
                    HistoricalSession::insert($data);
                });
            }
            $sessionQuery->delete();
            $trailQuery = TransactionTrail::where('created_at', '<', now()->subMinutes($minutes));
            if ($shouldArchive) {
                $trailQuery->chunkById(1000, function ($records) {
                    $data = $records->map(function ($model) {
                        $attributes = $model->getAttributes();
                        unset($attributes['id']);
                        return $attributes;
                    })->toArray();
                    HistoricalTransactionTrail::insert($data);
                });
            }
            $trailQuery->delete();
            $payloadQuery = Payload::where('created_at', '<', now()->subMinutes($minutes));
            if ($shouldArchive) {
                $payloadQuery->chunkById(1000, function ($records) {
                    $data = $records->map(function ($model) {
                        $attributes = $model->getAttributes();
                        unset($attributes['id']);
                        return $attributes;
                    })->toArray();
                    HistoricalPayload::insert($data);
                });
            }
            $payloadQuery->delete();
            $numberQuery = SessionNumber::where('created_at', '<', now()->subMinutes($minutes));
            if ($shouldArchive) {
                $numberQuery->chunkById(1000, function ($records) {
                    $data = $records->map(function ($model) {
                        $attributes = $model->getAttributes();
                        unset($attributes['id']);
                        return $attributes;
                    })->toArray();
                    HistoricalSessionNumber::insert($data);
                });
            }
            $numberQuery->delete();

        } catch (Exception $exception) {
            $this->error(sprintf("Operation failed: %s", $exception->getMessage()));
        }

        $this->info('Session logs cleaned up successfully');
    }
}

