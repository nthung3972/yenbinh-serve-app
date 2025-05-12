<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\ApartmentResident;
use Carbon\Carbon;


class ApartmentRepository
{
    public function getListByBuilding($id, $perPage = '', $keyword = null, $apartment_type = null, $status = null)
    {
        $query = Apartment::with('updatedBy')->where('building_id', $id);

        if (!empty($keyword)) {
            $query->where('apartment_number', 'LIKE', "%$keyword%");
        }
        if (!is_null($apartment_type)) {
            $query->where('apartment_type', $apartment_type);
        }

        if (!empty($status)) {
            if ($status === 'occupied') {
                $query->whereHas('currentResidents'); 
            } elseif ($status === 'vacant') {
                $query->whereDoesntHave('currentResidents');
            }
        }

        $query->orderBy('created_at', 'desc');
        $apartments = $query->with('currentResidents')
            ->paginate($perPage);

        return $apartments;
    }

    public function create(array $request)
    {
        $user = auth()->user();
        $request['updated_by'] = $user->id;
        $apartment = Apartment::create($request);
        return $apartment;
    }

    public function addMultipleResidents(array $request, int $id)
    {
        // $createdResidents = [];
        // foreach ($request as $residentData) {
        //     $resident = Resident::create($residentData);

            // ApartmentResident::create([
            //     'apartment_id' => $id,
            //     'resident_id' => $resident->id,
            // ]);

        //     $createdResidents[] = $resident;
        // }

        // return response()->json([
        //     'message' => 'Đã thêm tất cả cư dân thành công',
        //     'residents' => $createdResidents
        // ], 201);
    }

    public function getApartmentDetail(int $id)
    {
        $apartment = Apartment::with('residents')->where('apartment_id', $id)->get();
        return $apartment;
    }

    public function update(array $request, int $id)
    {
        $user = auth()->user();
        $request['updated_by'] = $user->id;
        $update = Apartment::where('apartment_id', $id)->update($request);
        return $update;
    }

    public function getApartmentByNumber($apartment_number)
    {
        $apartment = Apartment::where('apartment_number', $apartment_number)->first();
        return $apartment;
    }
}
