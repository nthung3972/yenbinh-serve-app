<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiAdmin\ApartmentService;
use App\Helper\Response;

class ApartmentController extends Controller
{
    public function __construct(
        public ApartmentService $apartmentService,
    ) {}

    public function getListByBuilding($id)
    {
        try {
            $apartmentList = $this->apartmentService->getListByBuilding($id);
            return Response::data(['data' => $apartmentList]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
