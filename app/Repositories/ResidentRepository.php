<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\Building;
use App\Models\ApartmentResident;
use Carbon\Carbon;


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
        $registrationDate = $request['registration_date'];
        $registrationStatus = $request['registration_status'];
        $residentId = $resident->resident_id;
        if($resident && $residentId) {
            foreach ($request['apartments'] as $residentData) {
                $apartment = Apartment::where('apartment_number', $residentData['apartment_number'])->first();
                if ($apartment) {
                    ApartmentResident::create([
                        'apartment_id' => $apartment->apartment_id,
                        'resident_id' => $residentId,
                        'role_in_apartment' => $residentData['role_in_apartment'],
                        'registration_date' =>  $registrationDate,
                        'registration_status' => $registrationStatus
                    ]);
                }
            }
        }
        return $resident;
    }

    public function edit($id)
    {
        $resident = Resident::where('resident_id', $id)
            ->with('apartments')->get();
        return $resident;
    }
}