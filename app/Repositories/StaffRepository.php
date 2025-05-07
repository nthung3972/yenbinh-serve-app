<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\StaffAssignment;
use Carbon\Carbon;


class StaffRepository
{
    public function getListStaff($perPage = '', $keyword = null)
    {
        $query = User::where('users.role', 'staff')
            ->select('users.*', 'buildings.name as building_name', 'staff_assignments.role as building_role')
            ->join('staff_assignments', 'users.id', '=', 'staff_assignments.staff_id')
            ->join('buildings', 'staff_assignments.building_id', '=', 'buildings.building_id');

        // dd($query->toSql(), $query->getBindings());
        if (!empty($keyword)) {
            $query->where('buildings.name', 'LIKE', "%$keyword%");
        }

        $query->orderBy('created_at', 'desc');

        $apartments = $query->paginate($perPage);


        return $apartments;
    }

    public function findById($id)
    {
        $user = User::select('id', 'name', 'email', 'avatar', 'gender', 'address', 'date_of_birth', 'phone_number')
            ->with([
                'staffAssignment' => function ($query) {
                    $query->select('staff_assignment_id', 'staff_id', 'building_id', 'role', 'assigned_tasks')
                        ->with([
                            'buildings' => function ($q) {
                                $q->select('building_id', 'name', 'address', 'floors', 'total_area', 'status', 'building_type');
                            }
                        ]);
                }
            ])
            ->where('id', $id)
            ->first();
        return $user;
    }

    public function createStaff($request)
    {
        $staff = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'address' => $request['address'],
            'phone_number' => $request['phone_number'],
            'password' => bcrypt($request['password']),
            'role' => $request['role'],
        ]);

        foreach ($request['buildings'] as $building) {
            StaffAssignment::create([
                'staff_id' => $staff->id,
                'building_id' => $building['building_id'],
                'role' => $building['role'],
                'assigned_tasks' => $building['assigned_tasks'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return $staff;
    }

    public function deleteStaff($id)
    {
        StaffAssignment::where('staff_id', $id)->delete();
        $deleteStaff = User::where('id', $id)->delete();
        return $deleteStaff;
    }
}
