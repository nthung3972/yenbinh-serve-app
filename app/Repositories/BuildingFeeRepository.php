<?php

namespace App\Repositories;

use App\Models\BuildingFee;
use App\Models\StaffAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuildingFeeRepository
{

    public function isAssigned($user, $id)
    {
        $isAssigned = StaffAssignment::where('staff_id', $user->id)
            ->where('building_id', $id)
            ->exists();
        return $isAssigned;
    }

    public function feesByBuilding($id, $perPage, $keyword = null, $fee_types_id = null)
    {
        $query = BuildingFee::where('building_id', $id)
            ->with(['feeType', 'feeSubtype']);

        if ($keyword) {
            $query->whereHas('feeType', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('code', 'like', "%$keyword%");
            })->orWhereHas('feeSubtype', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('code', 'like', "%$keyword%");
            });
        }

        if ($fee_types_id) {
            $query->where('fee_types_id', '=', $fee_types_id);
        }

        return $query->paginate($perPage);
    }

    public function show($id)
    {
        $buildingFee = BuildingFee::with('feeType', 'feeSubtype')->where('building_fee_id', $id)->first();
        return $buildingFee;
    }

    public function createBuildingFee($request)
    {
        $buildingFee = BuildingFee::create([
            'fee_types_id' => $request['fee_types_id'],
            'building_id' => $request['building_id'],
            'fee_subtypes_id' => $request['fee_subtypes_id'],
            'price' => $request['price'],
            'unit' => $request['unit'],
            'effective_from' => $request['effective_from'],
            'effective_to' => $request['effective_to'],
            'note' => $request['note'],
        ]);
        return $buildingFee;
    }

    public function updateBuildingFee($id, $request)
    {
        $update = BuildingFee::where('building_fee_id', $id)->update([
            'fee_types_id' => $request['fee_types_id'],
            'building_id' => $request['building_id'],
            'fee_subtypes_id' => $request['fee_subtypes_id'],
            'price' => $request['price'],
            'unit' => $request['unit'],
            'effective_from' => $request['effective_from'],
            'effective_to' => $request['effective_to'],
            'note' => $request['note'],
            'updated_at' => Carbon::now(),
        ]);

        return $update;
    }

    public function feeManagementByBuilding($id)
    {
        $managementFees = DB::table('building_fees as bf')
            ->join('fee_types as ft', 'bf.fee_types_id', '=', 'ft.fee_types_id')
            ->leftJoin('fee_subtypes as fs', 'bf.fee_subtypes_id', '=', 'fs.fee_subtypes_id')
            ->where('bf.building_id', $id)
            ->where('ft.code', 'FEE_MANAGEMENT')
            ->select(
                'bf.*',
                'ft.name as fee_type_name',
                'ft.code as fee_type_code',
                'fs.name as fee_subtype_name',
                'fs.code as fee_subtype_code'
            )
            ->get();
        return $managementFees;
    }
}
