<?php

namespace App\Repositories;

use App\Models\Shift;
use Carbon\Carbon;

class ShiftRepository
{
    public function getShifts($perPage, $keyword = null, $buildingId = null)
    {
        $query = Shift::query()->with('building');

        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($buildingId) {
            $query->where('building_id', $buildingId);
        }

        return $query->paginate($perPage);
    }

    public function createShift($request)
    {
        $shift = Shift::create([
            'building_id' => $request['building_id'],
            'name' => $request['name'],
            'description' => $request['description'],
            'start_time' => $request['start_time'],
            'end_time' => $request['end_time'],
            'type' => $request['type'],
        ]);
        return $shift;
    }

    public function getShiftById($id)
    {
        return Shift::with('building')->findOrFail($id);
    }

    public function updateShift($request, $id)
    {
        $update = Shift::where('shift_id', $id)->update([
            'building_id' => $request['building_id'] ?? null,
            'name' => $request['name'] ?? null,
            'description' => $request['description'] ?? null,
            'start_time' => $request['start_time'] ?? null,
            'end_time' => $request['end_time'] ?? null,
            'type' => $request['type'] ?? null,
        ]);

        return $update;
    }
}