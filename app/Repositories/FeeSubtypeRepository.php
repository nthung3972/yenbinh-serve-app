<?php

namespace App\Repositories;

use App\Models\FeeSubtype;
use Carbon\Carbon;

class FeeSubtypeRepository
{
    public function createFeeSubtype($request)
    {
        $feeSubtype = FeeSubtype::create([
            'fee_types_id' => $request['fee_types_id'],
            'code' => $request['code'],
            'name' => $request['name'],
            'unit' => $request['unit'],
            'note' => $request['note'],
        ]);
        return $feeSubtype;
    }

    public function updateFeeSubtype($id, $request)
    {
        $update = FeeSubtype::where('fee_subtypes_id', $id)->update([
            'fee_types_id' => $request['fee_types_id'],
            'code' => $request['code'],
            'name' => $request['name'],
            'unit' => $request['unit'],
            'note' => $request['note'],
            'updated_at' => Carbon::now(),
        ]);

        return $update;
    }

    public function getByFeeTypeId($id)
    {
        $feeSubtypes = FeeSubtype::select('fee_subtypes_id', 'name')->whereHas('feeType', function ($q) use ($id) {
            $q->where('fee_types_id', $id);
        })->get();
        return $feeSubtypes;
    }
}