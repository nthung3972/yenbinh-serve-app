<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Services\ApiAdmin\BuildingService;
use App\Helper\Response;
use App\Models\Building;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        public BuildingService $buildingService,
    ) {}

    public function statsAllBuildings()
    {   
        try {
            $user = auth()->user();
            if ($user) {
                $buildings = $this->buildingService->statsAllBuildings($user);
            }
            return Response::data(['data' => $buildings]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function statsBuildingById($id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $building = $this->buildingService->statsBuildingById($id);
            }
            $building = $this->buildingService->statsBuildingById($id);
            return Response::data(['data' => $building]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
