<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupPendingTopups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-pending-topups {hours=24}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels pending topups that have not been paid for a certain number of hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->argument('hours');
        $before = Carbon::now()->subHours($hours);

        $pendingTopups = Transaction::where('type', 'topup')
            ->where('status', 'pending')
            ->where('created_at', '<=', $before)
            ->get();

        if ($pendingTopups->isEmpty()) {
            $this->info("No pending topups older than {$hours} hours found.");

            return;
        }

        $bar = $this->output->createProgressBar($pendingTopups->count());
        $bar->start();

        foreach ($pendingTopups as $topup) {
            $topup->update([
                'status' => 'cancelled',
                'notes' => 'Otomatis dibatalkan oleh sistem karena melewati batas waktu pembayaran.',
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully cancelled {$pendingTopups->count()} pending topups.");
    }
}
