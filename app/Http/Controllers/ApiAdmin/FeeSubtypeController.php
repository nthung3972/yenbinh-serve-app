<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\FeeSubtypeService;
use App\Http\Requests\FeeSubtypeRequest\CreateFeeSubtypeRequest;
use App\Http\Requests\FeeSubtypeRequest\UpdateFeeSubtypeRequest;
use App\Helper\Response;

class FeeSubtypeController extends Controller
{
    public function __construct(
        public FeeSubtypeService $feeSubtypeService,
    ){}

    public function store(CreateFeeSubtypeRequest $request)
    {
        try {
            $feeSubtype = $this->feeSubtypeService->createFeeSubtype($request->only(
                'code', 'name', 'unit', 'note', 'fee_types_id'
            ));
            return Response::data(['data' => $feeSubtype]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function update(UpdateFeeSubtypeRequest $request, $id)
    {
        try {
            $update = $this->feeSubtypeService->updateFeeSubtype($id, $request->only(
                'code', 'name', 'unit', 'note', 'fee_types_id'
            ));
            return Response::data(['data' => $update]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function getByFeeTypeId($id)
    {
        try {   
            $feeSubtypes = $this->feeSubtypeService->getByFeeTypeId($id);
            return Response::data(['data' => $feeSubtypes]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
