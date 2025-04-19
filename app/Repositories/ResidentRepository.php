<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\ApartmentResident;
use Carbon\Carbon;


class ResidentRepository
{
    public function getListResident($building_id, $perPage = '', $keyword = null)
    {
        $query = Resident::with('updatedBy')->whereHas('apartments', function ($q) use ($building_id) {
            $q->where('building_id', $building_id);
        });

        if (!empty($keyword)) {
            $query->where('full_name', 'LIKE', "%$keyword%");
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function create(array $request)
    {
        $user = auth()->user();
        $request['updated_by'] = $user->id;
        $resident = Resident::create($request);

        $registrationDate = Carbon::now()->format('Y-m-d');
        $registrationStatus = 0;
        $residentId = $resident->resident_id;

        if ($resident && $residentId) {
            foreach ($request['apartments'] as $residentData) {
                $apartment = Apartment::where('apartment_number', $residentData['apartment_number'])->first();
                if ($apartment) {
                    ApartmentResident::create([
                        'apartment_id' => $apartment->apartment_id,
                        'resident_id' => $residentId,
                        'role_in_apartment' => $residentData['role_in_apartment'],
                        'notes' => $residentData['notes'],
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

    public function update(array $request, int $id)
    {
        $user = auth()->user();
        $request['updated_by'] = $user->id;
        $updated = Resident::where('resident_id', $id)->update($request);
        return $updated;
    }

    public function addResidentToApartment(array $request, int $id)
    {
        $apartment = Apartment::where('apartment_number', $request['apartment_number'])->first();

        $registrationDate = Carbon::now()->format('Y-m-d');
        $registrationStatus = 0;

        $data = [
            'apartment_id' => $apartment->apartment_id,
            'resident_id' => $id,
            'role_in_apartment' => $request['role_in_apartment'],
            'registration_date' =>  $registrationDate,
            'registration_status' => $registrationStatus
        ];

        $addResidentToApartment = ApartmentResident::create($data);

        return $addResidentToApartment;
    }

    public function deleteResidentToApartment(array $request, int $id)
    {
        $deleted = ApartmentResident::where('resident_id', $id)
            ->where('apartment_id', $request['apartment_id'])
            ->delete();

        return $deleted;
    }
}
