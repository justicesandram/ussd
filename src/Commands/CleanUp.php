<?php

namespace TNM\USSD\Commands;

use Illuminate\Console\Command;
use TNM\USSD\Services\CleanUpServiceInterface;

class CleanUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ussd:clean-up
                            {--m|minutes=10 : Number of minutes to clear}
                            {--f|force     : Force to suppress confirmation}
                            {--a|archive   : Archive data before deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old USSD session data';

    /**
     * @var CleanUpServiceInterface
     */
    private $cleaner;

    public function __construct(CleanUpServiceInterface $cleaner)
    {
        parent::__construct();

        $this->cleaner = $cleaner;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $force = $this->option('force');

        if (!$force && !$this->confirm("Delete data older than {$minutes} minutes?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $archive = (bool) $this->option('archive');
        try {
            $results = $this->cleaner->cleanup($minutes, $archive);

            foreach ($results as $type => $count) {
                $this->info(sprintf('Deleted %d %s', $count, $type));
            }

            $this->info('Cleanup completed successfully.');
        } catch (\Throwable $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
