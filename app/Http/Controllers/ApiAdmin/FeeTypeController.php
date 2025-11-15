<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use Illuminate\Http\Request;
use App\Helper\Response;
use App\Services\ApiAdmin\FeeTypeService;
use App\Http\Requests\FeeTypeRequest\CreateFeeTypeRequest;
use App\Http\Requests\FeeTypeRequest\UpdateFeeTypeRequest;

class FeeTypeController extends Controller
{
    public function __construct( 
        public FeeTypeService $feeTypeService
    ){}

    public function getFlexibleFee(Request $request)
    {
        $fee = FeeType::where('is_fixed', false)->get();
        return response()->json($fee);
    }

    public function index()
    {
        try {
            $feeTypes = $this->feeTypeService->getFeeTypes();
            return Response::data(['data' => $feeTypes]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function store(CreateFeeTypeRequest $request)
    {
        try {
            $typeFee = $this->feeTypeService->createFeeType($request->only(
                'code', 'name', 'description', 'is_active'
            ));
            return Response::data(['data' => $typeFee]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function update(UpdateFeeTypeRequest $request, $id)
    {
        try {
            $typeFee = $this->feeTypeService->updateFeeType($id, $request->only(
                'code', 'name', 'description', 'is_active'
            ));
            return Response::data(['data' => $typeFee]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function getFeeTypeCode()
    {
        try {
            $code = $this->feeTypeService->getFeeTypeCode();
            return Response::data(['data' => ['code' => $code]]);
        } catch (\Exception $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
