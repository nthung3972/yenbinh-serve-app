<?php

namespace App\Repositories;

use App\Models\BuildingPersonnel;
use App\Models\Invoice;
use App\Models\StaffAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuildingPersonnelRepository
{
    public function getList($id, $perPage = '', $keyword = null, $position = null, $status = null)
    {
        $query = BuildingPersonnel::where('building_id', $id);

        if (!empty($keyword)) {
            $query->where('personnel_name', 'LIKE', "%$keyword%");
        }
        if (!is_null($position)) {
            $query->where('position', $position);
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        $query->orderBy('created_at', 'desc');
        $buildingPersonnels = $query->with('building')
            ->paginate($perPage);

        return $buildingPersonnels;
    }

    public function create(array $request)
    {
        $activeStatus = 0;
        $buildingPersonnel = BuildingPersonnel::create([
            'building_id' => $request['building_id'],
            'personnel_name' => $request['personnel_name'],
            'personnel_phone' => $request['personnel_phone'],
            'personnel_address' => $request['personnel_address'],
            'position' => $request['position'],
            'monthly_salary' => $request['monthly_salary'],
            'status' => $activeStatus,
        ]);
        return $buildingPersonnel;
    }
}
