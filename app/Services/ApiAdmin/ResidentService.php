<?php
namespace App\Services\ApiAdmin;

use App\Repositories\ResidentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ResidentService
{
    public function __construct(
        public ResidentRepository $residentRepository,
    ) {
    }

    public function getListResident($request, $id)
    {
        return $this->residentRepository->getListResident(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->status,
        );
    }

    public function create(array $request)
    {
        return $this->residentRepository->create($request);
    }

    public function edit($id)
    {
        return $this->residentRepository->edit($id);
    }

    public function update(array $request, int $id)
    {
        return $this->residentRepository->update($request, $id);
    }

    public function addResidentToApartment(array $request, int $id)
    {
        return $this->residentRepository->addResidentToApartment($request, $id);
    }

    public function deleteResidentToApartment(array $request, int $id)
    {
        return $this->residentRepository->deleteResidentToApartment($request, $id);
    }
}
