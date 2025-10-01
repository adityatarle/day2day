<?php

namespace App\Console\Commands;

use App\Services\SmartReorderingService;
use Illuminate\Console\Command;

class InitializeReorderingSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:init-reordering';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize reorder point configurations for all products and branches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Initializing reordering system...');

        try {
            $service = new SmartReorderingService();
            
            $this->info('Creating reorder point configurations...');
            $count = $service->initializeReorderConfigs();
            $this->info("Created {$count} reorder point configurations");

            $this->info('Generating initial demand forecasts...');
            $forecasts = $service->generateDemandForecasts(7);
            $this->info("Generated {$forecasts} demand forecasts");

            $this->info('Reordering system initialized successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error initializing reordering system: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
