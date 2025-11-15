<?php

namespace App\Services\ApiAdmin;

use App\Repositories\BuildingFeeRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class BuildingFeeService
{
    public function __construct(
        public BuildingFeeRepository $buildingFeeRepository,
    ) {}

    public function isAssigned($user, $buildingId) 
    {
        return $this->buildingFeeRepository->isAssigned($user, $buildingId);
    }

    public function feesByBuilding($request, $id)
    {
        return $this->buildingFeeRepository->feesByBuilding(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->fee_types_id
        );
    }

    public function show($id)
    {
        $buildingFee = $this->buildingFeeRepository->show($id);
        if (!$buildingFee) {
            throw new \Exception("Phí tòa nhà không tồn tại!", 422);
        }
        return $buildingFee;
    }

    public function createBuildingFee($request)
    {
        $buildingFee = $this->buildingFeeRepository->createBuildingFee($request);
        return $buildingFee;
    }

    public function updateBuildingFee($id, $request)
    {
        $buildingFee = $this->buildingFeeRepository->updateBuildingFee($id, $request);
        return $buildingFee;
    }

    public function feeManagementByBuilding($id)
    {
        $feeManagement = $this->buildingFeeRepository->feeManagementByBuilding($id);
        return $feeManagement;
    }
}
