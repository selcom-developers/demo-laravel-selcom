<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{

    public function index()
    {
        return view('checkout');
    }


    public function create()
    {
        return view('checkout');
    }

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'buyer_name' => 'required',
            'buyer_phone' => 'required',
            'email' => 'required',
            'amount' => 'required',
        ]);




        $data = [
            'vendor' => env('TILL_NUMBER'),
            'order_id' => time().'-'.$validatedData['buyer_phone'],
            'buyer_email' => $validatedData['email'],
            'buyer_name' => $validatedData['buyer_name'],
            'buyer_phone' => $validatedData['buyer_phone'],
            'amount' => $validatedData['amount'],
            'currency' => 'TZS',
            'webhook' => base64_decode(route('webhook')),
            'buyer_remarks' => 'None',
            'merchant_remarks' => 'None',
            'no_of_items' => 1,
            'redirect_url' => base64_decode(route('success')),
        ];

        $endpointUrl = env('BASE_URL') . '/checkout/create-order-minimal';
        $signature = base64_encode(hash_hmac('sha256', $data['order_id'] . $data['buyer_email'] . $data['buyer_name'] . $data['buyer_phone'] . $data['amount'] . $data['currency'] . $data['webhook'] . $data['buyer_remarks'] . $data['merchant_remarks'] . $data['no_of_items'] . $data['redirect_url'], env('API_SECRET'), true));
        $signed_fields = 'order_id,buyer_email,buyer_name,buyer_phone,amount,currency,webhook,buyer_remarks,merchant_remarks,no_of_items,redirect_url';
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $date = date('c');


        Log::info('Signed Fields: ' . $signed_fields. ' Signature: '.$signature. ' Data: '.$data. ' Endpoint: '.$endpointUrl. ' Date: '.$date);


        $response = Http::withHeaders([
            'Authorization' => 'SELCOM ' . base64_decode(env('API_KEY')),
            'Digest-Method' => 'HS256',
            'Digest' => $signature,
            'Timestamp' => $date,
            'Signed-Fields' => $signed_fields,
        ])->post($endpointUrl, $data);

        Log::info($response);

    }


    public function webhook(Request $request)
    {
        dd($request);
    }


}
