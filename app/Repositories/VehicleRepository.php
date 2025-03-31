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
        ->where('vehicles.building_id', $building_id);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('vehicles.license_plate', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('apartments.apartment_number', 'LIKE', '%' . $keyword . '%');
            });
        }

        if (!empty($vehicle_type)) {
            $query->where('vehicle_type', 'LIKE', "%$vehicle_type%");
        }

        $query->orderBy('created_at', 'desc');
        // dd($query->toSql(), $query->getBindings());

        $vehicles = $query->with('apartment')
            ->paginate($perPage);

        return $vehicles;
    }

    public function checkVehicleSlot($slot) {
        $checkSlot = Vehicle::where('parking_slot', $slot)->exists();
        return $checkSlot;
    }

    public function create(array $request) {
        foreach($request as $vehicle) {
            Vehicle::create([
                'license_plate' => $vehicle['license_plate'],
                'vehicle_type' => $vehicle['vehicle_type'],
                'parking_slot' => $vehicle['parking_slot'],
                'status' => $vehicle['status'],
                'building_id' => $vehicle['building_id'],
                'apartment_id' => $vehicle['apartment_id'],
                'created_at' => $vehicle['created_at'],
            ]);
        }
    }

    public function edit(int $id)
    {
        $vehicle = Vehicle::with('apartment')->where('vehicle_id', $id)->first();
        return $vehicle;
    }

    public function update(array $request, int $id)
    {
        $update = Vehicle::where('vehicle_id', $id)->update([
            'license_plate' => $request['license_plate'],
            'vehicle_type' => $request['vehicle_type'],
            'parking_slot' => $request['parking_slot'],
            'status' => $request['status'],
            'building_id' => $request['building_id'],
            'apartment_id' => $request['apartment_id'],
            'created_at' => $request['created_at'],
        ]);
        return $update;
    }
}
