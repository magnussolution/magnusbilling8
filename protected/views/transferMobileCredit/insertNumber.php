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

<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Method')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'method', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'method') ?>
    <p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Method') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Number')) ?>
    <?php echo $form->textField(
        $modelTransferToMobile,
        'number',
        array(
            'class' => 'input',
            'id'      => 'number',
            'onkeyup' => 'showNext(this)',
        )
    ) ?>
    <?php echo $form->error($modelTransferToMobile, 'number') ?>
    <p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Number') ?></p>
</div>


<div class="controls" id="sendButton">
    <?php echo CHtml::submitButton(Yii::t('app', 'Next'), array(
        'class'   => 'button',
        'onclick' => "return button2(event)",
        'id'      => 'secondButton',
        'style'   => 'display:none;',
    ));
    ?>
    <input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">
</div>
<div class="controls" id="buttondivWait"></div>
<?php

$this->endWidget(); ?>


<script type="text/javascript">
    function showNext(value) {
        number = document.getElementById('number').value;


        if (number.length >= 7) {
            document.getElementById("secondButton").style.display = 'inline';
        } else {
            document.getElementById("secondButton").style.display = 'none';
        }

    }

    function button2(e) {

        document.getElementById("sendButton").style.display = 'none';
        document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";

    }
</script>

<style type="text/css">
    #contactform {
        margin: 0 auto;
        width: 720px;
        padding: 5px;
        background: #f0f0f0;
        overflow: auto;
        /* Border style */
        border: 1px solid #cccccc;
        -moz-border-radius: 7px;
        -webkit-border-radius: 7px;
        border-radius: 7px;
        /* Border Shadow */
        -moz-box-shadow: 2px 2px 2px #cccccc;
        -webkit-box-shadow: 2px 2px 2px #cccccc;
        box-shadow: 2px 2px 2px #cccccc;
    }

    .company__row {
        display: inline-block;
        -webkit-box-shadow: 0 0 5px 0 rgba(0, 0, 0, .1);
        box-shadow: 0 0 5px 0 rgba(0, 0, 0, .1);
        background-color: #fff;
        position: relative;
        vertical-align: top
    }

    .company__row:nth-child(odd) {
        margin-left: 0
    }

    .company__row:hover {
        background: #f7f7f7
    }

    .company__row input[type=radio] {
        display: none
    }


    .company__logo-container {
        text-align: center
    }

    .company__row--disabled .company__logo {
        filter: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg'><filter id='grayscale'><feColorMatrix type='matrix' values='0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0'/></filter></svg>#grayscale");
        filter: #999;
        -webkit-filter: grayscale(100%);
        -webkit-transition: all .6s ease;
        transition: all .6s ease;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        opacity: .41
    }

    .company__row {
        width: 23%;
        -webkit-box-shadow: none;
        box-shadow: none;
        padding: 20px 30px;
        cursor: pointer
    }

    .company__row:first-child,
    .company__row:nth-child(2) {
        border-top: 0 !important
    }

    .company__row:nth-child(odd) {
        border-right: 1px solid hsla(0, 0%, 80%, .25)
    }

    .company__row:nth-child(2n),
    .company__row:nth-child(odd) {
        border-top: 1px solid hsla(0, 0%, 80%, .25)
    }


    .company__list {
        -webkit-box-shadow: 0 1px 2px 0 rgba(0, 0, 0, .1);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, .1);
        -webkit-border-radius: 4px;
        border-radius: 4px;
        overflow: hidden
    }

    .company__logo {
        vertical-align: middle
    }

    .company__logo-container {
        height: 58px;
        line-height: 58px
    }

    .company__row--blank {
        height: 108px
    }
</style>