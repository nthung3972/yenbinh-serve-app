<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiAdmin\ResidentService;
use App\Services\ApiAdmin\BuildingService;
use App\Helper\Response;
use Illuminate\Http\Request;
use App\Http\Requests\ResidentRequest\CreateResidentRequest;
use App\Http\Requests\ResidentRequest\AddResidentToApartment;
use App\Http\Requests\ResidentRequest\DeleteResidentToApartment;
use App\Http\Requests\ResidentRequest\UpdateResidentRequest;
use Illuminate\Support\Facades\DB;

class ResidentController extends Controller
{
    public function __construct(
        public ResidentService $residentService,
        public BuildingService $buildingService,
    ) {}

    public function getListResident(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $residents = $this->residentService->getListResident($request, $id);
            }
            $residents = $this->residentService->getListResident($request, $id);
            return Response::data(['data' => $residents]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function create(CreateResidentRequest $request)
    {
        try {
            DB::beginTransaction();
            $resident = $this->residentService->create($request->all());
            DB::commit();
            return Response::data(['data' => $resident]);
        } catch (\Throwable $th) {
            DB::rollback();
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

    public function update(UpdateResidentRequest $request, $id)
    {
        try {
            $update = $this->residentService->update($request->only('resident_id', 'full_name', 'id_card_number', 'date_of_birth', 'gender', 'phone_number', 'email'), $id);
            return Response::data(['data' => $update]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function addResidentToApartment(AddResidentToApartment $request, $id)
    {
        try {
            $addResidentToApartment = $this->residentService->addResidentToApartment($request->only('resident_id', 'apartment_number', 'role_in_apartment'), $id);
            return Response::data(['data' => $addResidentToApartment]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function deleteResidentToApartment(DeleteResidentToApartment $request, $id) {
        try {
            $addResidentToApartment = $this->residentService->deleteResidentToApartment($request->only('resident_id', 'apartment_id'), $id);
            return Response::data(['data' => $addResidentToApartment]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
