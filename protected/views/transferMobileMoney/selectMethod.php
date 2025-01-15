<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
  <script src="https://plentz.github.io/jquery-maskmoney/javascripts/jquery.maskMoney.min.js" type="text/javascript"></script>
  <script type="text/javascript">$(function() {
    $('#amountfielEUR').maskMoney();
    $('#amountfielBDT').maskMoney({precision:0, thousands:''});
  })</script>
<?php

$form = $this->beginWidget('CActiveForm', array(
    'id'                   => 'contactform',
    'htmlOptions'          => array('class' => 'rounded'),
    'enableAjaxValidation' => false,
    'clientOptions'        => array('validateOnSubmit' => true),
    'errorMessageCssClass' => 'error',
));
?>

<br/>
<?php

$buttonName = 'Next';

$fieldOption = array('class' => 'input');

?>



<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Method')) ?>
    <div class="styled-select">
        <?php
echo $form->dropDownList($modelTransferToMobile, 'method',
    $methods,
    array(
        'empty'    => Yii::t('yii', 'Select the method'),
        'onchange' => "this.form.submit()",
    )); ?>
        <?php echo $form->error($modelTransferToMobile, 'method') ?>
    </div>
</div>



<br>


<br>
<div class='field' id="divsellingPrice" style="display:none; border:0">
    <label>Selling Price</label>
    <div id="sellingPrice" class="input" style="border:0; width:650px" ></div>
</div>

<div class="controls" id="sendButton">

<input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">

</div>
<div class="controls" id="buttondivWait"></div>

<?php $this->endWidget();?>




