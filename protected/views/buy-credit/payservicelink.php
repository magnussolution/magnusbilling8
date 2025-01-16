<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2023 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */


use app\assets\AppAsset;
use yii\bootstrap4\Html;


AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php $this->registerCsrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
	<?php $this->beginBody() ?>



	<style type="text/css">
		.styled-select {
			width: 450px;
		}

		form {
			margin: 0 auto;
		}
	</style>
	<dir align=center>
		<?php if (file_exists("resources/images/logo_custom.png")): ?>
			<img width="200" src="../../resources/images/logo_custom.png">
		<?php else: ?>
			<img src="../../resources/images/logo.png">
		<?php endif; ?>
	</dir>
	<?php $form = $this->beginWidget('CActiveForm', array(
		'id'                   => 'contactform',
		'htmlOptions'          => array('class' => 'rounded'),
		'enableAjaxValidation' => false,
		'clientOptions'        => array('validateOnSubmit' => true),
		'errorMessageCssClass' => 'error',
	)); ?>

	<br />

	<?php if (isset($message)):
		echo Yii::t('app', $message);
	?>
	<?php else: ?>



		<?php $modelMethodPay = CHtml::listData($modelMethodPay, 'id', 'show_name'); ?>


		<div class="field">
			<?php echo $form->labelEx($model, Yii::t('app', 'Method pay')) ?>
			<div class="styled-select" style="width: 380px">
				<?php echo $form->dropDownList(
					$model,
					'id_method',
					$modelMethodPay,
					array(
						'prompt' => Yii::t('app', 'Select a method'),
					)
				); ?>
			</div>
		</div>
		<br>
		<?php $totalPrice = 0; ?>
		<?php for ($i = 0; $i < count($modelServicesUse); $i++): ?>
			<?php $totalPrice += $modelServicesUse[$i]->idServices->price ?>
			<div class="field">

				<?php echo $form->labelEx($model, Yii::t('app', 'Service') . ' ' . $modelServicesUse[$i]->idServices->name) ?>
				<?php echo $form->textField($model, 'service0', array(
					'class'    => 'input',
					'value'    => $currency . ' ' . number_format($modelServicesUse[$i]->idServices->price, 2),
					'readOnly' => true,
				))
				?>

			</div>
			<?php if (strlen($modelServicesUse[$i]->idServices->name) > 40): ?>
				<br>
			<?php endif; ?>
			<br><br>
		<?php endfor; ?>
		<br>

		<?php if ($modelServicesUse[0]->idUser->credit > 0): ?>
			<div class="field">
				<?php echo $form->labelEx($model, Yii::t('app', 'Your credit')) ?>
				<?php echo $form->checkBox($model, 'use_credit', array('checked' => false)); ?>
				<?php echo ' ' . $currency . ' ' . number_format($modelServicesUse[0]->idUser->credit, 2) . ' ' . Yii::t('app', 'Use that') ?>

			</div>
		<?php endif; ?>
		<br><br><br>

		<div class="field">
			<?php echo $form->labelEx($model, Yii::t('app', 'Total Price')) ?>
			<?php echo $form->textField($model, 'total', array(
				'class'    => 'input',
				'value'    => $currency . ' ' . number_format($totalPrice, 2),
				'readOnly' => true,
			)) ?>
			<p>&nbsp;</p>
		</div>

		<?php echo CHtml::submitButton(Yii::t('app', 'Pay Now'), array('class' => 'button')); ?>
	<?php endif; ?>
	<?php $this->endWidget(); ?>


	?>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
<?php die(); ?>