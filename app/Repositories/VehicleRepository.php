<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Carbon\Carbon;


class VehicleRepository
{
    public function getListVehicle($building_id, $perPage = '', $keyword = null, $vehicle_type = null)
    {
        $query = Vehicle::select('vehicles.*', 'apartments.apartment_number')
        ->join('apartments', 'vehicles.apartment_id', '=', 'apartments.apartment_id')
        ->where('vehicles.building_id', $buildingId);

        // Lọc theo từ khóa biển số xe hoặc số căn hộ
        if (!empty($keyword)) {
            $query->where(function ($q) use ($request) {
                $q->where('vehicles.license_plate', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('apartments.apartment_number', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if (!empty($vehicle_type)) {
            $query->where('vehicle_type', 'LIKE', "%$vehicle_type%");
        }

        // $query->orderBy('created_at', 'desc');
        // dd($query->toSql(), $query->getBindings());

        $vehicles= $query->with('resident')
            ->paginate($perPage);
        // dd($vehicles->toArray());

        return $vehicles;
    }
}
