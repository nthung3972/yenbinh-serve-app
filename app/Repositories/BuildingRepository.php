<?php

namespace App\Repositories;

use App\Models\Building;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class BuildingRepository
{
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
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->startOfMonth()->subMonth()->format('Y-m');

        // Tổng số hóa đơn của tháng hiện tại & tháng trước
        $totalInvoicesCurrent = Invoice::where('building_id', $building_id)
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$currentMonth])
            ->count();

        $totalInvoicesLast = Invoice::where('building_id', $building_id)
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$lastMonth])
            ->count();

        // Số hóa đơn đã thanh toán của tháng hiện tại & tháng trước
        $paidInvoicesCurrent = Invoice::where('building_id', $building_id)
            ->where('status', 1)
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$currentMonth])
            ->count();

        $paidInvoicesLast = Invoice::where('building_id', $building_id)
            ->where('status', 1)
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$lastMonth])
            ->count();

        // Tính tỷ lệ thu phí của tháng hiện tại & tháng trước
        $collectionRateCurrent = $totalInvoicesCurrent > 0
            ? round(($paidInvoicesCurrent / $totalInvoicesCurrent) * 100, 2)
            : 0;

        $collectionRateLast = $totalInvoicesLast > 0
            ? round(($paidInvoicesLast / $totalInvoicesLast) * 100, 2)
            : 0;

        // Tính phần trăm thay đổi so với tháng trước
        if ($collectionRateLast > 0) {
            $collectionRateChange = round((($collectionRateCurrent - $collectionRateLast) / $collectionRateLast) * 100, 2);
        } else {
            $collectionRateChange = $collectionRateCurrent > 0 ? 100 : 0;
        }

        // Tỷ lệ thu phí (tránh chia cho 0)
        $collectionRate = $totalInvoicesCurrent > 0 ? round(($paidInvoicesCurrent / $totalInvoicesCurrent) * 100, 2) : 0;

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
        $buildings->transform(function ($building) use ($collectionRate, $collectionRateChange) {

            $building->collectionRate = $collectionRate;

            $building->collectionRateChange = $collectionRateChange;

            // Tỷ lệ sử dụng căn hộ (Occupied Apartments / Total Apartments)
            $building->occupancyRate = $building->apartments_count > 0
                ? round(($building->occupied_apartments / $building->apartments_count) * 100, 2)
                : 0;

            return $building;
        });

        return $buildings;
    }

    public function statsAllBuildings()
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->startOfMonth()->subMonth()->format('Y-m');

        // Lấy danh sách tất cả các tòa nhà + số lượng căn hộ + số lượng cư dân
        $buildings = Building::withCount('apartments')
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

        // Tính toán cho từng tòa nhà
        $buildings->transform(function ($building) use ($currentMonth, $lastMonth) {

            // Tổng số hóa đơn tháng hiện tại & tháng trước
            $totalInvoicesCurrent = Invoice::where('building_id', $building->building_id)
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$currentMonth])
                ->count();

            $totalInvoicesLast = Invoice::where('building_id', $building->building_id)
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$lastMonth])
                ->count();

            // Số hóa đơn đã thanh toán tháng hiện tại & tháng trước
            $paidInvoicesCurrent = Invoice::where('building_id', $building->building_id)
                ->where('status', 1)
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$currentMonth])
                ->count();

            $paidInvoicesLast = Invoice::where('building_id', $building->building_id)
                ->where('status', 1)
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$lastMonth])
                ->count();

            // Tính tỷ lệ thu phí của từng tòa nhà
            $building->collectionRate = $totalInvoicesCurrent > 0
                ? round(($paidInvoicesCurrent / $totalInvoicesCurrent) * 100, 2)
                : 0;

            $collectionRateLast = $totalInvoicesLast > 0
                ? round(($paidInvoicesLast / $totalInvoicesLast) * 100, 2)
                : 0;

            // Tính phần trăm thay đổi so với tháng trước
            if ($collectionRateLast > 0) {
                $building->collectionRateChange = round((($building->collectionRate - $collectionRateLast) / $collectionRateLast) * 100, 2);
            } else {
                $building->collectionRateChange = $building->collectionRate > 0 ? 100 : 0;
            }

            // Tỷ lệ sử dụng căn hộ (Occupied Apartments / Total Apartments)
            $building->occupancyRate = $building->apartments_count > 0
                ? round(($building->occupied_apartments / $building->apartments_count) * 100, 2)
                : 0;

            return $building;
        });

        return $buildings;
    }
}
