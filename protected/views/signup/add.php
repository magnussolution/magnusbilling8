<?php

use app\models\Estados;
use app\assets\AppAsset;
use yii\bootstrap4\Html;
use yii\widgets\ActiveForm;


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


	<?php $form = ActiveForm::begin([
		'id' => 'contactform',
		'options' => ['class' => 'rounded'],
		'enableAjaxValidation' => true,

	]); ?>

	<br />
	<?php $plans = \yii\helpers\ArrayHelper::map($plan, 'id', 'name'); ?>

	<?php if (count($plan) > 1): ?>
		<div class="field">
			<?php echo $form->labelEx($signup, Yii::t('zii', 'Plan')) ?>
			<div class="styled-select">
				<?php echo $form->dropDownList($signup, 'id_plan', $plans, array('prompt' => Yii::t('zii', 'Select a plan'))); ?>
			</div>
		</div>
		<br>
	<?php elseif (count($plan) == 1): ?>
		<?php echo $form->field($signup, 'id_plan')->hiddenInput(['value' => $plan[0]->id])->label(false); ?>

	<?php elseif (count($plan) == 0): ?>
		<?php exit(Yii::t('zii', 'No plans available for signup')) ?>
	<?php endif; ?>

	<?php echo $form->field($signup, 'ini_credit')->hiddenInput(['value' => $plan[0]->ini_credit])->label(false); ?>

	<?php echo $form->field($signup, 'id_user')->hiddenInput(['value' => $plan[0]->id_user])->label(false); ?>


	<?php if ($autoUser == 0): ?>
		<div class="field">
			<?php echo $form->field($signup, 'username')->textInput(['class' => 'input']) ?>
			<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Username') ?></p>
		</div>
	<?php endif; ?>
	<div class="field">
		<?php echo $form->field($signup, 'email')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Email') ?></p>
	</div>

	<?php if (strlen($autoPassword) < 6): ?>
		<div class="field">
			<p>&nbsp;</p>
			<?php echo $form->field($signup, 'password')->passwordInput(['class' => 'input']) ?>
			<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Password') ?></p>
		</div>

		<div class="field">
			<?php echo $form->field($signup, 'password2')->passwordInput(['class' => 'input']) ?>
			<p class="hint"><?php echo Yii::t('zii', 'Confirm your password') ?></p>
			<p>&nbsp;</p>
		</div>
	<?php else: ?>
		<?php echo $form->field($signup, 'password')->hiddenInput(['value' => $autoPassword])->label(false); ?>
		<?php echo $form->field($signup, 'password2')->hiddenInput(['value' => $autoPassword])->label(false); ?>
	<?php endif; ?>


	<div class="field">
		<?php echo $form->field($signup, 'firstname')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'First name') ?></p>
	</div>
	<div class="field">
		<?php echo $form->field($signup, 'lastname')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Last name') ?></p>
	</div>
	<div class="field">
		<?php echo $form->field($signup, 'zipcode')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Zip code') ?></p>
	</div>
	<div class="field">
		<?php echo $form->field($signup, 'address')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Address') ?></p>
	</div>
	<div class="field">
		<?php echo $form->field($signup, 'city')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'City') ?></p>
	</div>
	<div class="field">
		<?php echo $form->field($signup, 'neighborhood')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Neighborhood') ?></p>
	</div>

	<?php if ($language == 'pt_BR'): ?>
		<div class="field">
			<?php $modelEstados = Estados::find()->all(); ?>
			<?php $estados = \yii\helpers\ArrayHelper::map($modelEstados, 'sigla', 'nome'); ?>

			<?php echo $form->field($signup, 'state')->dropDownList($estados, ['prompt' => Yii::t('zii', 'Select a state')]); ?>
		</div>
	<?php else: ?>
		<div class="field">
			<?php echo $form->field($signup, 'state')->textInput(['class' => 'input']) ?>
			<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'State') ?></p>
		</div>
	<?php endif; ?>

	<div class="field">
		<?php echo $form->field($signup, 'phone')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Phone') ?></p>
	</div>
	<div class="field">
		<?php echo $form->field($signup, 'mobile')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Mobile') ?></p>
	</div>
	<div class="field">
		<?php echo $form->field($signup, 'doc')->textInput(['class' => 'input']) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'CPF/CNPJ') ?></p>
	</div>

	<?php if ($language == 'pt_BR'): ?>
		<div class="field">
			<?php echo $form->field($signup, 'company_name')->textInput(['class' => 'input']) ?>
			<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Company name') ?></p>
		</div>
		<div class="field">
			<?php echo $form->field($signup, 'state_number')->textInput(['class' => 'input']) ?>
			<p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'State number') ?></p>
		</div>
	<?php endif; ?>

	<br>






	<div class="field">
		<?php echo $form->field($signup, 'captcha')->widget(\yii\captcha\Captcha::class) ?>
		<p class="hint"><?php echo Yii::t('zii', 'Enter the verification code') ?></p>
	</div>


	<div class="field">
		<?php echo $form->field($signup, 'accept_terms')->checkbox(['label' => Yii::t('zii', 'I accept the terms')]); ?>
		<p class="hint"><?php echo Yii::t('zii', 'You must accept the terms to proceed') ?></p>
	</div>
	<br>
	<center><a href="<?php echo $termsLink ?>" target='_blank'><?php echo Yii::t('zii', 'Terms') ?></a></center>
	<br>
	<div class="form-group">
		<?php echo Html::submitButton(Yii::t('zii', 'Save'), ['class' => 'btn btn-primary']); ?>
	</div>

	<?php ActiveForm::end(); ?>

	<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
<?php die(); ?>