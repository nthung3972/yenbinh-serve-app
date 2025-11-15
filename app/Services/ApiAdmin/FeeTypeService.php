<?php

namespace App\Services\ApiAdmin;

use App\Repositories\FeeTypeRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class FeeTypeService
{
    public function __construct(
        public FeeTypeRepository $feeTypeRepository,
    ) {}

    public function getFeeTypes()
    {
        return $this->feeTypeRepository->getFeeTypes();
    }

    public function createFeeType($request)
    {
        $feeType = $this->feeTypeRepository->createFeeType($request);
        return $feeType;
    }

    public function updateFeeType($id, $request)
    {
        $feeType = $this->feeTypeRepository->updateFeeType($id, $request);
        return $feeType;
    }

    public function getFeeTypeCode()
    {
        return $this->feeTypeRepository->getFeeTypeCode();
    }
}
