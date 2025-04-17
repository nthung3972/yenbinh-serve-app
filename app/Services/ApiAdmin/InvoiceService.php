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
        // dd($request->invoice_date_from);
        return $this->invoiceRepository->getInvoicesByBuilding(
            $id, 
            $request->per_page ?? config('constant.paginate'),
            $request->keyword,
            $request->status,
            $request->invoice_date_from,
            $request->invoice_date_to,
        );
    }

    public function create(array $request)
    {
        return $this->invoiceRepository->create($request);
    }

    public function show(int $id)
    {
        return $this->invoiceRepository->show($id);
    }

    public function update(array $request, int $id)
    {
        return $this->invoiceRepository->update($request, $id);
    }
}
