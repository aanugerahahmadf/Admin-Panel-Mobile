<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkExpiredPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-expired-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks pending/processing payments as expired if they are past their deadline.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired payments...');

        $expiredPayments = Payment::whereIn('status', [PaymentStatus::PENDING, PaymentStatus::PROCESSING])
            ->where('expired_at', '<=', Carbon::now())
            ->get();

        if ($expiredPayments->isEmpty()) {
            $this->info('No expired payments found.');

            return;
        }

        $bar = $this->output->createProgressBar($expiredPayments->count());
        $bar->start();

        foreach ($expiredPayments as $payment) {
            $payment->update([
                'status' => PaymentStatus::EXPIRED,
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully marked {$expiredPayments->count()} payments as expired.");
    }
}
