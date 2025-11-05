<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function get(Request $request)
    {
        try {
            $countries = Country::orderBy('country_name')->get();
            return response()->json([
                'code' => 200,
                'data' => $countries,
                'message' => 'Successfully fetched countries'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => str_replace(["\r", "\n"], ' ', $e->getMessage())
            ], 500);
        }
    }
}
