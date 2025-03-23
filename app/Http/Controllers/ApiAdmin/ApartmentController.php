<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApartmentResident;
use App\Models\Resident;
use App\Services\ApiAdmin\ApartmentService;
use App\Helper\Response;
use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Http\Requests\ApartmentRequest\AddMultipleResidentRequest;
use Illuminate\Support\Carbon;

class ApartmentController extends Controller
{
    public function __construct(
        public ApartmentService $apartmentService,
    ) {
    }

    public function getListByBuilding(Request $request, $id)
    {
        try {
            $apartmentList = $this->apartmentService->getListByBuilding($id, $request);
            return Response::data(['data' => $apartmentList]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $create = $this->apartmentService->create($request->all());
            return Response::data(['data' => $create]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function addMultipleResidents(AddMultipleResidentRequest $request, $id)
    {
        $createdResidents = [];

        foreach ($request->residents as $residentData) {
            $residentData['date_of_birth'] = Carbon::createFromFormat('m/d/Y', $residentData['date_of_birth'])->format('Y-m-d');
            $residentData['registration_date'] = Carbon::createFromFormat('m/d/Y', $residentData['registration_date'])->format('Y-m-d');
            $resident = Resident::create($residentData);

            ApartmentResident::create([
                'apartment_id' => $id,
                'resident_id' => $resident->resident_id,
                'relationship' => $residentData['relationship'],
                'move_in_date' => Carbon::createFromFormat('m/d/Y', $residentData['move_in_date'])->format('Y-m-d'),
                'move_out_date' => Carbon::createFromFormat('m/d/Y', $residentData['move_out_date'])->format('Y-m-d'),
                'is_primary_resident' => $residentData['is_primary_resident']
            ]);

            $createdResidents[] = $resident;
        }

        return response()->json([
            'message' => 'Đã thêm tất cả cư dân thành công',
            'residents' => $createdResidents
        ], 201);
        // try {         
        //     $create = $this->apartmentService->addMultipleResidents($request->residents , $id);
        //     return Response::data(['data' => $create]);
        // } catch (\Throwable $th) {
        //     return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        // }
    }
}
