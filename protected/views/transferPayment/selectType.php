<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
<script src="https://plentz.github.io/jquery-maskmoney/javascripts/jquery.maskMoney.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function() {
        $('#amountfielEUR').maskMoney();
        $('#amountfielBDT').maskMoney({
            precision: 0,
            thousands: ''
        });
    })
</script>
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


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Country')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'country', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'country') ?>
</div>



<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Type')) ?>
    <div class="styled-select">
        <?php
        echo $form->dropDownList(
            $modelTransferToMobile,
            'type',
            $types,
            array(
                'empty'    => Yii::t('app', 'Select the operator'),
                'onchange' => "this.form.submit()",
            )
        ); ?>
        <?php echo $form->error($modelTransferToMobile, 'type') ?>
    </div>
</div>


<br>




<br>
<div class='field' id="divsellingPrice" style="display:none; border:0">
    <label>Selling Price</label>
    <div id="sellingPrice" class="input" style="border:0; width:650px"></div>
</div>

<div class="controls" id="sendButton">
    <?php echo CHtml::submitButton(Yii::t('app', $buttonName), array(
        'class' => 'button',
        'id'    => 'secondButton'
    ));
    ?>
    <input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">

</div>
<div class="controls" id="buttondivWait"></div>

<?php $this->endWidget(); ?>