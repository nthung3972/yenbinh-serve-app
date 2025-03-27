<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiAdmin\InvoiceService;
use App\Helper\Response;

class InvoiceController extends Controller
{
    public function __construct(
        public InvoiceService $invoiceService,
    ) {}

    public function getListInvoice(Request $request, $id)
    {
        try {
            $invoices = $this->invoiceService->getInvoicesByBuilding($request, $id);
            return Response::data(['data' => $invoices]);
        } catch (\Throwable $th) {
            return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
        }
    }
}
