<?php

/**
 * Class InvoiceExport
 * 
 * Lớp này chịu trách nhiệm xuất hóa đơn sang định dạng Excel
 * sử dụng thư viện maatwebsite/excel trong Laravel
 */

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
    /**
     * ID của hóa đơn cần xuất
     */
    protected $invoiceId;

    /**
     * Tổng số dòng trong bảng tính
     */
    protected $rowCount;

    /**
     * Dòng bắt đầu cho chi tiết hóa đơn
     */
    protected $detailStartRow = 9;

    /**
     * Dòng tiêu đề bảng
     */
    protected $headerRow = 8;

    /**
     * Các màu sắc được sử dụng trong bảng tính
     */
    protected $primaryColor = '4472C4';    // Màu chính (xanh đậm)
    protected $secondaryColor = 'E6ECF7';  // Màu phụ (xanh nhạt)
    protected $accentColor = '70AD47';     // Màu nhấn (xanh lá)
    protected $textColor = '2F528F';       // Màu chữ (xanh dương đậm)
    protected $borderColor = 'B4C6E7';     // Màu viền (xanh nhạt)

    /**
     * Khởi tạo lớp xuất hóa đơn
     *
     * @param int $invoiceId ID của hóa đơn cần xuất
     */
    public function __construct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * Xác định view để hiển thị dữ liệu
     * 
     * Phương thức này tải hóa đơn và các thông tin liên quan
     * để truyền vào view exports.invoice
     *
     * @return View
     */
    public function view(): View
    {
        // Tải hóa đơn với tất cả các quan hệ liên quan
        $invoice = Invoice::with('invoiceDetails.feeTypes', 'apartment', 'updatedBy')
            ->findOrFail($this->invoiceId);

        // Tính tổng số dòng trong bảng tính
        $positions = $this->calculateRowPositions();
        $this->rowCount = $positions['contactRow'] + 1;

        // Trả về view với dữ liệu hóa đơn
        return view('exports.invoice', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Tính toán vị trí các dòng quan trọng dựa trên số lượng chi tiết hóa đơn
     *
     * @return array Mảng các vị trí dòng
     */
    private function calculateRowPositions()
    {
        // Lấy số lượng chi tiết hóa đơn
        $invoice = Invoice::findOrFail($this->invoiceId);
        $detailsCount = $invoice->invoiceDetails->count();

        // Tính toán các vị trí dòng
        $positions = [
            'headerRow' => $this->headerRow,
            'detailStartRow' => $this->detailStartRow,
            'detailEndRow' => $this->detailStartRow + $detailsCount - 1,
            'totalRow' => $this->detailStartRow + $detailsCount,
            'paymentInfoRow' => $this->detailStartRow + $detailsCount + 1,
            'managerRow' => $this->detailStartRow + $detailsCount + 2,
            'dividerRow' => $this->detailStartRow + $detailsCount + 3,
            'thankRow' => $this->detailStartRow + $detailsCount + 4,
            'contactRow' => $this->detailStartRow + $detailsCount + 5,
        ];

        return $positions;
    }

    /**
     * Thiết lập kiểu dáng cho các ô trong bảng tính
     *
     * @param Worksheet $sheet Trang tính Excel
     * @return array Mảng các kiểu định dạng
     */
    public function styles(Worksheet $sheet)
    {
        // Lấy vị trí các dòng quan trọng
        $positions = $this->calculateRowPositions();

        // Thiết lập kiểu dáng cho từng dòng
        $styles = [
            // Dòng 1: Logo công ty
            1 => ['height' => 60],

            // Dòng 3: Tiêu đề hóa đơn
            3 => [
                'font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => $this->textColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],

            // Dòng 4: Thông tin phụ
            4 => [
                'font' => ['size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],

            // Dòng 5: Thông tin bên trái
            5 => [
                'font' => ['size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ],

            // Dòng 6: Thông tin khác
            6 => ['font' => ['size' => 11]],

            // Dòng tiêu đề bảng
            $positions['headerRow'] => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->primaryColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => $this->borderColor]],
                ],
            ],

            // Dòng tổng cộng
            $positions['totalRow'] => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => $this->accentColor]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->secondaryColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],

            // Dòng chữ ký trưởng ban quản lý
            $positions['managerRow'] => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],

            // Dòng lời cảm ơn
            $positions['thankRow'] => [
                'font' => ['italic' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],

            // Dòng thông tin liên hệ
            $positions['contactRow'] => [
                'font' => ['size' => 9],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        // Thiết lập kiểu dáng cho dòng thông tin thanh toán
        $sheet->getRowDimension($positions['paymentInfoRow'])->setRowHeight(100);
        $sheet->mergeCells("A{$positions['paymentInfoRow']}:D{$positions['paymentInfoRow']}");
        $sheet->getStyle("A{$positions['paymentInfoRow']}:D{$positions['paymentInfoRow']}")->applyFromArray([
            'font' => ['size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $this->secondaryColor]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
                'indent' => 1,
            ],
        ]);

        return $styles;
    }

    /**
     * Thêm hình ảnh vào bảng tính (logo và mã QR)
     *
     * @return array Mảng các đối tượng Drawing
     */
    public function drawings()
    {
        $positions = $this->calculateRowPositions();
        $drawings = [];

        // Thêm logo công ty vào góc trên bên trái
        $logo = new Drawing();
        $logo->setName('Logo');
        $logo->setDescription('Company Logo');
        $logo->setPath(public_path('logo.png'));  // Đường dẫn đến file logo
        $logo->setHeight(60);
        $logo->setCoordinates('A1');  // Vị trí ô A1
        $logo->setOffsetX(10);        // Căn lề ngang
        $logo->setOffsetY(10);        // Căn lề dọc
        $drawings[] = $logo;

        // Thêm mã QR cho thanh toán
        $qrCode = new Drawing();
        $qrCode->setName('QR Code');
        $qrCode->setDescription('Payment QR Code');
        $qrCode->setPath(public_path('yb_qr_code.jpg'));  // Đường dẫn đến file mã QR
        $qrCode->setHeight(140);  // Giảm kích thước để phù hợp với dòng
        $qrCode->setWidth(140);
        $qrCode->setCoordinates('D' . $positions['paymentInfoRow']);  // Vị trí ở cột E
        $qrCode->setOffsetX(100);
        $qrCode->setOffsetY(30);
        $drawings[] = $qrCode;

        return $drawings;
    }

    /**
     * Định dạng các cột trong bảng tính
     *
     * @return array Mảng các định dạng số
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,                 // Định dạng số thường
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Định dạng số có dấu phân cách hàng nghìn
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Định dạng số có dấu phân cách hàng nghìn
            'E' => NumberFormat::FORMAT_TEXT,                   // Định dạng văn bản cho cột ghi chú
        ];
    }

    /**
     * Đăng ký các sự kiện cho bảng tính
     *
     * @return array Mảng các sự kiện
     */
    public function registerEvents(): array
    {
        return [
            // Sự kiện sau khi trang tính được tạo
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $positions = $this->calculateRowPositions();

                // Thiết lập chiều cao và chiều rộng
                $this->setRowHeights($sheet, $positions);
                $this->setColumnWidths($sheet);

                // Gộp các ô
                $this->mergeCells($sheet, $positions);

                // Tạo viền và màu nền
                $this->createOuterBorder($sheet);
                $this->createTableBorders($sheet, $positions);
                $this->createAlternateRowColors($sheet, $positions);

                // Căn chỉnh dữ liệu
                $this->alignColumns($sheet, $positions);

                // Định dạng phần cuối
                $this->formatTotalRow($sheet, $positions);
                $this->formatPaymentRow($sheet, $positions);
                $this->formatFooterSection($sheet, $positions);
            },
        ];
    }

    /**
     * Thiết lập chiều cao cho các dòng
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function setRowHeights(Worksheet $sheet, array $positions)
    {
        // Thiết lập chiều cao cho các dòng cố định ở phần đầu
        $sheet->getRowDimension(1)->setRowHeight(60);  // Logo
        $sheet->getRowDimension(2)->setRowHeight(30);  // Khoảng trống
        $sheet->getRowDimension(3)->setRowHeight(20);  // Tiêu đề
        $sheet->getRowDimension($positions['headerRow'])->setRowHeight(25); // Tiêu đề bảng

        // Thiết lập chiều cao cho các dòng dựa trên vị trí tương đối
        $sheet->getRowDimension($positions['paymentInfoRow'])->setRowHeight(150); // Khu vực thông tin thanh toán
        // $sheet->getRowDimension($positions['dividerRow'])->setRowHeight(100);
        $sheet->getRowDimension($positions['managerRow'])->setRowHeight(200);    // Phần chữ ký
        $sheet->getRowDimension($positions['thankRow'])->setRowHeight(40);   
        $sheet->getRowDimension($positions['contactRow'])->setRowHeight(40);  // Lời cảm ơn
    }

    /**
     * Thiết lập chiều rộng cho các cột
     *
     * @param Worksheet $sheet Trang tính
     */
    private function setColumnWidths(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30); // Tên phí
        $sheet->getColumnDimension('B')->setWidth(12); // Số lượng
        $sheet->getColumnDimension('C')->setWidth(20); // Đơn giá
        $sheet->getColumnDimension('D')->setWidth(20); // Thành tiền
        $sheet->getColumnDimension('E')->setWidth(28); // Ghi chú
    }

    /**
     * Gộp các ô trong bảng tính
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function mergeCells(Worksheet $sheet, array $positions)
    {
        // Phần tiêu đề và thông tin chung
        $sheet->mergeCells('A3:E3'); // Tiêu đề
        $sheet->mergeCells('A4:E4'); // Thông tin phụ
        $sheet->mergeCells('A5:E5'); // Thông tin khách hàng
        $sheet->mergeCells('A6:B6'); // Thông tin bên trái
        $sheet->mergeCells('C6:D6'); // Thông tin bên phải

        // Phần chân trang
        $sheet->mergeCells("A{$positions['paymentInfoRow']}:D{$positions['paymentInfoRow']}"); // Thông tin thanh toán
        $sheet->mergeCells("D{$positions['managerRow']}:E{$positions['managerRow']}"); // Chữ ký BQL
        $sheet->mergeCells("A{$positions['contactRow']}:E{$positions['contactRow']}"); // Thông tin liên hệ
    }

    /**
     * Tạo viền ngoài cho toàn bộ bảng
     *
     * @param Worksheet $sheet Trang tính
     */
    private function createOuterBorder(Worksheet $sheet)
    {
        $positions = $this->calculateRowPositions();
        $fullRange = "A3:E" . $positions['contactRow'];
        $sheet->getStyle($fullRange)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => $this->primaryColor],
                ],
            ],
        ]);
    }

    /**
     * Tạo viền cho bảng chi tiết
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function createTableBorders(Worksheet $sheet, array $positions)
    {
        $tableRange = "A{$positions['headerRow']}:E{$positions['totalRow']}";
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => $this->borderColor],
                ],
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => $this->primaryColor],
                ],
            ],
        ]);
    }

    /**
     * Tạo màu nền cho các dòng xen kẽ (màu dòng lẻ)
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function createAlternateRowColors(Worksheet $sheet, array $positions)
    {
        $invoice = Invoice::findOrFail($this->invoiceId);
        $detailsCount = $invoice->invoiceDetails->count();

        for ($i = 0; $i < $detailsCount; $i++) {
            if ($i % 2 == 1) { // Chỉ áp dụng cho dòng lẻ
                $sheet->getStyle('A' . ($positions['detailStartRow'] + $i) . ':E' . ($positions['detailStartRow'] + $i))
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($this->secondaryColor);
            }
        }
    }

    /**
     * Căn chỉnh dữ liệu trong các cột
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function alignColumns(Worksheet $sheet, array $positions)
    {
        // Căn giữa cột số lượng
        $sheet->getStyle("B{$positions['detailStartRow']}:B{$positions['detailEndRow']}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Căn phải các cột đơn giá và thành tiền
        $sheet->getStyle("C{$positions['detailStartRow']}:D{$positions['detailEndRow']}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Căn trái cột ghi chú
        $sheet->getStyle("E{$positions['detailStartRow']}:E{$positions['detailEndRow']}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    /**
     * Tạo định dạng cho dòng tổng cộng
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function formatTotalRow(Worksheet $sheet, array $positions)
    {
        $sheet->getStyle("A{$positions['totalRow']}:E{$positions['totalRow']}")->applyFromArray([
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => $this->primaryColor],
                ],
            ],
        ]);
    }

    /**
     * Định dạng dòng thông tin thanh toán
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function formatPaymentRow(Worksheet $sheet, array $positions)
    {
        $sheet->getStyle("A{$positions['paymentInfoRow']}:E{$positions['paymentInfoRow']}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => $this->borderColor],
                ],
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => $this->primaryColor],
                ],
            ],
        ]);

        // Định dạng ô mã QR riêng
        $sheet->getStyle("E{$positions['paymentInfoRow']}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    /**
     * Hoàn thiện các định dạng phần cuối (chữ ký, thông tin liên hệ)
     *
     * @param Worksheet $sheet Trang tính
     * @param array $positions Vị trí các dòng quan trọng
     */
    private function formatFooterSection(Worksheet $sheet, array $positions)
    {
        // Thêm chữ ký trưởng ban quản lý
        $sheet->setCellValue("D{$positions['managerRow']}", "\nTRƯỞNG BAN QUẢN LÝ");
        $sheet->getStyle("D{$positions['managerRow']}:E{$positions['managerRow']}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        // Tạo đường kẻ ngang phía dưới phần chữ ký
        $sheet->getStyle("A{$positions['dividerRow']}:E{$positions['dividerRow']}")->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => $this->borderColor],
                ],
            ],
        ]);
    }
}