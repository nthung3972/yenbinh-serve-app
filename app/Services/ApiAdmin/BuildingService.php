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

    public function getListBuilding(array $request)
    {
        return $this->buildingRepository->getListBuilding($request);
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

    public function statsAllBuildings($request)
    {
        return $this->buildingRepository->statsAllBuildings($request);
    }

    public function statsBuildingById(int $id)
    {
        return $this->buildingRepository->statsBuildingById($id);
    }
}