<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use App\Services\ApiAdmin\BalanceService;

class UtilityController extends Controller
{
    public function __construct(
        public BalanceService $balanceService
    ) {}

    /**
     * Import chỉ số điện nước từ Excel
     * POST /api/utilities/import
     */

    public function importFromExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
            'period' => 'required|date_format:Y-m',
            'building_id' => 'required|exists:buildings,building_id',
        ]);

        try {
            $file = $request->file('file');
            $period = $request->period;
            $buildingId = $request->building_id;

            $feeTypeIds = DB::table('fee_types')
                ->where('code', 'FEE_ELECTRIC_WATER')
                ->pluck('fee_types_id');

            $hasFees = DB::table('building_fees')
                ->where('building_id', $buildingId)
                ->whereIn('fee_types_id', $feeTypeIds)
                ->exists();

            if (!$hasFees) {
                throw new \Exception("Tòa nhà này chưa được cấu hình phí điện/nước, không thể import dữ liệu.");
            }

            // Load Excel file
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Bỏ qua header (dòng đầu)
            array_shift($rows);

            // Lọc các dòng trống
            $rows = array_filter($rows, function ($row) {
                return !empty(array_filter($row));
            });

            $results = [
                'total' => 0,
                'success' => 0,
                'errors' => []
            ];

            $savedApartmentIds = []; // Lưu các apartment_id đã import thành công

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 vì có header và index bắt đầu từ 0
                $results['total']++;

                // Validate dữ liệu
                $validation = $this->validateRow($row, $rowNumber);
                if (!$validation['valid']) {
                    $results['errors'][] = $validation['error'];
                    continue;
                }

                // Parse dữ liệu
                try {
                    $data = $this->parseRowData($row, $period);

                    // Kiểm tra nếu đã có bản ghi "confirmed" của căn này + loại utility + tháng này
                    $existsConfirmed = DB::table('utility_readings')
                        ->where('apartment_id', $data['apartment_id'])
                        ->where('utility_type', $data['utility_type'])
                        ->where('period', $period)
                        ->where('status', 'confirmed')
                        ->exists();

                    if ($existsConfirmed) {
                        $apartmentName = DB::table('apartments')
                            ->where('apartment_id', $data['apartment_id'])
                            ->value('apartment_number');

                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'message' => "Không thể import: Căn hộ {$apartmentName} ({$data['utility_type']}) đã được xác nhận (confirmed) trong kỳ {$period}"
                        ];

                        continue;
                    }

                    // Nếu chưa có confirmed thì lưu bình thường
                    $this->saveUtilityReading($data);
                    $results['success']++;

                    if (!in_array($data['apartment_id'], $savedApartmentIds)) {
                        $savedApartmentIds[] = $data['apartment_id'];
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'message' => $e->getMessage()
                    ];
                }
            }

            // Lưu log import
            DB::table('utility_import_logs')->insert([
                'period' => $period,
                'utility_type' => 'mixed', // Hoặc phân biệt nếu file chỉ có 1 loại
                'file_name' => $file->getClientOriginalName(),
                'total_rows' => $results['total'],
                'success_rows' => $results['success'],
                'error_rows' => count($results['errors']),
                'errors' => json_encode($results['errors']),
                'imported_by' => auth()->id(),
                'imported_at' => now()
            ]);

            DB::commit();

            // Lấy danh sách các bản ghi vừa import để trả về
            $importedReadings = [];
            if (!empty($savedApartmentIds)) {
                $importedReadings = DB::table('utility_readings as ur')
                    ->join('apartments as a', 'ur.apartment_id', '=', 'a.apartment_id')
                    ->join('buildings as b', 'ur.building_id', '=', 'b.building_id')
                    ->where('ur.period', $period)
                    ->whereIn('ur.apartment_id', $savedApartmentIds) // Chỉ lấy các căn hộ vừa import
                    ->select(
                        'ur.*',
                        'a.apartment_number as apartment_number',
                        'b.name as building_name'
                    )
                    ->orderBy('ur.created_at', 'desc')
                    ->get();
            }

            // Xác định message dựa trên kết quả
            $hasErrors = count($results['errors']) > 0;
            $message = $hasErrors
                ? "Import hoàn tất: {$results['success']}/{$results['total']} dòng thành công, " . count($results['errors']) . " lỗi"
                : "Import thành công {$results['success']}/{$results['total']} dòng";

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'total' => $results['total'],
                    'success' => $results['success'],
                    'error_rows' => count($results['errors']),
                    'errors' => $results['errors'],
                    'imported_readings' => $importedReadings // Thêm danh sách vừa import
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Lỗi import: ' . $e->getMessage(),
                'data' => [
                    'total' => 0,
                    'success' => 0,
                    'error_rows' => 0,
                    'errors' => [],
                    'imported_readings' => []
                ]
            ], 500);
        }
    }

    /**
     * Validate từng dòng dữ liệu
     */

    private function validateRow($row, $rowNumber)
    {
        // Format: STT | Tòa nhà | Căn hộ | Loại | Chỉ số đầu | Chỉ số cuối | Ghi chú | Người ghi | Ngày ghi

        if (empty($row[2])) { // Căn hộ
            return [
                'valid' => false,
                'error' => ['row' => $rowNumber, 'message' => 'Thiếu tên căn hộ']
            ];
        }

        if (!in_array(strtoupper($row[3]), ['ELECTRIC', 'WATER'])) {
            return [
                'valid' => false,
                'error' => ['row' => $rowNumber, 'message' => 'Loại phải là ELECTRIC hoặc WATER']
            ];
        }

        if (!is_numeric($row[4]) || !is_numeric($row[5])) {
            return [
                'valid' => false,
                'error' => ['row' => $rowNumber, 'message' => 'Chỉ số phải là số']
            ];
        }

        if ($row[5] < $row[4]) {
            return [
                'valid' => false,
                'error' => ['row' => $rowNumber, 'message' => 'Chỉ số cuối phải >= chỉ số đầu']
            ];
        }

        return ['valid' => true];
    }

    /**
     * Parse dữ liệu từ Excel row
     */
    private function parseRowData($row, $period)
    {
        $buildingName = trim($row[1]);
        $apartmentName = trim($row[2]);
        $utilityType = strtolower($row[3]);
        $previousReading = floatval($row[4]);
        $currentReading = floatval($row[5]);
        $note = $row[6] ?? null;
        $readerName = $row[7] ?? 'System';
        $readingDate = !empty($row[8]) ? Carbon::parse($row[8])->format('Y-m-d') : now()->format('Y-m-d');

        // Tìm apartment_id
        $apartment = DB::table('apartments as a')
            ->join('buildings as b', 'a.building_id', '=', 'b.building_id')
            ->where('a.apartment_number', $apartmentName)
            ->where('b.name', $buildingName)
            ->select('a.apartment_id', 'a.building_id')
            ->first();

        // dd($apartment);

        if (!$apartment) {
            throw new \Exception("Không tìm thấy căn hộ {$apartmentName} - Tòa {$buildingName}");
        }

        // Tìm đơn giá
        $feeTypeCode = $utilityType === 'electric' ? 'ELECTRIC' : 'WATER';

        $unitPrice = DB::table('building_fees as bf')
            ->join('fee_subtypes as fs', 'bf.fee_subtypes_id', '=', 'fs.fee_subtypes_id')
            ->where('bf.building_id', $apartment->building_id)
            ->where('fs.code', '=', $feeTypeCode)
            ->where('bf.effective_from', '<=', now())
            ->orderBy('bf.effective_from', 'desc')
            ->value('bf.price') ?? 0;

        $consumption = $currentReading - $previousReading;
        $amount = $consumption * $unitPrice;

        return [
            'apartment_id' => $apartment->apartment_id,
            'building_id' => $apartment->building_id,
            'utility_type' => $utilityType,
            'period' => $period,
            'previous_reading' => $previousReading,
            'current_reading' => $currentReading,
            'consumption' => $consumption,
            'unit_price' => $unitPrice,
            'amount' => $amount,
            'reading_date' => $readingDate,
            'reader_name' => $readerName,
            'note' => $note,
            'status' => 'pending'
        ];
    }

    /**
     * Lưu chỉ số vào database
     */
    private function saveUtilityReading($data)
    {
        // Upsert: Update nếu đã tồn tại, Insert nếu chưa
        DB::table('utility_readings')->updateOrInsert(
            [
                'apartment_id' => $data['apartment_id'],
                'utility_type' => $data['utility_type'],
                'period' => $data['period']
            ],
            array_merge($data, [
                'updated_at' => now(),
                'created_at' => now()
            ])
        );
    }

    /**
     * Tính tiền điện nước và cập nhật vào hóa đơn THÁNG SAU
     * POST /api/utilities/calculate-and-bill
     * 
     * VD: Chỉ số tháng 9 → Tính vào hóa đơn tháng 10
     */
    public function calculateAndBill(Request $request)
    {
        $request->validate([
            'reading_period' => 'required|date_format:Y-m',
            'building_id' => 'nullable|exists:buildings,building_id',
        ]);

        $readingPeriod = $request->reading_period;
        $buildingId = $request->building_id;
        $invoicePeriod = Carbon::parse($readingPeriod . '-01')->addMonth()->format('Y-m');

        try {
            DB::beginTransaction();

            // Lấy danh sách chỉ số của THÁNG TRƯỚC đã xác nhận
            $readings = DB::table('utility_readings')
                ->where('period', $readingPeriod) // Tháng 9
                ->where('status', 'pending')
                ->when($buildingId, fn($q) => $q->where('building_id', $buildingId))
                // ->when($utilityType, fn($q) => $q->where('utility_type', $utilityType))
                ->get();

            $processed = 0;
            $errors = [];
            // dd($readings->toSql(), $readings->getBindings());

            foreach ($readings as $reading) {
                try {
                    $invoice = DB::table('invoices')
                        ->where('apartment_id', $reading->apartment_id)
                        ->where('period', $invoicePeriod)
                        ->first();

                    if (!$invoice) {
                        $errors[] = "Chưa có hóa đơn tháng {$invoicePeriod} cho căn hộ ID {$reading->apartment_id}. Vui lòng tạo hóa đơn trước!";
                        continue;
                    }

                    // Kiểm tra đã thêm chưa (tránh duplicate)
                    $exists = DB::table('invoice_details')
                        ->where('invoice_id', $invoice->invoice_id)
                        ->where('reference_type', 'utility_reading')
                        ->where('reference_id', $reading->utility_reading_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    // Lấy thông tin fee
                    $feeTypeCode = $reading->utility_type === 'electric' ? 'ELECTRIC' : 'WATER';
                    $feeTypeName = $reading->utility_type === 'electric' ? 'Tiền điện' : 'Tiền nước';
                    $desc = $this->getUtilityDescription($reading);

                    $feeInfo = DB::table('building_fees as bf')
                        ->join('fee_types as ft', 'bf.fee_types_id', '=', 'ft.fee_types_id')
                        ->join('fee_subtypes as fs', 'bf.fee_subtypes_id', '=', 'fs.fee_subtypes_id')
                        ->where('bf.building_id', $reading->building_id)
                        ->where('fs.code', $feeTypeCode)
                        ->where('bf.effective_from', '<=', now())
                        ->orderBy('bf.effective_from', 'desc')
                        ->select('bf.*', 'ft.fee_types_id as fee_type_id')
                        ->first();

                    if (!$feeInfo) {
                        $errors[] = "Không tìm thấy biểu phí {$feeTypeCode}";
                        continue;
                    }

                    // Thêm vào chi tiết hóa đơn
                    DB::table('invoice_details')->insert([
                        'invoice_id' => $invoice->invoice_id,
                        'fee_types_id' => $feeInfo->fee_types_id,
                        'fee_subtypes_id' => $feeInfo->fee_subtypes_id,
                        'building_fee_id' => $feeInfo->building_fee_id,
                        'reference_id' => $reading->utility_reading_id,
                        'reference_type' => 'utility_reading',
                        'description' => $desc,
                        'quantity' => $reading->consumption,
                        'unit_price' => $reading->unit_price,
                        'amount' => $reading->amount,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Cập nhật tổng tiền hóa đơn
                    $this->updateInvoiceTotal($invoice->invoice_id);

                    // Cập nhật bảng transactions & balance
                    $this->syncFinancialState($invoice->invoice_id, $desc);

                    // Đánh dấu đã xuất hóa đơn
                    DB::table('utility_readings')
                        ->where('utility_reading_id', $reading->utility_reading_id)
                        ->update(['status' => 'billed', 'updated_at' => now()]);

                    $processed++;
                } catch (\Exception $e) {
                    $errors[] = "Lỗi xử lý reading ID {$reading->utility_reading_id}: " . $e->getMessage();
                    throw $e;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã tính tiền điện nước tháng {$readingPeriod} vào hóa đơn tháng {$invoicePeriod} cho {$processed} căn hộ",
                'data' => [
                    'reading_period' => $readingPeriod,
                    'invoice_period' => $invoicePeriod,
                    'processed' => $processed,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mô tả cho chi tiết hóa đơn
     */
    private function getUtilityDescription($reading)
    {
        $type = $reading->utility_type === 'electric' ? 'Điện' : 'Nước';
        $unit = $reading->utility_type === 'electric' ? 'số' : 'm³';

        // Hiển thị rõ kỳ tính (tháng trước)
        $periodDisplay = Carbon::parse($reading->period . '-01')->format('m/Y');

        return sprintf(
            "%s tháng %s: %s → %s (%s %s)",
            $type,
            $periodDisplay,
            number_format($reading->previous_reading, 0),
            number_format($reading->current_reading, 0),
            number_format($reading->consumption, 0),
            $unit
        );
    }

    /**
     * Cập nhật tổng tiền hóa đơn
     */
    private function updateInvoiceTotal($invoiceId)
    {
        //Tính tổng tiền từ bảng chi tiết hóa đơn
        $total = DB::table('invoice_details')
            ->where('invoice_id', $invoiceId)
            ->sum('amount');

        //Lấy thông tin hóa đơn hiện tại
        $invoice = DB::table('invoices')->where('invoice_id', $invoiceId)->first();

        if (!$invoice) {
            throw new \Exception("Không tìm thấy hóa đơn ID: {$invoiceId}");
        }

        //Tính toán closing_balance = total_amount - paid_amount
        $newClosingBalance = $total - $invoice->paid_amount;

        //Cập nhật lại invoice
        DB::table('invoices')
            ->where('invoice_id', $invoiceId)
            ->update([
                'total_amount' => $total,
                'closing_balance' => $newClosingBalance,
                'updated_at' => now(),
            ]);
    }

    /**
     * Cập nhật bảng transactions & balance
     */
    private function syncFinancialState($invoiceId, $desc)
    {
        $invoice = DB::table('invoices')->where('invoice_id', $invoiceId)->first();
        if (!$invoice) return;

        $apartmentId = $invoice->apartment_id;
        $buildingId = $invoice->building_id;

        // Tổng tiền hóa đơn hiện tại
        $invoiceTotal = DB::table('invoice_details')
            ->where('invoice_id', $invoiceId)
            ->sum('amount');

        // Tổng các transaction liên quan đến hóa đơn này
        $transactionTotal = DB::table('apartment_transactions')
            ->where('reference_type', 'invoice')
            ->where('reference_id', $invoiceId)
            ->sum('amount');

        // Tính chênh lệch
        $delta = $invoiceTotal - $transactionTotal;

        if (abs($delta) > 0.01) {

            $type = $delta > 0 ? 'charge' : 'adjust';

            $this->balanceService->updateBalance(
                apartmentId: $apartmentId,
                buildingId: $buildingId,
                amount: abs($delta),
                type: $type,
                userId: auth()->id() ?? null,
                description: "Điều chỉnh sau khi thêm phí {$desc}",
                referenceType: "invoice",
                referenceId: $invoiceId
            );
        }

        // Nếu hóa đơn đã thanh toán toàn bộ → đổi trạng thái thành "partial" nếu có nợ mới
        if ($invoice->status == 'paid' && $delta > 0) {
            DB::table('invoices')
                ->where('invoice_id', $invoiceId)
                ->update([
                    'status' => 'partial',
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Tải template Excel mẫu
     * GET /api/utilities/template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['STT', 'Tòa nhà', 'Căn hộ', 'Loại', 'Chỉ số đầu', 'Chỉ số cuối', 'Ghi chú', 'Người ghi', 'Ngày ghi'];
        $sheet->fromArray($headers, null, 'A1');

        // Sample data
        $sampleData = [
            [1, 'Sunrise', 'A101', 'ELECTRIC', 1200, 1400, '', 'Nguyễn Văn A', date('d/m/Y')],
            [2, 'Sunrise', 'A101', 'WATER', 145, 160, '', 'Nguyễn Văn A', date('d/m/Y')],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Styling
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_DienNuoc_' . date('Ym') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Danh sách chỉ số điện nước
     * GET /api/utilities
     */
    public function index(Request $request, $id)
    {
        $query = DB::table('utility_readings as ur')
            ->join('apartments as a', 'ur.apartment_id', '=', 'a.apartment_id')
            ->join('buildings as b', 'ur.building_id', '=', 'b.building_id')
            ->where('ur.building_id', $id)
            ->select(
                'ur.*',
                'a.apartment_number as apartment_name',
                'b.name as building_name'
            );

        if ($request->period) {
            $query->where('ur.period', $request->period);
        }

        if ($request->building_id) {
            $query->where('ur.building_id', $request->building_id);
        }

        if ($request->utility_type) {
            $query->where('ur.utility_type', $request->utility_type);
        }

        if ($request->status) {
            $query->where('ur.status', $request->status);
        }

        $readings = $query->orderBy('ur.created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $readings
        ]);
    }
}
