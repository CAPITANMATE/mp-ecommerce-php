 <?php
    require_once 'vendor/autoload.php';


echo $_POST['transactionAmount'] . "<BR>"; 
echo $_POST['token'] . "<BR>"; 
echo $_POST['description'] . "<BR>"; 
echo $_POST['installments'] . "<BR>"; 
echo $_POST['paymentMethodId'] . "<BR>"; 
echo $_POST['issuer'] . "<BR>"; 
echo $_POST['email'] . "<BR>"; 
echo $_POST['docType'] . "<BR>"; 
echo $_POST['docNumber'] . "<BR>"; 
 
	MercadoPago\SDK::setAccessToken("TEST-4162999866747892-090313-50dc05b70bfeccafb2de5278f69c6c9d-325440040");
    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = (float)$_POST['transactionAmount'];
    $payment->token = $_POST['token'];
    $payment->description = $_POST['description'];
    $payment->installments = (int)$_POST['installments'];
    $payment->payment_method_id = $_POST['paymentMethodId'];
    $payment->issuer_id = (int)$_POST['issuer'];


 

    $payer = new MercadoPago\Payer();
    $payer->email = $_POST['email'];
    $payer->identification = array( 
        "type" => $_POST['docType'],
        "number" => $_POST['docNumber']
    );
    $payment->payer = $payer;

    $payment->save();

    $response = array(
        'status' => $payment->status,
        'status_detail' => $payment->status_detail,
        'id' => $payment->id
    );
    echo json_encode($response);

?>