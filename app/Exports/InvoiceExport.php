<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InvoiceExport implements FromView, WithStyles, WithDrawings, WithColumnFormatting, WithEvents
{
    protected $invoiceId;
    protected $rowCount;
    protected $detailStartRow = 9; // Dòng bắt đầu của chi tiết hóa đơn
    protected $headerRow = 8;      // Dòng tiêu đề bảng

    // Khai báo màu sắc theo theme
    protected $primaryColor = '4472C4';    // Xanh dương đậm
    protected $secondaryColor = 'D9E1F2';  // Xanh dương nhạt
    protected $accentColor = '70AD47';     // Xanh lá
    protected $textColor = '2F528F';       // Màu chữ xanh dương đậm
    protected $borderColor = 'B4C6E7';     // Màu viền nhẹ

    public function __construct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    public function view(): View
    {
        $invoice = Invoice::with('invoiceDetails', 'apartment', 'updatedBy')->findOrFail($this->invoiceId);
        $this->rowCount = $this->detailStartRow + $invoice->invoiceDetails->count() + 5; // Điều chỉnh rowCount

        return view('exports.invoice', [
            'invoice' => $invoice,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Logo và header section
            1 => [
                'height' => 60,
            ],
            // Tiêu đề chính
            3 => [
                'font' => [
                    'bold' => true,
                    'size' => 15,
                    'color' => ['argb' => $this->textColor]
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Thông tin căn hộ & ngày tháng
            4 => [
                'font' => ['size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            5 => [
                'font' => ['size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ],
            6 => [
                'font' => ['size' => 11],
            ],
            // Header bảng
            $this->headerRow => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => $this->primaryColor],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'height' => 30,
            ],
        ];

        // Style cho dòng tổng tiền
        $totalRow = $this->detailStartRow + count(Invoice::findOrFail($this->invoiceId)->invoiceDetails) + 1;
        $styles[$totalRow] = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => $this->accentColor]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => $this->secondaryColor],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ];

        // Style cho Thông tin thanh toán
        $paymentInfoRow = $totalRow + 1;
        $sheet->getRowDimension($paymentInfoRow)->setRowHeight(100);
        $sheet->mergeCells("A{$paymentInfoRow}:D{$paymentInfoRow}");
        $sheet->getStyle("A{$paymentInfoRow}:D{$paymentInfoRow}")->applyFromArray([
            'fill' => [
                'font' => ['size' => 11],
                'color' => ['argb' => $this->secondaryColor],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Style cho TRƯỞNG BAN QUẢN LÝ 
        $managerRow = $paymentInfoRow + 1;
        $styles[$managerRow] = [
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        // Style cho dòng cảm ơn
        $thankRow = $managerRow + 1;
        $styles[$thankRow] = [
            'font' => ['italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        // Style cho thông tin ban quản lý
        $contactRow = $thankRow + 1;
        $styles[$contactRow] = [
            'font' => ['size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        return $styles;
    }

    public function drawings()
    {
        $drawings = [];

        // Logo công ty
        $logo = new Drawing();
        $logo->setName('Logo');
        $logo->setDescription('Company Logo');
        $logo->setPath(public_path('logo.png'));
        $logo->setHeight(70);
        $logo->setCoordinates('A1');
        $logo->setOffsetX(20);
        $logo->setOffsetY(10);
        $drawings[] = $logo;

        // QR Code
        $invoice = Invoice::findOrFail($this->invoiceId);
        $totalRow = $this->detailStartRow + count($invoice->invoiceDetails) + 1;
        $paymentInfoRow = $totalRow + 1;

        $qrCode = new Drawing();
        $qrCode->setName('QR Code');
        $qrCode->setDescription('Payment QR Code');
        $qrCode->setPath(public_path('qr_code.jpg'));
        $qrCode->setHeight(100);
        $qrCode->setWidth(100);
        $qrCode->setCoordinates('D' . $paymentInfoRow);
        $qrCode->setOffsetX(10);
        $qrCode->setOffsetY(10);
        $drawings[] = $qrCode;

        return $drawings;
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Gợi ý chiều cao cho các dòng
                $sheet->getRowDimension(1)->setRowHeight(60);
                $sheet->getRowDimension(2)->setRowHeight(40);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(15)->setRowHeight(100);
                $sheet->getRowDimension(17)->setRowHeight(40);
                $sheet->getRowDimension($this->headerRow)->setRowHeight(30);

                // Căn chỉnh chiều rộng cột
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);

                // Merge cells cho header
                $sheet->mergeCells('A3:D3'); // Tiêu đề chính
                $sheet->mergeCells('A4:D4'); // Căn hộ
                $sheet->mergeCells('A5:D5'); // Ngày phát hành & hạn thanh toán
                $sheet->mergeCells('A6:B6'); // Người lập hóa đơn
                $sheet->mergeCells('C6:D6'); // Ngày in

                // Tạo khung cho hóa đơn
                $fullRange = "A3:D" . $this->rowCount;
                $sheet->getStyle($fullRange)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => $this->primaryColor],
                        ],
                    ],
                ]);

                // Tạo viền và định dạng cho bảng chi tiết
                $tableRange = "A{$this->headerRow}:D" . ($this->detailStartRow + Invoice::findOrFail($this->invoiceId)->invoiceDetails->count() + 1);
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => $this->borderColor],
                        ],
                    ],
                ]);

                // Tô màu xen kẽ cho các dòng dữ liệu
                $detailsCount = Invoice::findOrFail($this->invoiceId)->invoiceDetails->count();
                for ($i = 0; $i < $detailsCount; $i++) {
                    if ($i % 2 == 1) {
                        $sheet->getStyle('A' . ($this->detailStartRow + $i) . ':D' . ($this->detailStartRow + $i))
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB($this->secondaryColor);
                    }
                }

                // Căn giữa cột Số Lượng
                $detailLastRow = $this->detailStartRow + Invoice::findOrFail($this->invoiceId)->invoiceDetails->count() - 1;
                $sheet->getStyle("B{$this->detailStartRow}:B{$detailLastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Căn phải các giá trị tiền tệ
                $sheet->getStyle("C{$this->detailStartRow}:D{$detailLastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Tạo đường kẻ đậm phía trên dòng tổng cộng
                $totalRow = $this->detailStartRow + $detailsCount;
                $sheet->getStyle("A{$totalRow}:D{$totalRow}")->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => $this->primaryColor],
                        ],
                    ],
                ]);

                // TRƯỞNG BAN QUẢN LÝ
                $paymentInfoRow = $totalRow + 1;
                $managerRow = $paymentInfoRow + 1;
                $sheet->mergeCells("C{$managerRow}:D{$managerRow}");
                $sheet->setCellValue("C{$managerRow}", "TRƯỞNG BAN QUẢN LÝ");
                $sheet->getRowDimension($managerRow)->setRowHeight(90);

                $sheet->mergeCells("C{$managerRow}:D{$managerRow}");
                $sheet->setCellValue("C{$managerRow}", "TRƯỞNG BAN QUẢN LÝ");
                $sheet->getStyle("C{$managerRow}:D{$managerRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER); // Thêm dòng này để đảm bảo

                // Tạo footer với thông tin liên hệ
                $contactRow = $managerRow + 2;
                $sheet->mergeCells("A{$contactRow}:D{$contactRow}");

                // Thêm đường phân cách trên footer
                $dividerRow = $managerRow + 1;
                $sheet->getStyle("A{$dividerRow}:D{$dividerRow}")->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => $this->borderColor],
                        ],
                    ],
                ]);
            },
        ];
    }

    public static function getServiceName($code)
    {
        $services = [
            'DIEN' => 'Tiền điện',
            'NUOC' => 'Tiền nước',
            'QUANLY' => 'Phí quản lý',
            'GUIXE' => 'Phí gửi xe',
            'PHIKHAC' => 'Phí khác',
        ];
        return $services[$code] ?? 'Không xác định';
    }
}
