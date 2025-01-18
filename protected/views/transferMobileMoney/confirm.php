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

<br />


<?php //print_r($modelSendCreditProducts->getAttributes())
?>
<?php //print_r($_POST)
?>
<div class='field' id="aditionalInfo" style="display:inline; border:0">
	<label>Country:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['country'] ?></div>
	<label>Number:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['number'] ?></div>

	<label>Method</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['method'] ?></div>

	<label>Amount BDT:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['amountValuesBDT'] ?></div>
	<label>Amount to be collected EUR:</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo number_format($_POST['TransferToMobile']['amountValuesEUR'], 2) ?></div>
</div>

<?php echo $form->hiddenField($modelTransferToMobile, 'method', array('value' => $_POST['TransferToMobile']['method'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'country', array('value' => $_POST['TransferToMobile']['country'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'number', array('value' => $_POST['TransferToMobile']['number'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'amountValuesBDT', array('value' => $_POST['TransferToMobile']['amountValuesBDT'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'amountValuesEUR', array('value' => $_POST['TransferToMobile']['amountValuesEUR'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'product_id', array('value' => Yii::$app->session['id_product'])); ?>
<?php echo $form->hiddenField($modelTransferToMobile, 'confirmed', array('value' => 'ok')); ?>


<div class="controls" id="sendButton">
	<?php echo CHtml::submitButton(Yii::t('zii', 'CONFIRM'), array(
		'class'   => 'button',
		'onclick' => "button2(event)",
		'id'      => 'confirmButton'
	));
	?>
	<input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">
</div>
<div class="controls" id="buttondivWait"></div>


</div>

<?php

$this->endWidget(); ?>

<script type="text/javascript">
	function button2(e) {
		document.getElementById("sendButton").style.display = 'none';
		document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
	}
</script>