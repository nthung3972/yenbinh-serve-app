<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Exports\Sheets\SingleInvoiceSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvoicesMultiSheetExport implements WithMultipleSheets
{
    protected $invoices;

    public function __construct($invoices)
    {
        $this->invoices = $invoices;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->invoices as $invoice) {
            $sheets[] = new SingleInvoiceSheet($invoice);
        }

        return $sheets;
    }
}
