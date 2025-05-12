<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApartmentResident;
use App\Models\Resident;
use App\Services\ApiAdmin\ApartmentService;
use App\Services\ApiAdmin\BuildingService;
use App\Helper\Response;
use Illuminate\Http\Request;
use App\Http\Requests\ApartmentRequest\CreateApartmentRequest;
use App\Http\Requests\ApartmentRequest\UpdateApartmentStatusRequest;
use App\Models\Apartment;
use Illuminate\Support\Carbon;

class ApartmentController extends Controller
{
    public function __construct(
        public ApartmentService $apartmentService,
        public BuildingService $buildingService,
    ) {}

    public function getListByBuilding(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $apartmentList = $this->apartmentService->getListByBuilding($id, $request);
            }
            $apartmentList = $this->apartmentService->getListByBuilding($id, $request);
            return Response::data(['data' => $apartmentList]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function create(CreateApartmentRequest $request)
    {
        try {
            $create = $this->apartmentService->create($request->all());
            return Response::data(['data' => $create]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function edit($id) {
        try {
            $apartment = $this->apartmentService->getApartmentDetail($id);
            return Response::data($apartment);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function update(UpdateApartmentStatusRequest $request, $id)
    {
        try {
            $update = $this->apartmentService->update($request->only(['apartment_number', 'area', 'building_id', 'floor_number', 'ownership_type', 'apartment_type', 'notes']), $id);
            return Response::data(['data' => $update]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function getApartmentCode($id) 
    {
        try {
            $apartments =  Apartment::select('apartment_id', 'apartment_number')->where('building_id', $id)->get();
            return Response::data(['data' => $apartments]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function getResidentsByApartment($code)
    {
        try {
            $apartment = Apartment::where('apartment_number', $code)->firstOrFail();
            $residents = $apartment->residents->map(function ($resident) {
                return [
                    'resident_id' => $resident->resident_id,
                    'full_name' => $resident->full_name,
                ];
            });
            return Response::data(['data' => $residents]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
