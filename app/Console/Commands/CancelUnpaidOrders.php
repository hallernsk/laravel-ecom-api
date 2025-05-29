<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:cancel-unpaid-orders-command';
    protected $signature = 'orders:cancel-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel unpaid orders older than 2 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Order::where('status', 'На оплату')
            ->where('created_at', '<', now()->subMinutes(2))
            ->update(['status' => 'Отменен']);

        $this->info("Cancelled orders: {$count}");

    }
}
