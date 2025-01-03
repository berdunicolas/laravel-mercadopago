<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Mercado Pago</title>
</head>

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
</body>
</html>