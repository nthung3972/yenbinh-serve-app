<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\Building;
use App\Models\ApartmentResident;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResidentRepository
{
    public function getListResident($building_id, $perPage = '', $keyword = null)
    {

    $query = Resident::whereHas('apartments', function ($q) use ($building_id) {
        $q->where('building_id', $building_id);
    });

    if (!empty($keyword)) {
        $query->where('full_name', 'LIKE', "%$keyword%");
    }

    return $query->paginate($perPage);
    }

    public function create(array $request)
    {
        $resident = Resident::create($request);
        return $resident;
    }

    public function edit($id)
    {
        $resident = Resident::where('resident_id', $id)
            ->with('apartments')->get();
        return $resident;
    }
}