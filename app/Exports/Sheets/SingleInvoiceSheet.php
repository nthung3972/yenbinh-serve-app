<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SingleInvoiceSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        // Sử dụng mã hóa đơn hoặc ID làm tên của sheet
        return 'Hóa đơn #' . $this->invoice->invoice_id;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Mã hóa đơn',
            'Ngày tạo',
            'Tổng tiền',
            'Trạng thái',
            // Thêm các cột khác nếu cần
        ];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Chi tiết hóa đơn có thể bao gồm thông tin chung và các mục trong hóa đơn
        $invoiceData = [
            [
                $this->invoice->invoice_id,
                $this->invoice->invoice_date,
                number_format($this->invoice->total_amount),
                $this->invoice->status,
            ],
        ];

        // Thêm dòng trống
        $invoiceData[] = ['', '', '', '', ''];

        // Tiêu đề cho các mục trong hóa đơn
        $invoiceData[] = ['Mã SP', 'Tên sản phẩm'];

        // Thêm các mục trong hóa đơn
        foreach ($this->invoice->invoiceDetails as $item) {
            $invoiceData[] = [
                $item->invoice_detail_id,
                $item->description
            ];
        }

        return $invoiceData;
    }

    /**
     * Tùy chỉnh style cho worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style cho tiêu đề
            1 => ['font' => ['bold' => true]],
            // Style cho tiêu đề các mục
            4 => ['font' => ['bold' => true]],
        ];
    }
}
