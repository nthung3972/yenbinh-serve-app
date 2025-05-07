<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\InvoiceService;
use App\Services\ApiAdmin\BuildingService;
use App\Helper\Response;
use App\Http\Requests\InvoiceRequest\CreateInvoiceRequest;
use App\Http\Requests\InvoiceRequest\UpdateInvoiceRequest;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;

class InvoiceController extends Controller
{
    public function __construct(
        public InvoiceService $invoiceService,
        public BuildingService $buildingService,
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

    public function getApartmentFees($apartmentId)
    {
        // Lấy thông tin phí quản lý và thù lao ban quản trị
        $managementInfo = DB::table('apartments as a')
            ->join('buildings as b', 'a.building_id', '=', 'b.building_id')
            ->where('a.apartment_id', $apartmentId)
            ->select('a.area', 'b.management_fee_per_m2', 'b.management_board_fee_per_m2')
            ->first();

        // Tính phí quản lý vận hành
        $managementFeeAmount = $managementInfo->area * $managementInfo->management_fee_per_m2;
        $managementDescription = 'Diện tích ' . $managementInfo->area . 'm², phí ' . number_format($managementInfo->management_fee_per_m2, 0, ',', '.') . 'đ/m²';

        // Tính phí thù lao ban quản trị
        $managementBoardFeeAmount = $managementInfo->management_board_fee_per_m2 ? ($managementInfo->area * $managementInfo->management_board_fee_per_m2) : 0;
        $managementBoardDescription = $managementInfo->management_board_fee_per_m2
            ? 'Diện tích ' . $managementInfo->area . 'm², thù lao ' . number_format($managementInfo->management_board_fee_per_m2, 0, ',', '.') . 'đ/m²'
            : 'Không có phí thù lao';

        // Lấy danh sách loại xe, số lượng và phí
        $parkingFees = DB::table('vehicles as v')
            ->join('vehicle_types as vt', 'v.vehicle_type_id', '=', 'vt.vehicle_type_id')
            ->join('apartments as a', 'v.apartment_id', '=', 'a.apartment_id')
            ->join('building_vehicle_fees as bvf', function ($join) {
                $join->on('bvf.vehicle_type_id', '=', 'vt.vehicle_type_id')
                    ->on('bvf.building_id', '=', 'a.building_id');
            })
            ->where('v.apartment_id', $apartmentId)
            ->groupBy('vt.vehicle_type_name', 'bvf.parking_fee')
            ->selectRaw('
            vt.vehicle_type_name,
            COUNT(v.vehicle_id) as vehicle_count,
            bvf.parking_fee as parking_fee_per_vehicle
        ')
            ->get();

        // Tính tổng phí đỗ xe và mô tả
        $parkingFeeTotal = 0;
        $parkingDescriptionParts = [];

        foreach ($parkingFees as $fee) {
            $amount = 0;
            if ($fee->vehicle_type_name === 'Ô tô' && $fee->vehicle_count > 0) {
                // Phí cho ô tô: parking_fee_per_vehicle cho chiếc đầu, 120% cho các chiếc tiếp theo
                $firstCarFee = $fee->parking_fee_per_vehicle;
                $additionalCarFee = $firstCarFee * 1.2;
                if ($fee->vehicle_count == 1) {
                    $amount = $firstCarFee;
                } else {
                    $amount = $firstCarFee + ($fee->vehicle_count - 1) * $additionalCarFee;
                }
                $parkingDescriptionParts[] = "{$fee->vehicle_count} {$fee->vehicle_type_name} (1 x " . number_format($firstCarFee, 0, ',', '.') . "đ, " . ($fee->vehicle_count - 1) . " x " . number_format($additionalCarFee, 0, ',', '.') . "đ)";
            } else {
                // Các loại xe khác sử dụng phí từ cơ sở dữ liệu
                $amount = $fee->vehicle_count * $fee->parking_fee_per_vehicle;
                $parkingDescriptionParts[] = "{$fee->vehicle_count} {$fee->vehicle_type_name}";
            }
            $parkingFeeTotal += $amount;
        }

        $parkingDescription = implode(', ', $parkingDescriptionParts);

        // Tạo mảng kết quả
        $result = [
            [
                'type' => 'Phí quản lý vận hành',
                'amount' => $managementFeeAmount,
                'description' => $managementDescription,
            ],
            [
                'type' => 'Phí gửi xe',
                'amount' => $parkingFeeTotal,
                'description' => $parkingDescription ?: 'Không có phương tiện',
            ]
        ];

        // Thêm phí thù lao ban quản trị nếu có
        if ($managementBoardFeeAmount > 0) {
            $result[] = [
                'type' => 'Thù lao ban quản trị',
                'amount' => $managementBoardFeeAmount,
                'description' => $managementBoardDescription,
            ];
        }

        return response()->json($result);
    }

    public function create(CreateInvoiceRequest $request)
    {
        try {
            DB::beginTransaction();
            $invoice = $this->invoiceService->create($request->all());
            DB::commit();
            return Response::data(['data' => $invoice]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return Response::dataError($e->getCode(), $e->getErrors(), "Lỗi xác thực dữ liệu");
        } catch (\Throwable $th) {
            DB::rollBack();
            return Response::dataError($th->getCode() ?: 500, ['general' => [$th->getMessage()]], "Lỗi hệ thống");
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

    public function update(UpdateInvoiceRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $invoices = $this->invoiceService->update($request->all(), $id);
            DB::commit();
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function destroy($id)    
    {
        try {
            DB::beginTransaction();
            $invoices = $this->invoiceService->delete($id);
            DB::commit();
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
