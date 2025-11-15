<?php

namespace App\Services\ApiAdmin;

use App\Repositories\FeeSubtypeRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class FeeSubtypeService
{
    public function __construct(
        public FeeSubtypeRepository $feeSubtypeRepository,
    ) {}

    public function createFeeSubtype($request)
    {
        $feeSubtype = $this->feeSubtypeRepository->createFeeSubtype($request);
        return $feeSubtype;
    }

    public function updateFeeSubtype($id, $request)
    {
        $feeSubtype = $this->feeSubtypeRepository->updateFeeSubtype($id, $request);
        return $feeSubtype;
    }

    public function getByFeeTypeId($id)
    {
        $feeSubtypes = $this->feeSubtypeRepository->getByFeeTypeId($id);
        return $feeSubtypes;
    }
}
