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
            $request->keyword
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
}
