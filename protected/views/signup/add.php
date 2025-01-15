<?php header('Content-type: text/html; charset=utf-8'); ?>
<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />

<?php $form = $this->beginWidget('CActiveForm', array(
	'id'                   => 'contactform',
	'htmlOptions'          => array('class' => 'rounded'),
	'enableAjaxValidation' => false,
	'clientOptions'        => array('validateOnSubmit' => true),
	'errorMessageCssClass' => 'error',
)); ?>

<br />
<?php $plans = CHtml::listData($plan, 'id', 'name'); ?>

<?php if (count($plan) > 1): ?>
	<div class="field">
		<?php echo $form->labelEx($signup, Yii::t('app', 'Plan')) ?>
		<div class="styled-select">
			<?php echo $form->dropDownList($signup, 'id_plan', $plans, array('prompt' => Yii::t('app', 'Select a plan'))); ?>
		</div>
	</div>
	<br>
<?php elseif (count($plan) == 1): ?>
	<?php echo $form->hiddenField($signup, 'id_plan', array('value' => $plan[0]->id)); ?>

<?php elseif (count($plan) == 0): ?>
	<?php exit(Yii::t('app', 'No plans available for signup')) ?>
<?php endif; ?>

<?php echo $form->hiddenField($signup, 'ini_credit', array('value' => $plan[0]->ini_credit)); ?>

<?php echo $form->hiddenField($signup, 'id_user', array('value' => $plan[0]->id_user)); ?>


<?php if ($autoUser == 0): ?>
	<div class="field">
		<?php echo $form->labelEx($signup, Yii::t('app', 'Username')) ?>
		<?php echo $form->textField($signup, 'username', array('class' => 'input')) ?>
		<?php echo $form->error($signup, 'username') ?>
		<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Username') ?></p>
	</div>
<?php endif; ?>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'Email')) ?>
	<?php echo $form->textField($signup, 'email', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'email') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Email') ?></p>
</div>

<?php if (strlen($autoPassword) < 6): ?>
	<div class="field">
		<p>&nbsp;</p>
		<?php echo $form->labelEx($signup, Yii::t('app', 'Password')) ?>
		<?php echo $form->passwordField($signup, 'password', array('class' => 'input')) ?>
		<?php echo $form->error($signup, 'password') ?>
		<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Password') ?></p>
	</div>

	<div class="field">
		<?php echo $form->labelEx($signup, Yii::t('app', 'Confirm your password')) ?>
		<?php echo $form->passwordField($signup, 'password2', array('class' => 'input')) ?>
		<?php echo $form->error($signup, 'password2') ?>
		<p class="hint"><?php echo Yii::t('app', 'Confirm your password') ?></p>
		<p>&nbsp;</p>
	</div>
<?php else: ?>
	<?php echo $form->hiddenField($signup, 'password', array('value' => $autoPassword)); ?>
	<?php echo $form->hiddenField($signup, 'password2', array('value' => $autoPassword)); ?>
<?php endif; ?>



<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'First name')) ?>
	<?php echo $form->textField($signup, 'firstname', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'firstname') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'First name') ?></p>
</div>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'Last name')) ?>
	<?php echo $form->textField($signup, 'lastname', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'lastname') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Last name') ?></p>
</div>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'Zip code')) ?>
	<?php echo $form->textField($signup, 'zipcode', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'zipcode') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Zip code') ?></p>
</div>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'Address')) ?>
	<?php echo $form->textField($signup, 'address', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'address') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Address') ?></p>
</div>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'City')) ?>
	<?php echo $form->textField($signup, 'city', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'city') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'City') ?></p>
</div>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'Neighborhood')) ?>
	<?php echo $form->textField($signup, 'neighborhood', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'neighborhood') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Neighborhood') ?></p>
</div>

<?php if ($language == 'pt_BR'): ?>
	<div class="field">

		<?php $modelEstados = Estados::model()->findAll(); ?>
		<?php $estados      = CHtml::listData($modelEstados, 'sigla', 'nome'); ?>

		<?php echo $form->labelEx($signup, Yii::t('app', 'State')) ?>
		<div class="styled-select">
			<?php echo $form->dropDownList($signup, 'state', $estados, array('empty' => Yii::t('app', 'State'))); ?>
		</div>
	</div>
<?php else: ?>
	<div class="field">
		<?php echo $form->labelEx($signup, Yii::t('app', 'State')) ?>
		<?php echo $form->textField($signup, 'state', array('class' => 'input')) ?>
		<?php echo $form->error($signup, 'state') ?>
		<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'State') ?></p>
	</div>
<?php endif; ?>

<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'Phone')) ?>
	<?php echo $form->numberField($signup, 'phone', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'phone') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Phone') ?></p>
</div>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'Mobile')) ?>
	<?php echo $form->numberField($signup, 'mobile', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'mobile') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Mobile') ?></p>
</div>
<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'CPF/CNPJ')) ?>
	<?php echo $form->textField($signup, 'doc', array('class' => 'input')) ?>
	<?php echo $form->error($signup, 'doc') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'CPF/CNPJ') ?></p>
</div>

<?php if ($language == 'pt_BR'): ?>
	<div class="field">
		<?php echo $form->labelEx($signup, Yii::t('app', 'Company name')) ?>
		<?php echo $form->textField($signup, 'company_name', array('class' => 'input')) ?>
		<?php echo $form->error($signup, 'company_name') ?>
		<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Company name') ?></p>
	</div>
	<div class="field">
		<?php echo $form->labelEx($signup, Yii::t('app', 'State number')) ?>
		<?php echo $form->numberField($signup, 'state_number', array('class' => 'input')) ?>
		<?php echo $form->error($signup, 'state_number') ?>
		<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'State number') ?></p>
	</div>


<?php endif; ?>

<br>

<?php if (CCaptcha::checkRequirements()): ?>
	<div class="field">
		<?php echo $form->labelEx($signup, Yii::t('app', 'Verify code')); ?>
		<?php echo $form->textField($signup, 'verifyCode', array('class' => 'input')) ?>
		<?php echo $form->error($signup, 'verifyCode') ?>
		<p class="hint"><?php echo Yii::t('app', 'Enter the verification code shown') ?></p>
		<blockquote>
			<blockquote><?php $this->widget('CCaptcha'); ?> </blockquote>
		</blockquote>
	</div>
	<br>
<?php endif; ?>

<div class="field">
	<?php echo $form->labelEx($signup, Yii::t('app', 'I accept the terms')) ?>
	<?php echo $form->checkBox($signup, 'accept_terms', array('checked' => '')); ?>
	<?php echo $form->error($signup, 'accept_terms') ?>
	<p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'I accept the terms') ?></p>
</div>
<br>
<center><a href="<?php echo $termsLink ?>" target='_blank'><?php echo Yii::t('app', 'Terms') ?></a></center>
<br>

<?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'button')); ?>

<?php $this->endWidget(); ?>