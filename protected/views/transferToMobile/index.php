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


<?php

$buttonName = 'Next';

$fieldOption = array('class' => 'input');

?>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Method')) ?>
    <div class="styled-select">
        <?php echo $form->dropDownList(
            $modelTransferToMobile,
            'method',
            $methods,
            array(
                'empty'    => Yii::t('zii', 'Select the method'),
                'disabled' => strlen($modelTransferToMobile->country) > 3,
                'onchange' => "this.form.submit()",
            )
        ); ?>
        <?php echo $form->error($modelTransferToMobile, 'method') ?>
    </div>
</div>

<br>





</div>
<div class="controls" id="buttondivWait"></div>


<?php

$this->endWidget(); ?>