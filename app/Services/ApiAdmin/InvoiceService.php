<?php
namespace App\Services\ApiAdmin;

use App\Repositories\InvoiceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    public function __construct(
        public InvoiceRepository $invoiceRepository,
    ) {
    }

    public function getInvoicesByBuilding($request, int $id)
    {
        return $this->invoiceRepository->getInvoicesByBuilding(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword
        );
    }
}
