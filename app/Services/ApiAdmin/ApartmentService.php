<?php
namespace App\Services\ApiAdmin;

use App\Repositories\ApartmentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ApartmentService
{
    public function __construct(
        public ApartmentRepository $apartmentRepository,
    ) {
    }

    public function getListByBuilding(int $id)
    {
        return $this->apartmentRepository->getListByBuilding($id);
    }
}