<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="formDesign.css">
</head>
<body>
    <?php
        require_once('vendor/autoload.php');

        $client = new \GuzzleHttp\Client();

        date_default_timezone_set("Hongkong");
        $entryTime = date("H");
        $entryTimeInMinutes = ($entryTime * 60) + date("i");
        $currentTime = date("H:i:s");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $product1 = $_POST['firstProduct'];
            $price1 = $_POST["firstProductPrice"];
            $quantity1 = $_POST["quantity1"];
            $product2 = $_POST["secondProduct"];
            $price2 = $_POST["secondProductPrice"];
            $quantity2 = $_POST["quantity2"];
            $total1 = $price1 * $quantity1;
            $total2 = $price2 * $quantity2;
            $totalAmount = $total1 + $total2;

            $paymayaResponse = $client->request('POST', 'https://pg-sandbox.paymaya.com/checkout/v1/checkouts', [
                'body' => '{
                    "totalAmount": {"value": '.$totalAmount.', "currency": "PHP"},
                    "buyer": {"firstName": "' . $firstName . '", "lastName": "' . $lastName . '"},
                    "redirectUrl":{"success":"http://localhost/CODING/PHP-Paymaya/paymaya.php"},
                    "requestReferenceNumber": "5fc10b93-bdbd-4f31-b31d-4575a3785009",
                    "items": [
                        {"totalAmount": {"value": '.$total1.'}, "name": "'.$product1.'", "code": "CVG-096732","quantity":'.$quantity1.'},
                        {"totalAmount": {"value": '.$total2.'}, "name": "'.$product2.'", "code": "CVG-09234","quantity":'.$quantity2.'}
                    ]
                }',
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Basic cGstWjBPU3pMdkljT0kyVUl2RGhkVEdWVmZSU1NlaUdTdG5jZXF3VUU3bjBBaDo=',
                    'content-type' => 'application/json',
                ],
            ]);

            $paymongoResponse = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
                'body' => '{"data":{"attributes":{"send_email_receipt":false,
                    "show_description":true,
                    "show_line_items":true,
                    "success_url":"http://localhost/CODING/PHP-Paymaya/paymaya.php",
                    "line_items":[{"currency":"PHP","amount":'.($price1 * 100).',"name":"'.$product1.'","quantity":'.$quantity1.'},
                    {"currency":"PHP","amount":'.($price2 * 100).',"name":"'.$product2.'","quantity":'.$quantity2.'}],
                    "payment_method_types":["gcash", "paymaya"],"description":"Parking"}}}',
                'headers' => [
                  'Content-Type' => 'application/json',
                  'accept' => 'application/json',
                  'authorization' => 'Basic c2tfdGVzdF9FRXdZaWhySjFlNWdHZGRDWUFXZXJFWVU6',
                ],
              ]);

            $paymayaResponseData = json_decode($paymayaResponse->getBody(), true);
            $redirectUrlPaymaya = $paymayaResponseData["redirectUrl"];

            $paymongoResponseData = json_decode($paymongoResponse->getBody(), true);
            $dataPaymongo = $paymongoResponseData["data"];
            $attributePaymongo = $dataPaymongo["attributes"];
            $redirectUrlPaymongo = $attributePaymongo["checkout_url"];
        }
    ?>

    <?php if(!isset($redirectUrlPaymaya) || !isset($redirectUrlPaymongo)) : ?>
    <form action="" method="POST" class="form">
        <?php echo $entryTimeInMinutes?>
        <h1>Payment Gateway Testing API</h1>
        <?php echo "Time of transaction: ",$currentTime ?><br>
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" required><br>
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" required><br>
        <label for="product1">First Product Name:</label>
        <input type="text" name="firstProduct" required><br>
        <label for="product1Price">Price:</label>
        <input type="number" name="firstProductPrice" required><br>
        <label for="quantity1">Quantity:</label>
        <input type="number" name="quantity1" required><br>
        <label for="product2">Second Product Name:</label>
        <input type="text" name="secondProduct" required><br>
        <label for="product2Price">Price:</label>
        <input type="number" name="secondProductPrice" required><br>
        <label for="quantity2">Quantity:</label>
        <input type="number" name="quantity2" required><br>
        <button type="submit">Submit Form</button>
    </form>
    <?php endif; ?>

    <?php if (isset($redirectUrlPaymaya) || isset($redirectUrlPaymongo)) : ?>
        <div class="form">
            <a href="<?php echo $redirectUrlPaymaya ?>">
                <button type="button">Proceed to Checkout with Maya</button>
            </a>
            <a href="<?php echo $redirectUrlPaymongo?>">
                <button type="button">Proceed to Checkout with Paymongo</button>
            </a>
        </div>
    <?php endif; ?>
</body>
</html>