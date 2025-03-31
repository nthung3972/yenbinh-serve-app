<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\ApartmentResident;
use Carbon\Carbon;


class ApartmentRepository
{
    public function getListByBuilding($id, $perPage = '', $keyword = null)
    {
        $query = Apartment::where('building_id', $id);
        if (!empty($keyword)) {
            $query->where('apartment_number', 'LIKE', "%$keyword%");
        }
        $apartments = $query->with('residents')
            ->paginate($perPage);

        return $apartments;
    }

    public function create(array $request)
    {
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
        $apartment = Apartment::where('apartment_id', $id)->get();
        return $apartment;
    }

    public function update(array $request, int $id)
    {
        $update = Apartment::where('apartment_id', $id)->update($request);
        return $update;
    }

    public function getApartmentByNumber($apartment_number)
    {
        $apartment = Apartment::where('apartment_number', $apartment_number)->first();
        return $apartment;
    }
}
