<?php
namespace App\Services\ApiAdmin;

use App\Repositories\VehicleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleService
{
    public function __construct(
        public VehicleRepository $vehicleRepository,
    ) {
    }

    public function getListVehicle($request, $id)
    {
        return $this->vehicleRepository->getListVehicle(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->vehicle_type
        );
    }
}
