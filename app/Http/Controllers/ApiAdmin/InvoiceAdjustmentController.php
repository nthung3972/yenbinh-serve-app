<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Helper\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\InvoiceAdjustmentService;
use App\Http\Requests\AdjustmentRequest\CreateAdjustmentRequest;

class InvoiceAdjustmentController extends Controller
{
    public function __construct(
        public InvoiceAdjustmentService $invoiceAdjustmentService
    ) {}

    public function createAdjustment(CreateAdjustmentRequest $request)
    {
        try {
            $adjustment = $this->invoiceAdjustmentService->createAdjustment($request->all());
            return Response::data(['path' => $adjustment]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function approveAdjustment($id)
    {
        try {
            $approveAdjustment = $this->invoiceAdjustmentService->approveAdjustment($id);
            return Response::data(['path' => $approveAdjustment]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
