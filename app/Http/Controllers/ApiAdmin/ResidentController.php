<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApartmentResident;
use App\Models\Resident;
use App\Services\ApiAdmin\ResidentService;
use App\Helper\Response;
use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Http\Requests\ApartmentRequest\AddMultipleResidentRequest;
use Illuminate\Support\Carbon;
use App\Models\Building;

class ResidentController extends Controller
{
    public function __construct(
        public ResidentService $residentService,
    ) {}

    public function getListResident(Request $request, $id)
    {
        try {
            $residents = $this->residentService->getListResident($request, $id);
            return Response::data(['data' => $residents]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $resident = $this->residentService->create($request->all());
            return Response::data(['data' => $resident]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $resident = $this->residentService->edit($id);
            return Response::data(['data' => $resident]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
