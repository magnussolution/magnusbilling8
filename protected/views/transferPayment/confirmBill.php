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
	<label>Method:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->method ?></div>
	<label>Country:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->country ?></div>
	<label>Type:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->type ?></div>
	<label>Mobile Number:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->phone ?></div>

	<?php if ($this->modelTransferToMobile->country != 'Senegal'): ?>

		<label>Contract No:</label>
		<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->number ?></div>
		<label>Distribution code:</label>
		<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->zipcode ?></div>
	<?php else: ?>
		<label>Bill No:</label>
		<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->number ?></div>
	<?php endif ?>


	<label>Bill Date:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->creationdate ?></div>
	<br>
	<label>Bill amount (<?php echo Yii::$app->session['currency_dest'] ?>):</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $this->modelTransferToMobile->bill_amount ?></div>
	<label>Paid Amount (<?php echo Yii::$app->session['currency_orig'] ?>):</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo Yii::$app->session['sell_price'] ?></div>

</div>



</div>
<?php echo $form->hiddenField($modelTransferToMobile, 'country', array('value' => $this->modelTransferToMobile->country)); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'type', array('value' => $this->modelTransferToMobile->type)); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'number', array('value' => $this->modelTransferToMobile->number)); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'bill_amount', array('value' => $this->modelTransferToMobile->bill_amount)); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'zipcode', array('value' => $this->modelTransferToMobile->zipcode)); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'creationdate', array('value' => $this->modelTransferToMobile->creationdate)); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'phone', array('value' => $this->modelTransferToMobile->phone)); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'confirmed', array('value' => 'ok')); ?>


<div class="controls" id="sendButton">
	<?php echo CHtml::submitButton(Yii::t('zii', 'CONFIRM'), array(
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
	Product:<?php echo $this->modelTransferToMobile->amountValuesBDT ?><br>
	Meter: <?php echo $this->modelTransferToMobile->meter ?><br>

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