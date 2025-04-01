<?php

namespace App\Repositories;
use App\Models\User;
use App\Models\StaffAssignment;
use Carbon\Carbon;


class StaffRepository
{
    public function createStaff($request)
    {
        $staff = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
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
}
