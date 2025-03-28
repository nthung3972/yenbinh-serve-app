<?php

namespace App\Repositories;

use App\Models\Building;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class BuildingRepository
{
    public function overview($request)
    {
        $buildings = Building::withCount('apartments')
            ->withCount([
                'apartments as occupied_apartments_count' => function ($query) {
                    $query->where('status', 0);
                }
            ])
            ->withCount(['apartments as residents_count' => function ($query) {
                $query->join('apartment_resident', 'apartments.apartment_id', '=', 'apartment_resident.apartment_id');
            }])
            ->withCount([
                'invoices as total_invoices' => function ($query) {
                    $query->whereMonth('invoice_date', Carbon::now()->month)
                        ->whereYear('invoice_date', Carbon::now()->year);
                },
                'invoices as paid_invoices' => function ($query) {
                    $query->whereMonth('invoice_date', Carbon::now()->month)
                        ->whereYear('invoice_date', Carbon::now()->year)
                        ->where('status', 1);
                },
                'invoices as last_month_paid' => function ($query) {
                    $query->where('status', 1)->whereMonth('invoice_date', now()->subMonth()->month);
                },
                'invoices as last_month_total' => function ($query) {
                    $query->whereMonth('invoice_date', now()->subMonth()->month);
                }
            ])
            ->get();

        // Thêm tỷ lệ thu phí và tỷ lệ căn hộ có người ở
        $buildings->transform(function ($building) {
            $building->occupied_rate = $building->apartments_count > 0
                ? round(($building->occupied_apartments_count / $building->apartments_count) * 100, 2)
                : 0;

            $building->collection_rate = $building->total_invoices > 0
                ? round(($building->paid_invoices / $building->total_invoices) * 100, 2)
                : 0;

            $building->last_month_collection_rate = $building->last_month_total > 0
                ? round(($building->last_month_paid / $building->last_month_total) * 100, 2)
                : 0;

            $building->collection_rate_change = round(($building->collection_rate - $building->last_month_collection_rate), 2);

            return $building;
        });
        return $buildings;
    }

    public function getListBuilding(array $request)
    {
        $builder = Building::query();
        if (!empty($request)) {
            foreach ($request as $key => $value) {
                dump($key, $value);
                if ($value === null || $value === '') {
                    continue;
                }
                switch ($key) {
                    case 'name':
                        $builder->where('name', 'like', '%' . $value . '%');
                        break;
                }
            }
        }
        return $builder->paginate(config('constant.paginate'));
    }

    public function createBuilding(array $request)
    {
        return Building::create($request);
    }

    public function getBuildingByID(int $id)
    {
        return Building::findOrFail($id);
    }

    public function updateBuilding(int $id, array $request)
    {
        return Building::where('building_id', $id)->update($request);
    }

    public function statsBuildingById(int $building_id)
    {
        $totalInvoices = Invoice::where('building_id', $building_id)->count();

        $paidInvoices = Invoice::where('building_id', $building_id)
                        ->where('status', 1)
                        ->count();
    
        $unpaidInvoices = Invoice::where('building_id', $building_id)
                        ->where(function ($query) {
                            $query->where('status', 0)
                                  ->orWhere('status', 2);
                        })
                        ->count();
    
        // Tỷ lệ thu phí (tránh chia cho 0)
        $collectionRate = $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 2) : 0;
    
        // Lấy thông tin tòa nhà + số lượng căn hộ + số lượng cư dân
        $buildings = Building::where('building_id', $building_id)
            ->withCount('apartments')
            ->withCount([
                'apartments as occupied_apartments' => function ($query) {
                    $query->whereHas('residents'); // Căn hộ có cư dân
                }
            ])
            ->withCount([
                'apartments as empty_apartments' => function ($query) {
                    $query->whereDoesntHave('residents'); // Căn hộ trống
                }
            ])
            ->withCount([
                'apartments as residents_count' => function ($query) {
                    $query->join('apartment_resident', 'apartments.apartment_id', '=', 'apartment_resident.apartment_id');
                }
            ])
            ->get();
    
        // Thêm tỷ lệ thu phí & tỷ lệ sử dụng căn hộ vào kết quả
        $buildings->transform(function ($building) use ($collectionRate) {
            $building->collectionRate = $collectionRate;
    
            // Tỷ lệ sử dụng căn hộ (Occupied Apartments / Total Apartments)
            $building->occupancyRate = $building->apartments_count > 0
                ? round(($building->occupied_apartments / $building->apartments_count) * 100, 2)
                : 0;
    
            return $building;
        });
        
        return $buildings;
    }
}
