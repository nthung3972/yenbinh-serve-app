<?php
namespace App\Services\ApiAdmin;

// use App\Models\Blog;
use App\Repositories\BuildingPersonnelRepository;
// use App\Repositories\CategoryRepository;
// use App\Repositories\CommentRepository;
// use Exception;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BuildingPersonnelService
{
    public function __construct(
        public BuildingPersonnelRepository $buildingPesonnelRepository,
    ) {
    }

    public function getList($id, $request)
    {
        return $this->buildingPesonnelRepository->getList(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->position,
            $request->status
        );
    }

    public function create(array $request)
    {
        return $this->buildingPesonnelRepository->create($request);
    }

    public function edit($id)
    {
        return $this->buildingPesonnelRepository->edit($id);
    }

    public function update(int $id, array $request)
    {
        $findBuilding = $this->buildingPesonnelRepository->edit($id);
        if ($findBuilding) {
            return $this->buildingPesonnelRepository->update($id, $request);
        }
    }
}