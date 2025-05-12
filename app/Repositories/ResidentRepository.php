<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\ApartmentResident;
use Carbon\Carbon;


class ResidentRepository
{
    public function getListResident($building_id, $perPage = '', $keyword = null, $status = null)
    {
        $query = Resident::whereHas('apartments', function ($q) use ($building_id, $status) {
            $q->where('building_id', $building_id);

            if ($status === 'active') {
                $q->whereNull('apartment_resident.move_out_date');
            } elseif ($status === 'inactive') {
                $q->whereNotNull('apartment_resident.move_out_date');
            }
        })->with([
            'updatedBy',
            'apartments' => function ($q) use ($building_id, $status) {
                $q->where('building_id', $building_id);

                if ($status === 'active') {
                    $q->whereNull('apartment_resident.move_out_date');
                } elseif ($status === 'inactive') {
                    $q->whereNotNull('apartment_resident.move_out_date');
                }
            }
        ]);

        if (!empty($keyword)) {
            $query->where('full_name', 'LIKE', "%$keyword%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
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
        $resident = Resident::with('currentApartments')->findOrFail($id);
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

        if (!$apartment) {
            throw new \Exception("Không tìm thấy căn hộ với số: " . $request['apartment_number']);
        }

        // Kiểm tra xem đã có bản ghi cư dân từng sống ở căn hộ chưa
        $existing = ApartmentResident::where('apartment_id', $apartment->apartment_id)
            ->where('resident_id', $id)
            ->first();

        $registrationDate = Carbon::now()->format('Y-m-d');
        $registrationStatus = 0;

        if ($existing) {
            // Dùng query builder để update theo điều kiện 2 cột
            ApartmentResident::where('apartment_id', $apartment->apartment_id)
                ->where('resident_id', $id)
                ->update([
                    'role_in_apartment' => $request['role_in_apartment'],
                    'registration_date' => $registrationDate,
                    'registration_status' => 0,
                    'move_out_date' => null,
                ]);

            return ApartmentResident::where('apartment_id', $apartment->apartment_id)
                ->where('resident_id', $id)
                ->first(); // Trả lại bản ghi đã update
        }

        // Nếu chưa từng sống trong căn hộ -> tạo mới
        $data = [
            'apartment_id' => $apartment->apartment_id,
            'resident_id' => $id,
            'role_in_apartment' => $request['role_in_apartment'],
            'registration_date' =>  $registrationDate,
            'registration_status' => 0
        ];

        return ApartmentResident::create($data);
    }

    public function deleteResidentToApartment(array $request, int $id)
    {
        $deleted = ApartmentResident::where('resident_id', $id)
            ->where('apartment_id', $request['apartment_id'])
            ->whereNull('move_out_date')
            ->update(['move_out_date' => Carbon::now(), 'registration_status' => 1]);

        return $deleted;
    }
}
