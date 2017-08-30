<?php include ("vendor/autoload.php");

Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('r8j49sgytxyxjtrg');
Braintree_Configuration::publicKey('m793x7zyjgwfncjx');
Braintree_Configuration::privateKey('c6c843e4ffe9c3a48095695dc4657dec');





if(isset($_POST['hidden-nonce'])){
   // echo $_POST['hidden-nonce'];



    $result = Braintree\Transaction::sale([
        'amount' => 20,
        'paymentMethodNonce' => $_POST['hidden-nonce'],
        'options' => [
            'submitForSettlement' => true
        ]
    ]);



    if ($result->success || !is_null($result->transaction)) {
        $transaction = $result->transaction;
    } else {
        $errorString = "";
        foreach ($result->errors->deepAll() as $error) {
            $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
        }
    }


    if ($result->success) {
        print_r("Success ID: " . $result->transaction->id);
    } else {
        print_r("Error Message: " . $result->message);
    }

}

?>

<!DOCTYPE html>




<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://www.paypalobjects.com/api/checkout.js"></script>
    <script src="https://js.braintreegateway.com/web/3.11.0/js/client.min.js"></script>
    <script src="https://js.braintreegateway.com/web/3.11.0/js/paypal-checkout.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>


<div id="paypal-button"></div>
<form method="post" action="#">
<input type="hidden" name="hidden-nonce" id="hidden-nonce">
</form>







<script>

    // Create a client.
    braintree.client.create({
        authorization: '<?=Braintree_ClientToken::generate()?>'
    }, function (clientErr, clientInstance) {

        // Stop if there was a problem creating the client.
        // This could happen if there is a network error or if the authorization
        // is invalid.
        if (clientErr) {
            console.error('Error creating client:', clientErr);
            return;
        }

        // Create a PayPal Checkout component.
        braintree.paypalCheckout.create({
            client: clientInstance
        }, function (paypalCheckoutErr, paypalCheckoutInstance) {

            // Stop if there was a problem creating PayPal Checkout.
            // This could happen if there was a network error or if it's incorrectly
            // configured.
            if (paypalCheckoutErr) {
                console.error('Error creating PayPal Checkout:', paypalCheckoutErr);
                return;
            }

            // Set up PayPal with the checkout.js library
            paypal.Button.render({
                env: 'sandbox', // or 'sandbox'
                commit: true, // This will add the transaction amount to the PayPal button

                payment: function () {
                    return paypalCheckoutInstance.createPayment({
                        flow: 'checkout', // Required
                        amount: 10.00, // Required
                        currency: 'USD', // Required
                        locale: 'en_US',
                        enableShippingAddress: true,
                        shippingAddressEditable: false,
                        shippingAddressOverride: {
                            recipientName: 'Scruff McGruff',
                            line1: '1234 Main St.',
                            line2: 'Unit 1',
                            city: 'Chicago',
                            countryCode: 'US',
                            postalCode: '60652',
                            state: 'IL',
                            phone: '123.456.7890'
                        }
                    });
                },

                onAuthorize: function (data, actions) {
                    return paypalCheckoutInstance.tokenizePayment(data)
                        .then(function (payload) {

                            $("#hidden-nonce").val(payload.nonce);

                            $("form").submit();




                        });
                },

                onCancel: function (data) {
                    console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));
                },

                onError: function (err) {
                    console.error('checkout.js error', err);
                }
            }, '#paypal-button').then(function () {
                // The PayPal button will be rendered in an html element with the id
                // `paypal-button`. This function will be called when the PayPal button
                // is set up and ready to be used.
            });

        });

    });




</script>
</body>
</html>