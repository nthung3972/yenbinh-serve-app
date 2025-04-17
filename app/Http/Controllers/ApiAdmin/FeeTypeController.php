<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use Illuminate\Http\Request;

class FeeTypeController extends Controller
{
    public function getFlexibleFee(Request $request)
    {
        $fee = FeeType::where('is_fixed', false)->get();
        return response()->json($fee);
    }
}
