<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\InvoiceService;
use App\Services\ApiAdmin\BuildingService;
use App\Services\ApiAdmin\BalanceService;
use App\Helper\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct(
        public InvoiceService $invoiceService,
        public BuildingService $buildingService,
        public BalanceService $balanceService
    ) {}

    public function getListInvoice(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $invoices = $this->invoiceService->getInvoicesByBuilding($request, $id);
            }
            $invoices = $this->invoiceService->getInvoicesByBuilding($request, $id);
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $invoices = $this->invoiceService->show($id);
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $th->getMessage(),
            ], 500);
            // return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        // Validate request
        $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,invoice_id',
        ]);

        try {
            DB::beginTransaction();
            $invoices = $this->invoiceService->delete($request->invoice_ids);
            DB::commit();
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }


    public function generateMonthlyInvoices(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m', // VD: 2024-11
            'building_id' => 'nullable|exists:buildings,building_id'
        ]);

        $period = $request->period;
        $buildingId = $request->building_id;

        try {
            DB::beginTransaction();

            // 1. Lấy danh sách căn hộ cần tạo hóa đơn
            $apartments = DB::table('apartments')
                ->when($buildingId, fn($q) => $q->where('building_id', $buildingId))
                ->get();


            $invoicesCreated = 0;
            $errors = [];

            foreach ($apartments as $apartment) {
                try {
                    // Kiểm tra đã tồn tại hóa đơn chưa
                    $exists = DB::table('invoices')
                        ->where('apartment_id', $apartment->apartment_id)
                        ->where('period', $period)
                        ->exists();

                    if ($exists) {
                        DB::rollBack(); // Hủy transaction ngay
                        return response()->json([
                            'success' => false,
                            'message' => "Tòa nhà này đã có danh sách hóa đơn tháng {$period}",
                        ], 400);
                    }

                    // Tạo hóa đơn
                    $this->createInvoiceForApartment($apartment, $period);
                    $invoicesCreated++;
                } catch (\Exception $e) {
                    $errors[] = "Lỗi tạo hóa đơn căn hộ {$apartment->apartment_number}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã tạo {$invoicesCreated} hóa đơn cho tháng {$period}",
                'data' => [
                    'invoices_created' => $invoicesCreated,
                    'total_apartments' => $apartments->count(),
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo hóa đơn: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createInvoiceForApartment($apartment, $period)
    {
        $issueDate = Carbon::now();
        $dueDate = $issueDate->copy()->addDays(15);

        $openingBalance = DB::table('apartment_balances')
        ->where('apartment_id', $apartment->apartment_id)
        ->value('current_balance') ?? 0;
        // dd($openingBalance);

        // 1. Tạo hóa đơn master
        $invoiceId = DB::table('invoices')->insertGetId([
            'invoice_number' => $this->generateInvoiceNumber($apartment, $period),
            'apartment_id' => $apartment->apartment_id,
            'building_id' => $apartment->building_id,
            'period' => $period,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'opening_balance' => -$openingBalance,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Thêm phí quản lý
        $this->addManagementFee($invoiceId, $apartment);

        // 3. Thêm phí gửi xe
        $this->addParkingFees($invoiceId, $apartment);

        // 5. Cập nhật tổng tiền
        $totalAmount = DB::table('invoice_details')
            ->where('invoice_id', $invoiceId)
            ->sum('amount');

        DB::table('invoices')
            ->where('invoice_id', $invoiceId)
            ->update([
                'total_amount' => $totalAmount,
                'closing_balance' => -$openingBalance + $totalAmount,
                'updated_at' => now()
            ]);

        $this->balanceService->updateBalance(
            apartmentId: $apartment->apartment_id,
            buildingId: $apartment->building_id,
            amount: $totalAmount,
            type: 'charge',
            userId: auth()->id(),
            description: 'Tạo hóa đơn tháng ' . $period,
            referenceType: 'invoice',
            referenceId: $invoiceId
        );

        return $invoiceId;
    }

    private function addManagementFee($invoiceId, $apartment)
    {
        //Xác định loại căn hộ
        $apartmentType = $apartment->apartment_type;

        //Tìm phí quản lý tương ứng loại căn hộ
        $managementFee = DB::table('building_fees as bf')
            ->join('fee_types as ft', 'bf.fee_types_id', '=', 'ft.fee_types_id')
            ->join('fee_subtypes as fs', 'bf.fee_subtypes_id', '=', 'fs.fee_subtypes_id')
            ->where('bf.building_id', $apartment->building_id)
            ->where('ft.code', 'FEE_MANAGEMENT')
            ->where(function ($query) use ($apartmentType) {
                $query->where('fs.code', $apartmentType)
                    ->orWhereNull('fs.code');
            })
            ->where('bf.effective_from', '<=', now())
            ->orderBy('fs.code', $apartmentType ? 'desc' : 'asc')
            ->orderBy('bf.effective_from', 'desc')
            ->select('bf.*', 'fs.name as subtype_name', 'fs.code as subtype_code')
            ->first();

        if (!$managementFee) {
            return;
        }

        //Lấy diện tích căn hộ để tính phí
        $apartmentArea = DB::table('apartments')
            ->where('apartment_id', $apartment->apartment_id)
            ->value('area') ?? 0;

        //Tính tổng tiền
        $quantity = $apartmentArea;
        $unitPrice = $managementFee->price;
        $amount = $quantity * $unitPrice;

        //Lưu chi tiết hóa đơn
        DB::table('invoice_details')->insert([
            'invoice_id' => $invoiceId,
            'fee_types_id' => $managementFee->fee_types_id,
            'fee_subtypes_id' => $managementFee->fee_subtypes_id,
            'building_fee_id' => $managementFee->building_fee_id,
            'description' => "Phí quản lý căn hộ loại {$managementFee->subtype_name} ({$quantity}m²)",
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function addParkingFees($invoiceId, $apartment)
    {
        // Lấy danh sách xe đang active của căn hộ
        $vehicles = DB::table('vehicles as v')
            ->join('vehicle_types as pvt', 'v.vehicle_type_id', '=', 'pvt.vehicle_type_id')
            ->join('residents as re', 'v.resident_id', '=', 're.resident_id')
            ->where('v.apartment_number', $apartment->apartment_number)
            ->where('v.status', 0)
            ->select('v.*', 'pvt.name as vehicle_type_name', 'pvt.code as vehicle_type_code', 're.full_name as owner_name')
            ->get();

        foreach ($vehicles as $vehicle) {
            // Tìm phí gửi xe tương ứng (theo code của vehicle_type)
            $parkingFee = DB::table('building_fees as bf')
                ->join('fee_types as ft', 'bf.fee_types_id', '=', 'ft.fee_types_id')
                ->join('fee_subtypes as fs', 'bf.fee_subtypes_id', '=', 'fs.fee_subtypes_id')
                ->where('bf.building_id', $apartment->building_id)
                ->where('ft.code', 'FEE_PARKING')
                ->where('fs.code', $vehicle->vehicle_type_code)
                ->where('bf.effective_from', '<=', now())
                ->orderBy('bf.effective_from', 'desc')
                ->first();

            if (!$parkingFee) {
                continue;
            }

            DB::table('invoice_details')->insert([
                'invoice_id' => $invoiceId,
                'fee_types_id' => $parkingFee->fee_types_id,
                'fee_subtypes_id' => $parkingFee->fee_subtypes_id,
                'building_fee_id' => $parkingFee->building_fee_id,
                'reference_id' => $vehicle->vehicle_id,
                'reference_type' => 'vehicle',
                'description' => "{$vehicle->vehicle_type_name} - {$vehicle->license_plate} - {$apartment->apartment_number} ({$vehicle->owner_name})",
                'quantity' => 1,
                'unit_price' => $parkingFee->price,
                'amount' => $parkingFee->price,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function generateInvoiceNumber($apartment, $period)
    {
        // Format: INV-202411-A101
        $periodFormatted = str_replace('-', '', $period);
        return "INV-{$periodFormatted}-{$apartment->apartment_number}";
    }

    private function updateInvoiceTotal($invoiceId)
    {
        $total = DB::table('invoice_details')
            ->where('invoice_id', $invoiceId)
            ->sum('amount');

        // Trừ đi các khoản điều chỉnh (nếu có)
        // $adjustments = DB::table('invoice_adjustments')
        //     ->where('invoice_id', $invoiceId)
        //     ->sum('amount');

        // $finalTotal = $total + $adjustments;

        DB::table('invoices')
            ->where('invoice_id', $invoiceId)
            ->update([
                'total_amount' => $total,
                'updated_at' => now()
            ]);
    }
}
