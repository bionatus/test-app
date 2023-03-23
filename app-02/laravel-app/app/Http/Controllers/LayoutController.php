<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Layout;

class LayoutController extends Controller
{
    /**
     * Get a the layout JSON for the provided version
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String                    $version
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,string $version) {
        $firstDigit = substr($version, 0, 1);

        return Layout::where('version', '<=', $version)
            ->where('version', 'LIKE', $firstDigit . '%')
            ->orderBy('version', 'DESC')
            ->firstOrFail();
    }
}
