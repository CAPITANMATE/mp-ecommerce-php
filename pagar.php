 <?php
    require_once 'vendor/autoload.php';

 
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
  <!DOCTYPE html>
<html>
    <head>
        <title>E-commerce sample</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=1024">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="format-detection" content="telephone=no">
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> 
        
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>

        <link rel="stylesheet" href="/assets/custom.css" type="text/css">
		
		
    </head>

    <body>
	<script>
	window.Mercadopago.setPublishableKey("TEST-999d8c20-8c77-4ec2-a106-38154e40c4f5");
	window.Mercadopago.getIdentificationTypes();
	document.getElementById('cardNumber').addEventListener('change', guessPaymentMethod);

	function guessPaymentMethod(event) {
	   let cardnumber = document.getElementById("cardNumber").value;
	   if (cardnumber.length >= 6) {
		   let bin = cardnumber.substring(0,6);
		   window.Mercadopago.getPaymentMethod({
			   "bin": bin
		   }, setPaymentMethod);
	   }
	};

	function setPaymentMethod(status, response) {
	   if (status == 200) {
		   let paymentMethod = response[0];
		   document.getElementById('paymentMethodId').value = paymentMethod.id;

		   if(paymentMethod.additional_info_needed.includes("issuer_id")){
			   getIssuers(paymentMethod.id);
		   } else {
			   getInstallments(
				   paymentMethod.id,
				   document.getElementById('transactionAmount').value
			   );
		   }
	   } else {
		   alert(`payment method info error: ${response}`);
	   }
	}
	function getIssuers(paymentMethodId) {
	   window.Mercadopago.getIssuers(
		   paymentMethodId,
		   setIssuers
	   );
	}

	function setIssuers(status, response) {
	   if (status == 200) {
		   let issuerSelect = document.getElementById('issuer');
		   response.forEach( issuer => {
			   let opt = document.createElement('option');
			   opt.text = issuer.name;
			   opt.value = issuer.id;
			   issuerSelect.appendChild(opt);
		   });

		   getInstallments(
			   document.getElementById('paymentMethodId').value,
			   document.getElementById('transactionAmount').value,
			   issuerSelect.value
		   );
	   } else {
		   alert(`issuers method info error: ${response}`);
	   }
	}
	function getInstallments(paymentMethodId, transactionAmount, issuerId){
	   window.Mercadopago.getInstallments({
		   "payment_method_id": paymentMethodId,
		   "amount": parseFloat(transactionAmount),
		   "issuer_id": issuerId ? parseInt(issuerId) : undefined
	   }, setInstallments);
	}

	function setInstallments(status, response){
	   if (status == 200) {
		   document.getElementById('installments').options.length = 0;
		   response[0].payer_costs.forEach( payerCost => {
			   let opt = document.createElement('option');
			   opt.text = payerCost.recommended_message;
			   opt.value = payerCost.installments;
			   document.getElementById('installments').appendChild(opt);
		   });
	   } else {
		   alert(`installments method info error: ${response}`);
	   }
	}
	doSubmit = false;
	document.getElementById('paymentForm').addEventListener('submit', getCardToken);
	function getCardToken(event){
	   event.preventDefault();
	   if(!doSubmit){
		   let $form = document.getElementById('paymentForm');
		   window.Mercadopago.createToken($form, setCardTokenAndPay);
		   return false;
	   }
	};

	function setCardTokenAndPay(status, response) {
	   if (status == 200 || status == 201) {
		   let form = document.getElementById('paymentForm');
		   let card = document.createElement('input');
		   card.setAttribute('name', 'token');
		   card.setAttribute('type', 'hidden');
		   card.setAttribute('value', response.id);
		   form.appendChild(card);
		   doSubmit=true;
		   form.submit();
	   } else {
		   alert("Verify filled data!\n"+JSON.stringify(response, null, 4));
	   }
	};

	</script>
        <div id="content-wrap">
            <form action="/process_payment" method="post" id="paymentForm">
   <h3>Detalles del comprador</h3>
     <div>
       <div>
         <label for="email">E-mail</label>
         <input id="email" name="email" type="text" value="test@test.com"></select>
       </div>
       <div>
         <label for="docType">Tipo de documento</label>
         <select id="docType" name="docType" data-checkout="docType" type="text"></select>
       </div>
       <div>
         <label for="docNumber">Número de documento</label>
         <input id="docNumber" name="docNumber" data-checkout="docNumber" type="text"/>
       </div>
     </div>
   <h3>Detalles de la tarjeta</h3>
     <div>
       <div>
         <label for="cardholderName">Titular de la tarjeta</label>
         <input id="cardholderName" data-checkout="cardholderName" type="text">
       </div>
       <div>
         <label for="">Fecha de vencimiento</label>
         <div>
           <input type="text" placeholder="MM" id="cardExpirationMonth" data-checkout="cardExpirationMonth"
             onselectstart="return false" onpaste="return false"
             oncopy="return false" oncut="return false"
             ondrag="return false" ondrop="return false" autocomplete=off>
           <span class="date-separator">/</span>
           <input type="text" placeholder="YY" id="cardExpirationYear" data-checkout="cardExpirationYear"
             onselectstart="return false" onpaste="return false"
             oncopy="return false" oncut="return false"
             ondrag="return false" ondrop="return false" autocomplete=off>
         </div>
       </div>
       <div>
         <label for="cardNumber">Número de la tarjeta</label>
         <input type="text" id="cardNumber" data-checkout="cardNumber"
           onselectstart="return false" onpaste="return false"
           oncopy="return false" oncut="return false"
           ondrag="return false" ondrop="return false" autocomplete=off>
       </div>
       <div>
         <label for="securityCode">Código de seguridad</label>
         <input id="securityCode" data-checkout="securityCode" type="text"
           onselectstart="return false" onpaste="return false"
           oncopy="return false" oncut="return false"
           ondrag="return false" ondrop="return false" autocomplete=off>
       </div>
       <div id="issuerInput">
         <label for="issuer">Banco emisor</label>
         <select id="issuer" name="issuer" data-checkout="issuer"></select>
       </div>
       <div>
         <label for="installments">Cuotas</label>
         <select type="text" id="installments" name="installments"></select>
       </div>
       <div>
         <input type="hidden" name="transactionAmount" id="transactionAmount" value="100" />
         <input type="hidden" name="paymentMethodId" id="paymentMethodId" />
         <input type="hidden" name="description" id="description" />
         <br>
         <button type="submit">Pagar</button>
         <br>
       </div>
   </div>
 </form>
        </div>
    </body>
</html>