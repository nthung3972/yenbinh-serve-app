<?php

namespace App\Services\ApiAdmin;

use App\Repositories\VehicleRepository;
use App\Repositories\ApartmentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    public function __construct(
        public VehicleRepository $vehicleRepository,
        public ApartmentRepository $apartmentRepository,
    ) {}

    public function getListVehicle($request, $id)
    {
        return $this->vehicleRepository->getListVehicle(
            $id,
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->vehicle_type
        );
    }

    public function create(array $request)
    {
        foreach ($request as $index => &$vehicle) {
            $apartment = $this->apartmentRepository->getApartmentByNumber($vehicle['apartment_number']);

            if (!$apartment) {
                throw new \Exception("{$index}.apartment_number:Số căn hộ {$vehicle['apartment_number']} không tồn tại!", 422);
            }
            $vehicle['apartment_id'] = $apartment->apartment_id;

            if (!empty($vehicle['parking_slot'])) {
                $parkingExists = $this->vehicleRepository->checkVehicleSlot($vehicle['parking_slot']);
                if ($parkingExists) {
                    throw new \Exception("{$index}.parking_slot: Vị trí {$vehicle['parking_slot']} đã có xe đỗ!", 422);
                }
            }

            if ($vehicle['status'] === config('constant.vehicle_status.INACTVE')) {
                throw new \Exception("{$index}.status: Trạng thái xe phải đang hoạt động!", 422);
            }
        }
        unset($vehicle);

        return $this->vehicleRepository->create($request);
    
    }

    public function edit(int $id)
    {

        $vehicle = $this->vehicleRepository->edit($id);
        if (!$vehicle) {
            throw new \Exception("Xe không tồn tại!", 422);
        }
        return $vehicle;
    }

    public function update(array $request, int $id)
    {
        $apartment = $this->apartmentRepository->getApartmentByNumber($request['apartment_number']);
        if (!$apartment) {
            throw new \Exception("Số căn hộ {$request['apartment_number']} không tồn tại!", 422);
        }
        $request['apartment_id'] = $apartment->apartment_id;

        if (!empty($request['parking_slot'])) {
            $parkingExists = $this->vehicleRepository->checkVehicleSlot($request['parking_slot']);
            if ($parkingExists) {
                throw new \Exception("Vị trí {$request['parking_slot']} đã có xe đỗ!", 422);
            }
        }
        return $this->vehicleRepository->update($request, $id);
    }
}
