<?php
namespace App\Services\ApiAdmin;

use App\Repositories\InvoiceRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exceptions\ValidationException;

class InvoiceService
{
    public function __construct(
        public InvoiceRepository $invoiceRepository,
    ) {
    }

    public function getInvoicesByBuilding($request, int $id)
    {
        // dd($request->invoice_date_from);
        return $this->invoiceRepository->getInvoicesByBuilding(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->status,
            $request->period,
        );
    }

    public function show(int $id)
    {
        return $this->invoiceRepository->show($id);
    }

    public function delete(array $id)
    {
        return $this->invoiceRepository->delete($id);
    }
}
