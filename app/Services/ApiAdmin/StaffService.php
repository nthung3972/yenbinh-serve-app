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

    public function getListStaff($request)
    {
        return $this->staffRepository->getListStaff(
            $request->per_page ?? config('constant.paginate'),
            $request->keyword
        );
    }

    public function createStaff($request)
    {
        $staff = $this->staffRepository->createStaff($request);
        return $staff;
    }

    public function getStaffByID($id)
    {
        $staff = $this->staffRepository->findById($id);
        if ($staff->role === 'admin') {
            throw new \Exception("Không thể xem thông tin Admin!", 403);
        }
        if (!$staff) {
            throw new \Exception("Mã nhân viên không tồn tại!", 422);
        }
        return $staff;
    }

    public function deleteStaff($id)
    {
        $staff = $this->staffRepository->findById($id);
        if (!$staff) {
            throw new \Exception("Mã nhân viên không tồn tại!", 422);
        }
        if ($staff->role === 'admin') {
            throw new \Exception("Không thể xóa Admin!", 403);
        }
        $deleted = $this->staffRepository->deleteStaff($id);
    }
}
