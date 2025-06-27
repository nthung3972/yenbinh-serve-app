<?php

namespace App\Console\Commands;

use App\Services\ApiAdmin\FeeService;
use App\Services\ApiAdmin\InvoiceService;

use Illuminate\Console\Command;

class CreateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:create-monthly-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo hóa đơn hàng tháng cho tất cả căn hộ';

    protected $invoiceService;
    
    protected $feeService;

    public function __construct(InvoiceService $invoiceService, FeeService $feeService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->feeService = $feeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apartments = \App\Models\Apartment::all();
        $invoiceDate = now()->startOfMonth()->toDateString();
        $dueDate = now()->startOfMonth()->addDays(30)->toDateString();

        foreach ($apartments as $apartment) {
            try {
                $fees = $this->feeService->getFeesForApartment($apartment->apartment_id);

                $totalAmount = array_sum(array_column($fees, 'amount'));

                $requestData = [
                    'building_id' => $apartment->building_id,
                    'apartment_id' => $apartment->apartment_id,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'total_amount' => $totalAmount,
                    'fees' => $fees
                ];
                
                // $this->info("requestData: " . json_encode($requestData, JSON_PRETTY_PRINT));

                $this->invoiceService->create($requestData);

                $this->info("Đã tạo hóa đơn cho căn hộ ID {$apartment->apartment_id}");
            } catch (\Throwable $e) {
                $this->error("Lỗi căn hộ ID {$apartment->apartment_id}: " . $e->getMessage());
            }
        }
    }
}
