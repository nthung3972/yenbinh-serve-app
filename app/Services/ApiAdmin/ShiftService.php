<?php

namespace App\Services\ApiAdmin;

use App\Repositories\ShiftRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftService
{
    public function __construct(
        public ShiftRepository $shiftRepository,
    ) {}

    public function getShifts($request): LengthAwarePaginator
    {
        return $this->shiftRepository->getShifts(
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->buildingId,
        );
    }

    public function createShift($request)
    {
        $shift = $this->shiftRepository->createShift($request);
        return $shift;
    }

    public function getShiftById($id)
    {
        $shift = $this->shiftRepository->getShiftById($id);
        return $shift;
    }

    public function updateShift($request, $id)
    {
        $shift = $this->shiftRepository->getShiftById($id);
        
        if (!$shift) {
            throw new \Exception('Shift not found', 404);
        }

        $update = $this->shiftRepository->updateShift($request, $id);

        return $update;
    }
}
