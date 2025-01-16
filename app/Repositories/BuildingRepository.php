<?php

namespace App\Repositories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BuildingRepository
{
    public function getListBuilding(array $request)
    {
        $builder = Building::query();
        if(!empty($request)) {
            foreach ($request as $key => $value) {
                dump($key, $value);
                if ($value === null || $value === '') {
                    continue;
                }
                switch ($key) {
                    case 'name':
                        $builder->where('name', 'like', '%' . $value . '%');
                        break;
                }
            }
        }
        return $builder->paginate(config('constant.paginate'));
    }

    public function createBuilding(array $request)
    {
        return Building::create($request);
    }

    public function getBuildingByID(int $id)
    {
        return Building::findOrFail($id);
    }

    public function updateBuilding(int $id, array $request)
    {
        return Building::where('building_id', $id)->update($request);
    }
}