# Como integrar Mercado Pago en Laravel con Checkout API by mercado pago


## Requerimientos 

- Tener cuenta en mercado pago.
- Crear credenciales de access token y public key desde [mercadopago.com.ar/developers](https://www.mercadopago.com.ar/developers/).
- [SDK oficial de Mercado Pago para PHP](https://github.com/mercadopago/sdk-php).


## Empecemos

### Instala el SDK de Mercado Pago
Primero instala la dependencia de Mercado Pago para PHP

``` bash
composer require mercadopago/dx-php
```

### Configura tus credenciales
Agrega las credenciales de tu app de Mercado Pago en el .env

```
MERCADOPAGO_ACCESS_TOKEN=tu_access_token
MERCADOPAGO_PUBLIC_KEY=tu_public_key
```


### Crea el formulario de pago
Haz una vista y utiliza el formulario proporcionado por Mercado Pago para hacer un prueba.
```html
<style>
    #form-checkout {
      display: flex;
      flex-direction: column;
      max-width: 600px;
    }

    .container {
      height: 18px;
      display: inline-block;
      border: 1px solid rgb(118, 118, 118);
      border-radius: 2px;
      padding: 1px 2px;
    }
</style>
<body>
    <form id="form-checkout">
        @csrf

        <div id="form-checkout__cardNumber" class="container"></div>
        <div id="form-checkout__expirationDate" class="container"></div>
        <div id="form-checkout__securityCode" class="container"></div>
        <input type="text" id="form-checkout__cardholderName" />
        <select id="form-checkout__issuer"></select>
        <select id="form-checkout__installments"></select>
        <select id="form-checkout__identificationType"></select>
        <input type="text" id="form-checkout__identificationNumber" />
        <input type="email" id="form-checkout__cardholderEmail" />
    
        <button type="submit" id="form-checkout__submit">Pagar</button>
        <progress value="0" class="progress-bar">Cargando...</progress>
    </form>
```

Ademas inserta el script js

``` html
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const mp = new MercadoPago("{{ $mp_public_key }}");

        const csrf_token = document.querySelector('meta[name="csrf-token"]').content;


        const cardForm = mp.cardForm({
            amount: "100.5",
            iframe: true,
            form: {
                id: "form-checkout",
                cardNumber: {
                id: "form-checkout__cardNumber",
                placeholder: "Numero de tarjeta",
                },
                expirationDate: {
                id: "form-checkout__expirationDate",
                placeholder: "MM/YY",
                },
                securityCode: {
                id: "form-checkout__securityCode",
                placeholder: "Código de seguridad",
                },
                cardholderName: {
                id: "form-checkout__cardholderName",
                placeholder: "Titular de la tarjeta",
                },
                issuer: {
                id: "form-checkout__issuer",
                placeholder: "Banco emisor",
                },
                installments: {
                id: "form-checkout__installments",
                placeholder: "Cuotas",
                },        
                identificationType: {
                id: "form-checkout__identificationType",
                placeholder: "Tipo de documento",
                },
                identificationNumber: {
                id: "form-checkout__identificationNumber",
                placeholder: "Número del documento",
                },
                cardholderEmail: {
                id: "form-checkout__cardholderEmail",
                placeholder: "E-mail",
                },
            },
            callbacks: {
                onFormMounted: error => {
                    if (error) return console.warn("Form Mounted handling error: ", error);
                    console.log("Form mounted");
                },
                onSubmit: event => {
                    event.preventDefault();

                    const {
                        paymentMethodId: payment_method_id,
                        issuerId: issuer_id,
                        cardholderEmail: email,
                        amount,
                        token,
                        installments,
                        identificationNumber,
                        identificationType,
                    } = cardForm.getCardFormData();

                    fetch("/process_payment", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrf_token
                        },
                        body: JSON.stringify({
                        token,
                        issuer_id,
                        payment_method_id,
                        transaction_amount: Number(amount),
                        installments: Number(installments),
                        description: "Descripción del producto",
                        payer: {
                            email,
                            identification: {
                            type: identificationType,
                            number: identificationNumber,
                            },
                        },
                        }),
                    });
                },
                onFetching: (resource) => {
                    console.log("Fetching resource: ", resource);

                    // Animate progress bar
                    const progressBar = document.querySelector(".progress-bar");
                    progressBar.removeAttribute("value");

                    return () => {
                        progressBar.setAttribute("value", "0");
                    };
                }
            }
        });
    </script>
```


### Crea la api para procesar el pago con Mercado Pago
 

```php
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
```


> NOTA: los codigos proporcionados se encuentran en https://www.mercadopago.com.ar/developers/es/docs/checkout-api/integration-configuration/integrate-mp-wallet


### Haz una prueba
Utiliza las tarjetas para pruebas proporcionadas por Mercado Pago

[mercadopago.com.ar/developers/es/docs/your-integrations/test/cards](https://www.mercadopago.com.ar/developers/es/docs/your-integrations/test/cards)