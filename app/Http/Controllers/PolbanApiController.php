<?php

namespace App\Http\Controllers;

use App\Models\ApiLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PolbanApiController extends Controller
{
    public function loginToPolbanAPI()
    {
        try {
            $response = Http::post('https://api.polban.ac.id/login', [
                'username' => env('POLBAN_API_USERNAME'),
                'password' => env('POLBAN_API_PASSWORD'),
            ]);

            return response()->json([
                'status' => $response->successful() ? 1 : 0,
                'response' => $response->json(),
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 0, 'response' => $th->getMessage()]);
        }
    }

    public function createBrivaPayment(Request $request)
    {
        try {
            $loginResponse = $this->loginToPolbanAPI();
            $loginData = $loginResponse->getData(true);

            if ($loginData['status'] == 1 && $loginData['response']['status'] == 1) {
                $token = $loginData['response']['data']['token'];

                $response = Http::withBasicAuth(env('POLBAN_API_USERNAME'), $token)
                    ->post('https://api.polban.ac.id/issat/create_payment', [
                        'fullname' => $request->fullname,
                        'paperID' => $request->paperID,
                        'email' => $request->email,
                        'amount' => $request->amount,
                        'server' => env('APP_ENV') != 'production' ? 'DEV' : 'PROD',
                ]);

                ApiLogs::create([
                    'type' => 'create_payment',
                    'response_data' => $response->json(),
                ]); 

                return response()->json($response->json());
            } else {

                ApiLogs::create([
                    'type' => 'failed_login',
                    'response_data' => $loginResponse->original,
                ]);

                return response()->json(['status' => 0, 'response' => $loginData['response'] ?? 'Failed to get token']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => 0, 'response' => $th->getMessage()]);
        }
    }

    public function getBrivaResponse(Request $request)
    {
        try {
            $response = $request->all();

            ApiLogs::create([
                'type' => 'confirmation',
                'response_data' => $response,
            ]);

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => 0, 'response' => $th->getMessage()]);
        }
    }
}
