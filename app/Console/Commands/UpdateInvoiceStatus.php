<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Carbon\Carbon;

class UpdateInvoiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật trạng thái hóa đơn nếu quá hạn thanh toán';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // Tìm hóa đơn quá hạn chưa thanh toán
        $invoices = Invoice::where('status', 0)
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 2]);

        $this->info("Đã cập nhật trạng thái cho {$invoices} hóa đơn quá hạn.");
    }
}
