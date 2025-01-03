<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\MercadoPagoConfig;

class PaymentController extends Controller
{
    public function pantallaDeCobro(){
        //return view();
    }

    public function formularioDePago(){
        return view('payments.pantalla_de_pago', ['mp_public_key' => config('app.mp_public_key')]);
    }

    public function procesoDePago(Request $request){
        MercadoPagoConfig::setAccessToken(config('app.mp_access_token'));

        if(config('app.env') == 'local') MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $client = new PaymentClient();
        $request_options = new RequestOptions();
        //$request_options->setCustomHeaders(["X-Idempotency-Key: " . Str::uuid()->toString()]);

        $payload = [
            "transaction_amount" => (float) $request['transaction_amount'],
            "token" => $request['token'],
            "description" => $request['description'],
            "installments" => $request['installments'],
            "payment_method_id" => $request['payment_method_id'],
            "issuer_id" => $request['issuer_id'],
            "payer" => [
                "email" => $request['payer']['email'],
                "identification" => [
                    "type" => $request['payer']['identification']['type'],
                    "number" => $request['payer']['identification']['number']
                ]
            ]
        ];
        
        $payment = $client->create($payload, $request_options);

        return response()->json($payment, 200);
    }
}
