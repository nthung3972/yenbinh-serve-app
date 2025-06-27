<?php

namespace App\Services\ApiAdmin;

use Illuminate\Support\Facades\DB;

class FeeService
{
    public function getFeesForApartment($apartmentId)
    {
        $managementInfo = DB::table('apartments as a')
            ->join('buildings as b', 'a.building_id', '=', 'b.building_id')
            ->where('a.apartment_id', $apartmentId)
            ->select('a.area', 'b.management_fee_per_m2', 'b.management_board_fee_per_m2')
            ->first();

        $managementFeeAmount = $managementInfo->area * $managementInfo->management_fee_per_m2;
        $managementDescription = 'Diện tích ' . $managementInfo->area . 'm², phí ' . number_format($managementInfo->management_fee_per_m2, 0, ',', '.') . 'đ/m²';

        $managementBoardFeeAmount = $managementInfo->management_board_fee_per_m2 ? ($managementInfo->area * $managementInfo->management_board_fee_per_m2) : 0;
        $managementBoardDescription = $managementInfo->management_board_fee_per_m2
            ? 'Diện tích ' . $managementInfo->area . 'm², thù lao ' . number_format($managementInfo->management_board_fee_per_m2, 0, ',', '.') . 'đ/m²'
            : 'Không có phí thù lao';

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

        $parkingFeeTotal = 0;
        $parkingDescriptionParts = [];

        foreach ($parkingFees as $fee) {
            $amount = 0;
            if ($fee->vehicle_type_name === 'Ô tô' && $fee->vehicle_count > 0) {
                $firstCarFee = $fee->parking_fee_per_vehicle;
                $additionalCarFee = $firstCarFee * 1.2;
                $amount = $firstCarFee + max(0, $fee->vehicle_count - 1) * $additionalCarFee;
                $parkingDescriptionParts[] = "{$fee->vehicle_count} {$fee->vehicle_type_name} (1 x " . number_format($firstCarFee, 0, ',', '.') . "đ, " . ($fee->vehicle_count - 1) . " x " . number_format($additionalCarFee, 0, ',', '.') . "đ)";
            } else {
                $amount = $fee->vehicle_count * $fee->parking_fee_per_vehicle;
                $parkingDescriptionParts[] = "{$fee->vehicle_count} {$fee->vehicle_type_name}";
            }
            $parkingFeeTotal += $amount;
        }

        $parkingDescription = implode(', ', $parkingDescriptionParts);

        $result = [
            [
                'fee_type_id' => 1, // Phí quản lý
                'amount' => $managementFeeAmount,
                'price' => $managementFeeAmount,
                'quantity' => 1,
                'description' => $managementDescription,
            ],
            [
                'fee_type_id' => 2, // Phí gửi xe
                'amount' => $parkingFeeTotal,
                'price' => $parkingFeeTotal,
                'quantity' => 1,
                'description' => $parkingDescription ?: 'Không có phương tiện',
            ]
        ];

        if ($managementBoardFeeAmount > 0) {
            $result[] = [
                'fee_type_id' => 3, // Thù lao BQT
                'amount' => $managementBoardFeeAmount,
                'price' => $managementBoardFeeAmount,
                'quantity' => 1,
                'description' => $managementBoardDescription,
            ];
        }

        return $result;
    }
}
