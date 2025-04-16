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

    public function getListByBuilding(int $id, $request)
    {
        return $this->apartmentRepository->getListByBuilding(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->apartment_type,
            $request->status
        );
    }

    public function create(array $request)
    {
        return $this->apartmentRepository->create($request);
    }

    public function addMultipleResidents(array $request, int $id)
    {
        return $this->apartmentRepository->addMultipleResidents($request, $id);
    }

    public function getApartmentDetail(int $id)
    {
        return $this->apartmentRepository->getApartmentDetail($id);
    }

    public function update(array $request, int $id)
    {
        return $this->apartmentRepository->update($request, $id);
    }


}