<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Carbon\Carbon;


class VehicleRepository
{
    public function getListVehicle($building_id, $perPage = '', $keyword = null, $vehicle_type = null, $status = null)
    {
        $query = Vehicle::with('updatedBy', 'vehicleType')->select('vehicles.*', 'apartments.apartment_number')
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

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        $query->orderBy('created_at', 'desc');
        // dd($query->toSql(), $query->getBindings());

        $vehicles = $query->with('apartment')
            ->paginate($perPage);

        return $vehicles;
    }

    public function findById($id)
    {
        $vehicle = Vehicle::where('vehicle_id', $id)->first();
        return $vehicle;
    }

    public function checkVehicleSlot($slot, $vehicleId = null) {
        $query = Vehicle::where('parking_slot', $slot);
    
        if ($vehicleId) {
            $query->where('vehicle_id', '!=', $vehicleId);
        }
        
        return $query->exists();
    }

    public function create(array $request) {

        $user = auth()->user();

        foreach($request as $vehicle) {
            Vehicle::create([
                'license_plate' => $vehicle['license_plate'],
                'vehicle_type_id' => $vehicle['vehicle_type_id'],
                'parking_slot' => $vehicle['parking_slot'],
                'vehicle_company' => $vehicle['vehicle_company'],
                'vehicle_model' => $vehicle['vehicle_model'],
                'vehicle_color' => $vehicle['vehicle_color'],
                'status' => $vehicle['status'],
                'building_id' => $vehicle['building_id'],
                'resident_id' => $vehicle['resident_id'],
                'apartment_id' => $vehicle['apartment_id'],
                'created_at' => $vehicle['created_at'],
                'updated_by' => $user->id,
            ]);
        }
    }

    public function edit(int $id)
    {
        $vehicle = Vehicle::with('apartment', 'resident')->where('vehicle_id', $id)->first();
        return $vehicle;
    }

    public function update(array $request, int $id)
    {
        $inactive_date = null;
        $user = auth()->user();

        if($request['status'] === 1) {
            $inactive_date = Carbon::now();
        }

        $update = Vehicle::where('vehicle_id', $id)->update([
            'license_plate' => $request['license_plate'],
            'vehicle_type_id' => $request['vehicle_type_id'],
            'vehicle_company' => $request['vehicle_company'],
            'vehicle_model' => $request['vehicle_model'],
            'vehicle_color' => $request['vehicle_color'],
            'parking_slot' => $request['parking_slot'],
            'status' => $request['status'],
            'building_id' => $request['building_id'],
            'apartment_id' => $request['apartment_id'],
            'created_at' => $request['created_at'],
            'updated_by' => $user->id,
            'inactive_date' => $inactive_date,
        ]);
        return $update;
    }

    public function delete($id)
    {
        $vehicle = Vehicle::where('vehicle_id', $id)->delete();
        return $vehicle;
    }
}
