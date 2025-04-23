<?php

namespace App\Repositories;

use App\Models\Building;
use App\Models\Invoice;
use App\Models\StaffAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuildingRepository
{
    public function getListBuilding($perPage = '', $keyword = null)
    {
        $query = Building::with('staffs:id,name')->orderBy('created_at', 'desc');
        if (!empty($keyword)) {
            $query->where('buildings.name', 'LIKE', "%$keyword%");
        }
        $query->orderBy('created_at', 'desc');
        $buildings = $query->paginate($perPage);

        $buildings->getCollection()->transform(function ($building) {
            $building->staff_names = $building->staffs->pluck('name')->join(', ');
            return $building;
        });

        return $buildings;
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

    public function deleteBuilding(int $id)
    {
        return Building::where('building_id', $id)->delete();
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

    public function isAssigned($user, $id)
    {
        $isAssigned = StaffAssignment::where('staff_id', $user->id)
            ->where('building_id', $id)
            ->exists();
        return $isAssigned;
    }

    public function statsAllBuildings($user)
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->startOfMonth()->subMonth()->format('Y-m');

        // Tạo query builder cho buildings
        $buildingsQuery = Building::query();

        // Giới hạn tòa nhà theo quyền user
        if ($user->role !== 'admin') {
            $buildingsQuery->whereIn('building_id', function ($query) use ($user) {
                $query->select('building_id')
                    ->from('staff_assignments')
                    ->where('staff_id', $user->id);
            });
        }

        // Eager loading và tính toán số liệu căn hộ
        $buildings = $buildingsQuery->withCount([
            'apartments',
            'apartments as occupied_apartments' => function ($query) {
                $query->whereHas('residents');
            },
            'apartments as empty_apartments' => function ($query) {
                $query->whereDoesntHave('residents');
            }
        ])
            // Đếm tổng số cư dân
            ->withCount([
                'apartments as residents_count' => function ($query) {
                    $query->join('apartment_resident', 'apartments.apartment_id', '=', 'apartment_resident.apartment_id');
                }
            ])
            ->get();

        // Lấy thông tin hóa đơn từ database một lần cho tất cả các tòa nhà
        $buildingIds = $buildings->pluck('building_id')->toArray();

        // Lấy tất cả dữ liệu hóa đơn cần thiết trong một lần query
        $invoiceStats = DB::table('invoices')
            ->selectRaw(
                'building_id, 
                     SUM(CASE WHEN DATE_FORMAT(invoice_date, "%Y-%m") = ? THEN 1 ELSE 0 END) as total_current,
                     SUM(CASE WHEN DATE_FORMAT(invoice_date, "%Y-%m") = ? AND status = 1 THEN 1 ELSE 0 END) as paid_current,
                     SUM(CASE WHEN DATE_FORMAT(invoice_date, "%Y-%m") = ? THEN 1 ELSE 0 END) as total_last,
                     SUM(CASE WHEN DATE_FORMAT(invoice_date, "%Y-%m") = ? AND status = 1 THEN 1 ELSE 0 END) as paid_last',
                [$currentMonth, $currentMonth, $lastMonth, $lastMonth]
            )
            ->whereIn('building_id', $buildingIds)
            ->groupBy('building_id')
            ->get()
            ->keyBy('building_id');

        // Tính toán số liệu
        $buildings = $buildings->map(function ($building) use ($invoiceStats) {
            // Chuẩn hóa thành mảng để thêm thuộc tính mới
            $buildingData = $building->toArray();

            // Lấy thông tin hóa đơn từ dữ liệu đã tổng hợp
            $stats = $invoiceStats->get($building->building_id);

            $totalInvoicesCurrent = $stats->total_current ?? 0;
            $paidInvoicesCurrent = $stats->paid_current ?? 0;
            $totalInvoicesLast = $stats->total_last ?? 0;
            $paidInvoicesLast = $stats->paid_last ?? 0;

            // Tính tỷ lệ thu phí
            $buildingData['collection_rate'] = $totalInvoicesCurrent > 0
                ? round(($paidInvoicesCurrent / $totalInvoicesCurrent) * 100, 2)
                : 0;

            $collectionRateLast = $totalInvoicesLast > 0
                ? round(($paidInvoicesLast / $totalInvoicesLast) * 100, 2)
                : 0;

            $buildingData['collection_rate_change'] = $collectionRateLast > 0
                ? round((($buildingData['collection_rate'] - $collectionRateLast) / $collectionRateLast) * 100, 2)
                : ($buildingData['collection_rate'] > 0 ? 100 : 0);

            // Tỷ lệ sử dụng căn hộ
            $buildingData['occupancy_rate'] = $buildingData['apartments_count'] > 0
                ? round(($buildingData['occupied_apartments'] / $buildingData['apartments_count']) * 100, 2)
                : 0;

            return $buildingData;
        });

        return $buildings;
    }
}
