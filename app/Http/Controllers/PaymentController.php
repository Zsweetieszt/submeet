<?php

namespace App\Http\Controllers;

use App\Models\ApiLogs;
use App\Models\Country;
use App\Models\Event;
use App\Models\Paper;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PaymentSettings;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\UserLogs; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;  

class PaymentController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

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
            return response()->json(['status' => 0, 'response' => 'An error occurred while logging in to API.']);
        }
    }

    public function createBrivaPayment($fullname, $paperID, $email, $amount)
    {
        try {
            $loginResponse = $this->loginToPolbanAPI();
            $loginData = $loginResponse->getData(true);

            if ($loginData['status'] == 1 && $loginData['response']['status'] == 1) {
                $token = $loginData['response']['data']['token'];

                $response = Http::withBasicAuth(env('POLBAN_API_USERNAME'), $token)
                    ->post('https://api.polban.ac.id/issat/create_payment', [
                        'fullname' => $fullname,
                        'paperID' => $paperID,
                        'email' => $email,
                        'amount' => $amount,
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
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'response' => 'An error occurred while creating briva payment.']);
        }
    }

    public function index(Request $request, $event)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            $event_id = $event->event_id;
            $papers = Paper::with(['payment', 'event', 'first:paper_sub_id,status'])
                ->with([
                    'payment' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    }
                ])
                ->where('user_id', '=', auth()->user()->user_id)
                ->where('status', '=', 'Accepted')
                ->where('event_id', '=', $event_id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($papers->isEmpty()) {
                $papers = [];
            }

            $payment_set = PaymentSettings::where('event_id', $event_id)->first();
            $payment = Payment::where('event_id', $event_id)->where('paid_by', auth()->user()->user_id)->orderBy('created_at', 'desc')->first();
            // dd($papers);
            // $firstPaperIds = $papers->pluck('first_paper_sub_id')->toArray();

            // $allPaperVersions = Paper::whereIn('first_paper_sub_id', $firstPaperIds)
            //                         ->orderBy('round', 'desc')
            //                         ->get();

            // $event = $request->route('event');

            // $countries = Country::orderBy('country_name', 'asc')->get();
            $payment_set = PaymentSettings::where('event_id', $event->event_id)->first();

            // non presenter
            $current_payment = Payment::where('paid_by', '=', auth()->user()->user_id)
                ->where('event_id', '=', $event->event_id)
                ->first() ?? null;

            if (!is_null($current_payment)) {
                $existingPaymentHistory = PaymentHistory::where('payment_id', $current_payment->payment_id)
                    ->latest('created_at')
                    ->first();
            } else {
                $existingPaymentHistory = null;
            }
            

            $countries = Country::orderBy('country_name', 'asc')->get();

            return view('pages.payments.index', compact('event', 'papers', 'payment_set', 'payment', 'countries', 'current_payment', 'existingPaymentHistory'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading papers.');
        }
    }

    public function index_receipt(Request $request, $event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            // $eventObj = Event::where('event_code', $event)->first();
            // if (!$eventObj) {
            //     return back()->withErrors('Event not found.');
            // }
            // $event_id = $eventObj->event_id;
            $paper = Paper::with(['payment', 'event', 'first:paper_sub_id,status'])
                ->where('paper_sub_id', '=', $paper_id)
                ->where('user_id', '=', auth()->user()->user_id)
                ->where('event_id', '=', $event->event_id)
                ->orderBy('created_at', 'desc')
                ->first();
            // dd($paper);
            // $event = $request->route('event');


            $paper_authors = $paper->authors ? json_decode($paper->authors, true) : [];
            // dd($paper_authors);

            $payment_set = PaymentSettings::where('event_id', $event->event_id)->first();

            $current_payment = Payment::where('first_paper_sub_id', '=', $paper->paper_sub_id)
                ->where('event_id', '=', $event->event_id)
                ->first() ?? null;

            if ($current_payment) {
                $existingPaymentHistory = PaymentHistory::where('payment_id', $current_payment->payment_id)
                    ->where('expired_date', '>', now())
                    ->orderBy('created_at', 'desc')
                    ->first() ?? null;
            } else {
                $existingPaymentHistory = null;
            }

            $countries = Country::orderBy('country_name', 'asc')->get();

            return view('pages.payments.upload_receipt', compact('event', 'paper', 'payment_set', 'countries', 'paper_authors', 'current_payment', 'existingPaymentHistory'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading papers.');
        }
    }

    public function payment_proof(Request $request, $event, $payment_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }

            $payment = PaymentHistory::where('payment_id', '=', $payment_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$payment) {
                return back()->withErrors('Payment not found.');
            }

            $request->validate(
                [
                    'receipt' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
                    'desc' => 'nullable|string|max:500',
                ],
                [
                    'receipt.required' => 'Please upload a receipt file.',
                    'receipt.mimes' => 'The receipt must be a file of type: jpg, jpeg, png, pdf.',
                    'receipt.max' => 'The receipt may not be greater than 2MB.',
                    'desc.max' => 'The description may not be greater than 500 characters.',
                ]
            );

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filePath = null;

                try {
                    $filename = 'receipt_' . time() . '.' . $file->getClientOriginalExtension();
                    if ($payment->payment->first_paper_sub_id) {
                        $filePath = $file->storeAs(config('path.payment_receipt') . $event->event_id . '/' . $payment->first_paper_sub_id, $filename, 'public');
                    } else {
                        $filePath = $file->storeAs(config('path.payment_receipt') . $event->event_id . '/nonpresenter/' . auth()->user()->user_id, $filename, 'public');
                    }
                } catch (\Exception $e) {
                    return back()->withErrors('Failed to upload file.');
                }

                try {
                    if (!$payment->upload_receipt_at) {
                        $payment->receipt = $filePath;
                        $payment->upload_receipt_at = now();
                        $payment->desc = $request->desc;
                        $payment->save();
                    } else {
                        PaymentHistory::create([
                            'payment_id' => $payment->payment->payment_id,
                            'brivano' => $payment->brivano,
                            'expired_date' => $payment->expired_date,
                            'receipt' => $filePath,
                            'upload_receipt_at' => now(),
                            'desc' => $request->desc,
                        ]);
                    }

                    $payment->payment->status = 'Pending';
                    $payment->payment->save();

                    try {
                        $user = Auth::user();
                        if ($user) {
                            UserLogs::create([
                                'user_id' => $user->user_id,
                                'ip_address' => $request->getClientIp(),
                                'user_log_type' => 'Upload Payment Proof', // <-- Nilai ENUM
                                'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                                'created_at' => now(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal mencatat log Upload Payment Proof: ' . $e->getMessage());
                    }

                } catch (\Exception $e) {
                    // Delete uploaded file if payment save fails
                    if ($filePath && \Storage::disk('public')->exists($filePath)) {
                        \Storage::disk('public')->delete($filePath);
                    }
                    return back()->withErrors('Failed to save payment info');
                }

                if ($payment->payment->first_paper_sub_id) {
                    return back()
                        ->with(['event' => $event->event_code, 'paper' => $payment->payment->first_paper_sub_id])
                        ->with('success', 'Payment proof uploaded successfully and payment is pending verification.');
                } else {
                    return back()
                        ->with(['event' => $event->event_code])
                        ->with('success', 'Payment proof uploaded successfully and payment is pending verification.');
                }
            } else {
                return back()->withErrors('No file uploaded.');
            }
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while uploading the payment proof.');
        }
    }

    public function apply_payment_info(Request $request, $event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();

            if (!$event) {
                return back()->withErrors('Event not found.');
            }

            $payment_set = PaymentSettings::where('event_id', $event->event_id)->first();

            $is_presenter = auth()->user()
                ->user_events()
                ->whereHas('role', function ($q) {
                    $q->where('role_name', 'Presenter');
                })
                ->whereHas('event', function ($q) use ($event) {
                    $q->where('event_code', $event->event_code);
                })
                ->exists();

            if ($is_presenter) {
                $paper = Paper::where('paper_sub_id', '=', $paper_id)
                    // ->where('user_id', '=', auth()->user()->user_id)
                    ->where('event_id', '=', $event->event_id)
                    ->first();

                if (!$paper) {
                    return back()->withErrors('Paper not found.');
                }

                $request->validate([
                    'presenter' => 'required|string|max:100',
                    'country_of_nationality' => 'required|exists:countries,country_id',
                    'attendance' => 'required|boolean',
                ]);

                try {
                    $payment = Payment::updateOrCreate(
                        [
                            'first_paper_sub_id' => $paper->paper_sub_id,
                            'event_id' => $event->event_id,
                            'paid_by' => auth()->user()->user_id,
                        ],
                        [
                            'presenter' => $request->presenter,
                            'nationality_country_id' => $request->country_of_nationality,
                            'is_offline' => ($request->country_of_nationality == $event->country_id) ? true : (($request->attendance == "1") ? true : false),
                        ]
                    );

                    try {
                        $user = Auth::user();
                        if ($user) {
                            UserLogs::create([
                                'user_id' => $user->user_id,
                                'ip_address' => $request->getClientIp(),
                                'user_log_type' => 'Request Payment', // <-- Nilai ENUM
                                'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                                'created_at' => now(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal mencatat log Request Payment: ' . $e->getMessage());
                    }

                    $existingPaymentHistory = PaymentHistory::where('payment_id', $payment->payment_id)
                        ->where('expired_date', '>', now())
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($payment->status == (null || 'Unpaid')) {
                        if ($payment->nationality_country_id == $event->country_id && $payment->is_offline == true) {
                            try {
                                $payment->role = 'Presenter';
                                $payment_history = PaymentHistory::where('payment_id', $payment->payment_id)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
                                $payment->payment_history = $payment_history;
                                $payment->currency = $payment_set->pay_as_pstr_off_ntl_curr ?? 'IDR';
                                $payment->amount = $payment_set->pay_as_pstr_off_ntl_amount ?? '0.00';
                                $this->emailService->sendNationalPaymentEmail($payment->presenter, $event, $payment);
                            } catch (\Exception $e) {
                                return back()->withErrors(
                                    'Failed to send payment email.'
                                );
                            }
                        } else if ($payment->nationality_country_id != $event->country_id) {
                            if ($payment->is_offline == false) {
                                try {
                                    $payment->role = 'Presenter';
                                    $payment_history = PaymentHistory::where('payment_id', $payment->payment_id)
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                                    $payment->payment_history = $payment_history;
                                    $payment->currency = $payment_set->pay_as_pstr_on_intl_curr ?? 'USD';
                                    $payment->amount = $payment_set->pay_as_pstr_on_intl_amount ?? '0.00';
                                    $payment->bank_name = $payment_set->acc_beneficiary_name;
                                    $payment->account_name = $payment_set->acc_bank_name;
                                    $payment->account_number = $payment_set->acc_bank_acc;
                                    $payment->swift_code = $payment_set->acc_swift_code;
                                    $this->emailService->sendInternationalPaymentEmail($payment->presenter, $event, $payment);
                                } catch (\Exception $e) {
                                    return back()->withErrors(
                                        'Failed to send payment email.'
                                    );
                                }
                            }
                        }
                    }

                    if (!$existingPaymentHistory) {
                        if ($payment->nationality_country_id == $event->country_id && $payment->is_offline == true) {
                            // National Offline/Online presenter
                            $response = $this->createBrivaPayment(
                                auth()->user()->given_name . ' ' . auth()->user()->family_name,
                                $paper->paper_sub_id,
                                auth()->user()->email,
                                (int) $payment_set->pay_as_pstr_off_ntl_amount
                            );

                            $responseData = $response->getData(true);
                            if ($responseData && $responseData['status'] == 1) {
                                if (
                                    $responseData['response']['status'] == 1 &&
                                    isset($responseData['response']['brivano'])
                                ) {
                                    try {
                                        PaymentHistory::create([
                                            'payment_id' => $payment->payment_id,
                                            'brivano' => $responseData['response']['brivano'],
                                            'expired_date' => $responseData['response']['expiredDate'],
                                        ]);

                                        Payment::where('payment_id', $payment->payment_id)
                                            ->update(['status' => 'Unpaid']);

                                    } catch (\Exception $e) {
                                        return back()->withErrors(
                                            'Failed to save payment data.'
                                        );
                                    }
                                } else {
                                    return back()->withErrors(
                                        'Failed to create payment.'
                                    );
                                }
                            } else {
                                return back()->withErrors(
                                    'Payment service unavailable. Please try again later.'
                                );
                            }
                        } else if($payment->nationality_country_id != $event->country_id) {
                            // Intenational
                            try {
                                PaymentHistory::create([
                                    'payment_id' => $payment->payment_id,
                                ]);
                            } catch (\Exception $e) {
                                return back()->withErrors(
                                    'Failed to save payment info.'
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(
                        'Failed to save payment info.'
                    );
                }

                return back()
                    ->with(['event' => $event->event_code, 'paper' => $paper->paper_sub_id])
                    ->with('success', 'Saving payment information success.');
            } else {
                // Non-presenter
                $request->validate([
                    'country_of_nationality' => 'required|exists:countries,country_id',
                    'attendance' => 'required|boolean',
                ]);

                try {
                    $payment = Payment::updateOrCreate(
                        [
                            'event_id' => $event->event_id,
                            'paid_by' => auth()->user()->user_id,
                        ],
                        [
                            'nationality_country_id' => $request->country_of_nationality,
                            'is_offline' => $request->attendance == "1" ? true : false,
                        ]
                    );

                    $existingPaymentHistory = PaymentHistory::where('payment_id', $payment->payment_id)
                        ->where('expired_date', '>', now())
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($payment->status == (null || 'Unpaid')) {
                        if ($payment->is_offline == true && $payment->nationality_country_id == $event->country_id) {
                            try {
                                $payment->role = 'Non-Presenter';
                                $payment_history = PaymentHistory::where('payment_id', $payment->payment_id)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
                                $payment->payment_history = $payment_history;
                                $payment->currency = $payment_set->pay_as_npstr_off_ntl_curr ?? 'IDR';
                                $payment->amount = $payment_set->pay_as_npstr_off_ntl_amount ?? '0.00';
                                $this->emailService->sendNationalPaymentEmail(auth()->user(), $event, $payment);
                            } catch (\Exception $e) {
                                return back()->withErrors(
                                    'Failed to send payment email.'
                                );
                            }
                        }
                    }

                    //national offline
                    if (!$existingPaymentHistory) {
                        if ($payment->is_offline == true && $payment->nationality_country_id == $event->country_id) {
                            if ($event->country_id == $payment->nationality_country_id && $payment->is_offline == true) {
                                $response = $this->createBrivaPayment(
                                    auth()->user()->given_name . ' ' . auth()->user()->family_name,
                                    '',
                                    auth()->user()->email,
                                    (int) $payment_set->pay_as_npstr_off_ntl_amount
                                );

                                $responseData = $response->getData(true);
                                if ($responseData && $responseData['status'] == 1) {
                                    if (
                                        $responseData['response']['status'] == 1 &&
                                        isset($responseData['response']['brivano'])
                                    ) {
                                        try {
                                            PaymentHistory::create([
                                                'payment_id' => $payment->payment_id,
                                                'brivano' => $responseData['response']['brivano'],
                                                'expired_date' => $responseData['response']['expiredDate'],
                                            ]);

                                            Payment::where('payment_id', $payment->payment_id)
                                                ->update(['status' => 'Unpaid']);

                                        } catch (\Exception $e) {
                                            return back()->withErrors(
                                                'Failed to save payment data.'
                                            );
                                        }
                                    } else {
                                        return back()->withErrors(
                                            'Failed to create payment: '
                                        );
                                    }
                                } else {
                                    return back()->withErrors(
                                        'Payment service unavailable. Please try again later.'
                                    );
                                }
                            }
                        } else if($payment->nationality_country_id != $event->country_id) {
                            try {
                                PaymentHistory::create([
                                    'payment_id' => $payment->payment_id,
                                ]);
                            } catch (\Exception $e) {
                                return back()->withErrors(
                                    'Failed to save payment info.'
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(
                        'Failed to save payment info.'
                    );
                }

                return back()
                    ->with(['event' => $event->event_code])
                    ->with('success', 'Saving payment information success.');
            }
        } catch (\Exception $e
        ) {
            return back()->withErrors(
                'An error occurred while saving payment information.'
            );
        }
    }

    public function download_receipt($event, $payment_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }

            $payment = PaymentHistory::where('payment_id', '=', $payment_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$payment) {
                return back()->withErrors('Payment not found.');
            }

            if ($payment->payment->first_paper_sub_id) {
                $paper = Paper::where('paper_sub_id', '=', $payment->payment->first_paper_sub_id)
                    ->where('event_id', '=', $event->event_id)
                    ->first();

                if (!$paper) {
                    return back()->withErrors('Paper not found.');
                }
            }

            if (!$payment || !$payment->receipt) {
                return back()->withErrors('Receipt not found.');
            }

            $filePath = $payment->receipt;
            if (\Storage::disk('public')->exists($filePath)) {
                $fullPath = storage_path('app/public/' . $filePath);
                return response()->download($fullPath);
            } else {
                return back()->withErrors('File does not exist on the server.');
            }
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while downloading the receipt.');
        }
    }
}
