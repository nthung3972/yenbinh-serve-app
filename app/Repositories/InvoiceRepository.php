<?php

namespace App\Repositories;

use App\Models\Apartment;
use App\Models\Resident;
use App\Models\ApartmentResident;
use App\Models\Invoice;
use Carbon\Carbon;


class InvoiceRepository
{
    public function getInvoicesByBuilding($building_id, $perPage = '', $keyword = null)
    {
        $query = Invoice::where('building_id', $building_id);
        if (!empty($keyword)) {
            $query->where('apartment_number', 'LIKE', "%$keyword%");
        }
        $query->orderBy('invoice_date', 'desc');
        $apartments = $query->with('apartment')
            ->paginate($perPage);

        return $apartments;
    }
}
