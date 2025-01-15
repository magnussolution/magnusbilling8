<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />

<?php

$form = $this->beginWidget('CActiveForm', array(
	'id'                   => 'contactform',
	'htmlOptions'          => array('class' => 'rounded'),
	'enableAjaxValidation' => false,
	'clientOptions'        => array('validateOnSubmit' => true),
	'errorMessageCssClass' => 'error',
));
?>

<?php

if (isset($_POST['TransferToMobile']['metric']) && strlen($_POST['TransferToMobile']['metric'])) {
	$metric_operator_name = SendCreditOrange2::checkMetric($_POST['TransferToMobile']['metric']);

	if ($metric_operator_name === false) {
		echo '<div align=center id="container">';
		echo '<font color=red>The Meter number is invalid. Please try again</font>';
		echo "<br>";
		echo '<a href="../../index.php/transferToMobile/read">Start new request </a>' . "<br><br>";
		echo '</div>';

		exit;
	}
}

?>


<br />


<div class='field' id="aditionalInfo" style="display:inline; border:0">
	<label>Country:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['country'] ?></div>
	<label>Number:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->number ?></div>
	<label>Operator:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['operator'] ?></div>
	<label>Product:</label>
	<?php if (preg_match_all('/-/', $modelSendCreditRates->idProduct->product) && isset($_POST['TransferToMobile']['amountValuesBDT']) && $_POST['TransferToMobile']['amountValuesBDT'] > 0): ?>

		<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $modelSendCreditRates->idProduct->currency_dest . ' ' . $_POST['TransferToMobile']['amountValuesBDT'] ?></div>
	<?php else: ?>

		<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $modelSendCreditRates->idProduct->currency_dest . ' ' . $modelSendCreditRates->idProduct->product ?></div>
	<?php endif ?>
	<label>Amount to be collected:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $modelSendCreditRates->idProduct->currency_orig . ' ' . number_format($modelSendCreditRates->sell_price, 2) ?></div>


	<?php if ($_POST['TransferToMobile']['metric'] != ''): ?>

		<label>Meter:</label>
		<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['metric'] ?></div>
		<label>Meter owner name:</label>
		<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $metric_operator_name ?></div>
		<?php echo $form->hiddenField($modelTransferToMobile, 'metric', array('value' => $_POST['TransferToMobile']['metric'])); ?>
		<?php echo $form->hiddenField($modelTransferToMobile, 'metric_operator_name', array('value' => $metric_operator_name)); ?>
</div>

<?php endif; ?>

</div>

<?php echo $form->hiddenField($modelTransferToMobile, 'number', array('value' => $_POST['TransferToMobile']['number'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'country', array('value' => $_POST['TransferToMobile']['country'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'operator', array('value' => $_POST['TransferToMobile']['operator'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'amountValues', array('value' => $_POST['TransferToMobile']['amountValues'])); ?>

<?php if (isset($_POST['TransferToMobile']['amountValuesBDT'])): ?>
	<?php echo $form->hiddenField($modelTransferToMobile, 'amountValuesBDT', array('value' => $_POST['TransferToMobile']['amountValuesBDT'])); ?>
<?php endif ?>
<?php if (isset($_POST['TransferToMobile']['amountValuesEUR'])): ?>
	<?php echo $form->hiddenField($modelTransferToMobile, 'amountValuesEUR', array('value' => $_POST['TransferToMobile']['amountValuesEUR'])); ?>
<?php endif ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'confirmed', array('value' => 'ok')); ?>


<div class="controls" id="sendButton">
	<?php echo CHtml::submitButton(Yii::t('app', 'CONFIRM'), array(
		'class'   => 'button',
		'onclick' => "return button2(event)",
		'id'      => 'confirmButton'
	));
	?>
	<a href="javascript:printDiv('printdiv');">Print</a>
	<input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">


</div>
<div class="controls" id="buttondivWait"></div>


</div>
<div style="display:none; border:0" id='printdiv'>
	Confirm?<br><br>
	Country: <?php echo $_POST['TransferToMobile']['country'] ?><br>
	Number: <?php echo $_POST['TransferToMobile']['number'] ?><br>
	Product:<?php echo $modelSendCreditRates->idProduct->currency_dest . ' ' . $modelSendCreditRates->idProduct->product ?><br>
	<?php if ($_POST['TransferToMobile']['metric'] != ''): ?>
		Meter: <?php echo $_POST['TransferToMobile']['metric'] ?><br>
	<?php endif; ?>
</div>

<?php

$this->endWidget(); ?>

<script type="text/javascript">
	function printDiv(divName) {
		var printContents = document.getElementById(divName).innerHTML;
		var originalContents = document.body.innerHTML;

		document.body.innerHTML = printContents;

		window.print();

		setInterval(function() {
			document.body.innerHTML = originalContents;
		}, 3000);




	}

	function button2(e) {


		document.getElementById("sendButton").style.display = 'none';
		document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";



	}
</script>