<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInventoryManagementTasks;
use Illuminate\Console\Command;

class RunInventoryManagementTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:process-tasks
                          {--async : Run tasks asynchronously using queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process inventory management tasks (expiry alerts, reordering, discounts)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Inventory Management Tasks...');

        try {
            if ($this->option('async')) {
                // Dispatch to queue
                ProcessInventoryManagementTasks::dispatch();
                $this->info('Tasks dispatched to queue successfully');
            } else {
                // Run synchronously
                $job = new ProcessInventoryManagementTasks();
                $job->handle();
                $this->info('Tasks completed successfully');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error processing tasks: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
