<?php
namespace App\Services\ApiAdmin;

// use App\Models\Blog;
use App\Repositories\BuildingRepository;
// use App\Repositories\CategoryRepository;
// use App\Repositories\CommentRepository;
// use Exception;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BuildingService
{
    public function __construct(
        public BuildingRepository $buildingRepository,
    ) {
    }

    public function getListBuilding($request)
    {
        return $this->buildingRepository->getListBuilding(
            $request->per_page ?? config('constant.paginate'),
            $request->keyword
        );
    }

    public function createBuilding(array $request)
    {
        return $this->buildingRepository->createBuilding($request);
    }

    public function getBuildingByID(int $id)
    {
        return $this->buildingRepository->getBuildingByID($id);
    }

    public function updateBuilding(int $id, array $request)
    {
        return $this->buildingRepository->updateBuilding($id, $request);
    }

    public function statsAllBuildings($user)
    {
        return $this->buildingRepository->statsAllBuildings($user);
    }

    public function isAssigned($user, $id) 
    {
        return $this->buildingRepository->isAssigned($user, $id);
    }

    public function statsBuildingById(int $id)
    {
        return $this->buildingRepository->statsBuildingById($id);
    }
}