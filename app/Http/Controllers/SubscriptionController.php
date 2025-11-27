<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSubscription;

class SubscriptionController extends Controller
{
    
    public function index(){
        return view('pages.subscription.index');
    }

    public function plans_index()
    {
        $user = Auth::user();

        $organizer_plans = $this->getPlanData(); 

        return view('pages.subscription.index', compact('user', 'organizer_plans'));
    }

    public function preview($plan_id)
    {
       
        $plans = $this->getPlanData();
        $selectedPlan = collect($plans)->firstWhere('id', $plan_id);

        if (!$selectedPlan) {
            return back()->withErrors('Paket tidak ditemukan.');
        }

        return view('pages.subscription.preview', compact('selectedPlan'));
    }

     public function purchase(Request $request)
    {
        $request->validate(['plan_id' => 'required']);

       
        $plans = $this->getPlanData();
        
        $selectedPlan = collect($plans)->firstWhere('id', $request->plan_id);

        if (!$selectedPlan) {
            return back()->withErrors('Paket tidak ditemukan.');
        }

        $maxEvents = 1; 

        if ($selectedPlan->id == 'org_annual') {
            $maxEvents = 10; 
        } elseif ($selectedPlan->id == 'org_enterprise') {
            $maxEvents = 20; 
        }

     
        $priceNumeric = (int) filter_var($selectedPlan->price, FILTER_SANITIZE_NUMBER_INT);

     
        $subscription = UserSubscription::create([
            'user_id' => Auth::user()->user_id,
            'plan_code' => $selectedPlan->id, 
            'plan_name' => $selectedPlan->name,
            'price' => $priceNumeric,
            'max_events' => $maxEvents,       
            'status' => 'pending',           
        ]);

  
        return redirect()->route('subscription.payment.show', $subscription->id)
            ->with('success', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.');
    }

  
    public function payments_index()
    {
        $subscriptions = UserSubscription::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.subscription.payments.index', compact('subscriptions'));
    }

 
    public function payment_show($id)
    {
        $subscription = UserSubscription::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

       
        $bankInfo = [
            'bank_name' => 'Bank BRI',
            'account_number' => '1234-5678-9000-111',
            'account_holder' => 'PT Submeet Indonesia',
        ];

        return view('pages.subscription.payments.show', compact('subscription', 'bankInfo'));
    }

 
    public function upload_proof(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $subscription = UserSubscription::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if ($request->hasFile('payment_proof')) {
          
            if ($subscription->payment_proof) {
                Storage::disk('public')->delete($subscription->payment_proof);
            }

            
            $path = $request->file('payment_proof')->store('subscription_proofs', 'public');

        
            $subscription->payment_proof = $path;
       
            $subscription->save();

            return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah! Menunggu konfirmasi Admin.');
        }

        return back()->withErrors('Gagal mengunggah file.');
    }

    private function getPlanData()
    {
        return [
            (object)[
                'id' => 'org_single',
                'name' => 'Single Event Package',
                'price' => 'IDR 1.500.000',
                'features' => ['Publish 1 Event', 'Up to 100 Participants']
            ],
            (object)[
                'id' => 'org_annual',
                'name' => 'Annual Pro Package',
                'price' => 'IDR 5.000.000',
                'features' => ['5 Events/year', 'Priority Support']
            ],
            (object)[
                'id' => 'org_enterprise',
                'name' => 'Enterprise Package',
                'price' => 'IDR 10.000.000', 
                'features' => ['10 Events/year',  'Dedicated Account Manager']
            ]
        ];
    }
}