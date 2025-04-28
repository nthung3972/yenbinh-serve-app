<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\StaffService;
use App\Services\ApiAdmin\BuildingService;
use Illuminate\Support\Facades\DB;
use App\Helper\Response;
use App\Http\Requests\UserRequest\CreateStaffRequest;

class StaffController extends Controller
{
    public function __construct(
        public StaffService $staffService,
    ) {}

    public function getListStaff(Request $request)
    {
        try {
            $staffs = $this->staffService->getListStaff($request);
            return Response::data(['data' => $staffs]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function createStaff(CreateStaffRequest $request)
    {
        try {
            DB::beginTransaction();
            $staff = $this->staffService->createStaff($request->all());
            DB::commit();
            return Response::data(['data' => $staff]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function getStaffDetail($id)
    {
        try {
            $staff = $this->staffService->getStaffByID($id);
            return Response::data(['data' => $staff]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function deleteStaff($id)
    {
        try {
            DB::beginTransaction();
            $deleted = $this->staffService->deleteStaff($id);
            DB::commit();
            return Response::data(['data' => $deleted]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
