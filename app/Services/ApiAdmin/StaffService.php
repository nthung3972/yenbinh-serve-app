<?php
namespace App\Services\ApiAdmin;

use App\Repositories\StaffRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffService
{
    public function __construct(
        public StaffRepository $staffRepository,
    ) {
    }

    public function createStaff($request)
    {
        $staff = $this->staffRepository->createStaff($request);
        return $staff;
    }
}
