<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>

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
<?php

$buttonName = 'Next';

$fieldOption = array('class' => 'input');

?>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Method')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'method', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'method') ?>
    <p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Method') ?></p>
</div>


<?php

$modelSendCreditProduct = SendCreditProducts::model()->findAll(array(
    'condition' => ' type = :key1',
    'params'    => array(
        ':key1' => 'Mobile money',
    ),
    'group'     => 'country',
));

$country = CHtml::listData($modelSendCreditProduct, 'country', 'country');

?>

<?php if (count($country) > 0): ?>
    <div class="field">
        <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Country')) ?>
        <div class="styled-select">
            <?php echo $form->dropDownList(
                $modelTransferToMobile,
                'country',
                $country,
                array(
                    'prompt'   => Yii::t('app', 'Select a country'),
                    'onchange' => "this.form.submit()",
                )
            ); ?>
        </div>
    </div>
<?php endif; ?>





<input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">

</div>
<div class="controls" id="buttondivWait"></div>

<?php $this->endWidget(); ?>