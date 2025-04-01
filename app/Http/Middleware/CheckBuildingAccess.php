<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckBuildingAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $buildingId = $request->input('building_id') ?? $request->json('building_id');
        dd($buildingId);

        // Nếu là admin, cho phép truy cập
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Nếu là staff, kiểm tra quyền truy cập tòa nhà
        $isAssigned = DB::table('staff_assignments')
            ->where('staff_id', $user->id)
            ->where('building_id', $buildingId)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
