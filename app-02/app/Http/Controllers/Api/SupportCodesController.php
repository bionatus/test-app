<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Technician;

class SupportCodesController extends Controller
{
    /**
     * Get all support codes
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $code = $request->code;
        $technician = Technician::where('code', $code)->firstOrFail();

        return response()->json([
            'id'    => $technician->id,
            'name'  => $technician->name,
            'code'  => $technician->code,
            'phone' => $technician->phone,
            'image' => asset('storage/' . $technician->image),
        ]);
    }
}
