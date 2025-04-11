<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\InvoiceService;
use App\Services\ApiAdmin\BuildingService;
use App\Helper\Response;
use App\Http\Requests\InvoiceRequest\CreateInvoiceRequest;
use App\Http\Requests\InvoiceRequest\UpdateInvoiceRequest;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        public InvoiceService $invoiceService,
        public BuildingService $buildingService,
    ) {}

    public function getListInvoice(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if ($user->role === 'staff') {
                $isAssigned = $this->buildingService->isAssigned($user, $id);
                if (!$isAssigned) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $invoices = $this->invoiceService->getInvoicesByBuilding($request, $id);
            }
            $invoices = $this->invoiceService->getInvoicesByBuilding($request, $id);
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function create(CreateInvoiceRequest $request)
    {
        try {
            DB::beginTransaction();
            $invoice = $this->invoiceService->create($request->all());
            DB::commit();
            return Response::data(['data' => $invoice]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $invoices = $this->invoiceService->show($id);
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $th->getMessage(),
            ], 500);
            // return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }

    public function update(UpdateInvoiceRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $invoices = $this->invoiceService->update($request->all(), $id);
            DB::commit();
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            DB::rollback();
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
