<?php

namespace App\Console\Commands;

use App\Models\CycleCountSchedule;
use App\Models\PhysicalCountSession;
use Illuminate\Console\Command;

class GenerateCycleCountSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:generate-cycle-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate physical count sessions for due cycle count schedules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating cycle count sessions...');

        try {
            $schedules = CycleCountSchedule::dueForCount()->get();
            $count = 0;

            foreach ($schedules as $schedule) {
                // Create physical count session
                $session = PhysicalCountSession::create([
                    'session_number' => PhysicalCountSession::generateSessionNumber(),
                    'branch_id' => $schedule->branch_id,
                    'warehouse_id' => $schedule->warehouse_id,
                    'cycle_count_schedule_id' => $schedule->id,
                    'count_type' => 'cycle',
                    'status' => 'scheduled',
                    'scheduled_date' => now()->toDateString(),
                ]);

                // Get products to count
                $products = $schedule->getProductsToCount();
                $itemsCreated = $session->createCountItems($products->pluck('id')->toArray());

                $this->info("Created session {$session->session_number} with {$itemsCreated} items");
                $count++;
            }

            $this->info("Generated {$count} cycle count sessions successfully");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error generating cycle counts: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
