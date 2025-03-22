<?php

namespace App\Repositories;

use App\Models\Apartment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class ApartmentRepository
{
    public function getListByBuilding(int $id)
    {
        $apartments = Apartment::where('building_id', $id)
            ->with(['residents' => function ($query) {
                $query->where('is_owner', 1);
            }])
            ->get();
        return $apartments;
    }
}
