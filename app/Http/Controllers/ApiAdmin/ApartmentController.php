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
            $update = $this->apartmentService->update($request->only(['apartment_number', 'area', 'building_id', 'floor_number', 'ownership_type']), $id);
            return Response::data(['data' => $update]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function addMultipleResidents(AddMultipleResidentRequest $request, $id)
    {
        // try {         
        //     $create = $this->apartmentService->addMultipleResidents($request->residents , $id);
        //     return Response::data(['data' => $create]);
        // } catch (\Throwable $th) {
        //     return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        // }


        $createdResidents = [];

        foreach ($request->all() as $residentData) {
            $resident = Resident::create($residentData);

            ApartmentResident::create([
                'apartment_id' => $id,
                'resident_id' => $resident->resident_id,
                'role_in_apartment' => $residentData['resident_type'],
                'registration_date' => $residentData['registration_date'],
                'registration_status' => $residentData['registration_status']
            ]);

            $createdResidents[] = $resident;
        }

        return response()->json([
            'message' => 'Đã thêm tất cả cư dân thành công',
            'residents' => $createdResidents
        ], 201);
    }
}
