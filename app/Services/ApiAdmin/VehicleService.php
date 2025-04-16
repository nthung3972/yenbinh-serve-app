<?php

namespace App\Services\ApiAdmin;

use App\Repositories\VehicleRepository;
use App\Repositories\ApartmentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;

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
            $request->vehicle_type,
            $request->status
        );
    }

    public function create(array $request)
    {
        $data = $this->validateVehicles($request);
        return $this->vehicleRepository->create($data);
    }

    public function update($vehicleData, $id)
    {
        $data = $this->validateSingleVehicle($vehicleData, $id);
        
        return $this->vehicleRepository->update($data, $id);
    }

    protected function validateVehicles(array $vehicles)
    {
        $errors = [];
        
        foreach ($vehicles as $index => $vehicle) {
            // Kiểm tra căn hộ
            $apartment = $this->apartmentRepository->getApartmentByNumber($vehicle['apartment_number']);
            if (!$apartment) {
                $errors["{$index}.apartment_number"] = ["Số căn hộ {$vehicle['apartment_number']} không tồn tại!"];
                continue; // Bỏ qua các kiểm tra khác nếu căn hộ không tồn tại
            }
            
            $vehicles[$index]['apartment_id'] = $apartment->apartment_id;
            
            // Kiểm tra vị trí đỗ xe
            if (!empty($vehicle['parking_slot'])) {
                $parkingExists = $this->vehicleRepository->checkVehicleSlot($vehicle['parking_slot']);
                if ($parkingExists) {
                    $errors["{$index}.parking_slot"] = ["Vị trí {$vehicle['parking_slot']} đã có xe đỗ!"];
                }
            }
            
            // Kiểm tra trạng thái
            if ($vehicle['status'] === config('constant.vehicle_status.INACTVE')) {
                $errors["{$index}.status"] = ["Trạng thái xe phải đang hoạt động!"];
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        
        return $vehicles;
    }

    public function edit(int $id)
    {

        $vehicle = $this->vehicleRepository->edit($id);
        if (!$vehicle) {
            throw new \Exception("Xe không tồn tại!", 422);
        }
        return $vehicle;
    }

    public function validateSingleVehicle(array $vehicle, int $id)
    {
        $apartment = $this->apartmentRepository->getApartmentByNumber($vehicle['apartment_number']);
        if (!$apartment) {
            throw ValidationException::forField(
                'apartment_number', 
                "Số căn hộ {$vehicle['apartment_number']} không tồn tại!"
            );
        }
        
        $vehicle['apartment_id'] = $apartment['apartment_id'];
        
        // Kiểm tra vị trí đỗ xe
        if (!empty($vehicle['parking_slot'])) {
            $parkingExists = $this->vehicleRepository->checkVehicleSlot($vehicle['parking_slot'], $id);
            if ($parkingExists) {
                throw ValidationException::forField(
                    'parking_slot', 
                    "Vị trí {$vehicle['parking_slot']} đã có xe đỗ!"
                );
            }
        }
        
        return $vehicle;
    }
}
