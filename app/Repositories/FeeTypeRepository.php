<?php

namespace App\Repositories;

use App\Models\FeeType;
use Carbon\Carbon;

class FeeTypeRepository
{
    public function getFeeTypes()
    {
        $feeTypes = FeeType::with('feeSubtypes')->get();
        return $feeTypes;
    }

    public function createFeeType($request)
    {
        $feeType = FeeType::create([
            'code' => $request['code'],
            'name' => $request['name'],
            'description' => $request['description'],
            'is_active' => $request['is_active'],
        ]);
        return $feeType;
    }

    public function updateFeeType($id, $request)
    {
        $update = FeeType::where('fee_types_id', $id)->update([
            'code' => $request['code'],
            'name' => $request['name'],
            'description' => $request['description'],
            'is_active' => $request['is_active'],
            'updated_at' => Carbon::now(),
        ]);

        return $update;
    }

    public function getFeeTypeCode()
    {
        $feeTypeCode = FeeType::select('fee_types_id', 'name')->get();
        return $feeTypeCode;
    }
}