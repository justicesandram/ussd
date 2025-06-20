<?php

namespace TNM\USSD\Commands;

use Illuminate\Console\Command;
use TNM\USSD\Models\Session;
use TNM\USSD\Repositories\Database\EloquentSessionRepository;

class ListUserTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ussd:list {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all transactions done by a phone number';

    private EloquentSessionRepository $sessionRepository;
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
        $transactions = EloquentSessionRepository::findByPhoneNumber($this->argument('phone'));

        if ($transactions->isEmpty()) {
            $this->info(sprintf("There are no transactions by %s", $this->argument('phone')));
            return;
        }

        $this->table(['Session ID', 'Timestamp'], $transactions->map(function (Session $session) {
            return $session->only(['session_id', 'created_at']);
        }));
    }
}
