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

        date_default_timezone_set('Africa/Dar_es_Salaam');
        $requestTimestamp = date('c');

        dd(route('webhook'));


        $webhook = base64_encode(route('webhook'));
        $redirect_url = base64_encode(route('success'));

        $data = [
            'vendor' => env('TILL_NUMBER'),
            'order_id' =>  date('His', strtotime($requestTimestamp)),
            'buyer_email' => $validatedData['email'],
            'buyer_name' => $validatedData['buyer_name'],
            'buyer_phone' => $validatedData['buyer_phone'],
            'amount' => $validatedData['amount'],
            'currency' => 'TZS',
            'webhook' => $webhook,
            'no_of_items' => '1',
            'redirect_url' => $redirect_url,
        ];


        $signed_fields = 'vendor,order_id,buyer_email,buyer_name,buyer_phone,amount,currency,webhook,no_of_items,redirect_url';

        $endpointUrl = env('BASE_URL') . '/checkout/create-order-minimal';


        $signature = $this->computeSignature($data, $signed_fields, $requestTimestamp);

        Log::info('Signed Fields: ' . $signed_fields .  ' Signature: ' . $signature . ' Data: ' . json_encode($data));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json;charset=\"utf-8\"',
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache',
            'Authorization' => 'SELCOM ' . base64_encode(env('API_KEY')),
            'Digest-Method' => 'HS256',
            'Digest' => $signature,
            'Timestamp' => $requestTimestamp,
            'Signed-Fields' => $signed_fields,
        ])->post($endpointUrl, $data);


        Log::info('Response Body: ' . $response->body());





        $paymentGatewayUrl = base64_decode(json_decode($response->body(), true)['data'][0]['payment_gateway_url']);

        Log::info('Payment Gateway Url: ' . $paymentGatewayUrl);

        return redirect($paymentGatewayUrl);
    }


    public function computeSignature($data ,$signed_fields, $requestTimestamp): string
    {
        $fieldsOrder = explode(',', $signed_fields);
        $signData = "timestamp=$requestTimestamp";

        foreach ($fieldsOrder as $key) {
            $signData .= "&$key=" . $data[$key];
        }

        return base64_encode(hash_hmac('sha256', $signData, env('API_SECRET'), true));
    }


    public function webhook(Request $request)
    {
        dd($request);

        // Record complition og the Transaction
    }


}
