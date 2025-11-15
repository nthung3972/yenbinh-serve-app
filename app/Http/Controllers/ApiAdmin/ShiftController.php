<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiAdmin\ShiftService;
use App\Helper\Response;
use App\Http\Requests\ShiftRequest\CreateShiftRequest;
use App\Http\Requests\ShiftRequest\UpdateShiftRequest;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    protected $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function getShifts(Request $request)
    {
        try {
            $shifts = $this->shiftService->getShifts($request);
            return Response::data(['data' => $shifts]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function createShift(CreateShiftRequest $request)
    {
        try {
            $shift = $this->shiftService->createShift($request->only([
                'building_id',
                'name',
                'description',
                'start_time',
                'end_time',
                'type'
            ]));

            return Response::data(['data' => $shift]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function getShiftById($id)
    {
        try {
            $shift = $this->shiftService->getShiftById($id);
            return Response::data(['data' => $shift]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function updateShift(UpdateShiftRequest $request, $id)
    {
       try {
            $update = $this->shiftService->updateShift($request->only([
                'building_id',
                'name',
                'description',
                'start_time',
                'end_time',
                'type'
            ]), $id);
            return Response::data(['data' => $update]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}