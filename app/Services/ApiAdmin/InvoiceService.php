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
            $request->invoice_date_from,
            $request->invoice_date_to,
        );
    }

    public function create(array $request)
    {
        $data = $this->checkInvoice($request);
        return $this->invoiceRepository->create($data);
    }

    protected function checkInvoice(array $request) 
    {
        $errors = [];
        $invoiceDate = Carbon::parse($request['invoice_date']);
        $year = $invoiceDate->year;
        $month = $invoiceDate->month;
        
        $checkInvoice = $this->invoiceRepository->existingInvoice($request, $year, $month);

        if ($checkInvoice) {
            throw new \Exception("Căn hộ này đã có hóa đơn của tháng {$month}/{$year}!", 422);
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        
        return $request;
    }

    public function show(int $id)
    {
        return $this->invoiceRepository->show($id);
    }

    public function update(array $request, int $id)
    {
        return $this->invoiceRepository->update($request, $id);
    }

    public function delete(int $id)
    {
        return $this->invoiceRepository->delete($id);
    }
}
