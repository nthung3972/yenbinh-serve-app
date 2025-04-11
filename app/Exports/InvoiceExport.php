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
    protected $detailStartRow = 8; // Dòng bắt đầu của chi tiết hóa đơn
    protected $headerRow = 7;      // Dòng tiêu đề bảng

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
        $this->rowCount = $this->detailStartRow + $invoice->invoiceDetails->count() + 4; // Tính số dòng (header + details + footer)
        
        return view('exports.invoice', [
            'invoice' => $invoice,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Logo và header section
            1 => [
                'height' => 80, // Tăng chiều cao cho dòng logo
            ],
            // Tiêu đề chính
            3 => [
                'font' => [
                    'bold' => true, 
                    'size' => 18,
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
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ],
            6 => [
                'font' => ['size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
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
                'height' => 30, // Tăng chiều cao header
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
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ];

        $paymentInfoRow = $totalRow + 1;
        // Đặt chiều cao cho dòng thông tin thanh toán
        $sheet->getRowDimension($paymentInfoRow)->setRowHeight(60); // Tăng chiều cao để hiển thị đầy đủ nội dung

        // Merge cells và style cho dòng thông tin thanh toán
        $sheet->mergeCells("A{$paymentInfoRow}:D{$paymentInfoRow}");
        $sheet->getStyle("A{$paymentInfoRow}:D{$paymentInfoRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => $this->secondaryColor],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true, // Cho phép nội dung tự động xuống dòng
            ],
        ]);

        $thankRow = $paymentInfoRow + 1;
$managerInfoRow = $thankRow + 1; // Dòng thông tin ban quản lý

// Đặt chiều cao cho dòng thông tin ban quản lý
$sheet->getRowDimension($managerInfoRow)->setRowHeight(50); // Tăng chiều cao để hiển thị đầy đủ nội dung

// Merge cells và style cho dòng thông tin ban quản lý
$sheet->mergeCells("A{$managerInfoRow}:D{$managerInfoRow}");
$sheet->getStyle("A{$managerInfoRow}:D{$managerInfoRow}")->applyFromArray([
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true, // Cho phép nội dung tự động xuống dòng
    ],
    'font' => [
        'size' => 10,
    ],
]);

        // Style cho phần footer
        $styles[$totalRow + 2] = [
            'font' => ['italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        // Style cho thông tin ban quản lý
        $styles[$totalRow + 3] = [
            'font' => ['size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        // Style cho người lập hóa đơn
        $styles[$this->rowCount - 1] = [
            'font' => ['size' => 11],
        ];

        return $styles;
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath(public_path('logo-yb.png'));
        $drawing->setHeight(70);
        
        // Đặt logo ở góc trái trên cùng
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);
        
        return $drawing;
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER, // Số lượng
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Đơn giá
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Thành tiền
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Gợi ý chiều cao cho các dòng
                $sheet->getRowDimension(1)->setRowHeight(80); // Logo
                $sheet->getRowDimension(3)->setRowHeight(30); // Tiêu đề
                $sheet->getRowDimension($this->headerRow)->setRowHeight(30); // Header bảng
                
                // Căn chỉnh chiều rộng cột
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                
                // Merge cells cho header
                $sheet->mergeCells('A3:D3'); // Tiêu đề chính
                $sheet->mergeCells('A4:D4'); // Căn hộ
                $sheet->mergeCells('A5:D5'); // Ngày phát hành
                $sheet->mergeCells('A6:D6'); // Hạn thanh toán
                
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
                    if ($i % 2 == 1) { // Dòng lẻ
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
                
                // Tạo footer với thông tin liên hệ
                $contactRow = $totalRow + 3;
                $sheet->mergeCells("A{$contactRow}:D{$contactRow}");
                
                // Thêm đường phân cách trên footer
                $dividerRow = $totalRow + 2;
                $sheet->getStyle("A{$dividerRow}:D{$dividerRow}")->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => $this->borderColor],
                        ],
                    ],
                ]);
                
                // Style cho người lập hóa đơn và ngày in
                $signRow = $this->rowCount - 1;
                $sheet->mergeCells("A{$signRow}:B{$signRow}");
                $sheet->mergeCells("C{$signRow}:D{$signRow}");
                $sheet->getStyle("C{$signRow}:D{$signRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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