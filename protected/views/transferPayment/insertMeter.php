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
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Method')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'method', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'method') ?>
</div>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Country')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'country', array('class' => 'input', 'id' => 'country', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'country') ?>
</div>

<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Type')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'type', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'type') ?>
</div>

<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Amount')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'amountValuesBDT', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'amountValuesBDT') ?>
</div>



<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Meter number')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'meter', array('class' => 'input', 'id' => 'meter')) ?>
    <?php echo $form->error($modelTransferToMobile, 'meter') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Meter') ?></p>
</div>


<div class="controls" id="sendButton">
    <?php echo CHtml::submitButton(Yii::t('zii', 'Next'), array(
        'class'   => 'button',
        'onclick' => "return button2(event)",
        'style'   => 'display: none;',
        'id'      => 'secondButton'
    ));
    ?>
    <input class="button" style="width: 80px; " onclick="window.location='../../index.php/TransferToMobile/read';" value="Cancel">
    <input id='check_number' class="button" onclick="checknumber()" value="Check number" readonly>
</div>
<div class="controls" id="buttondivWait"></div>
<?php

$this->endWidget(); ?>


<script type="text/javascript">
    function checknumber() {
        meter = document.getElementById('meter').value;
        country = document.getElementById('country').value;
        document.getElementById("secondButton").style.display = 'none';

        countries = ['Benin',
            'Burkina Faso',
            'Ivory Coast',
            'Gambia',
            'Ghana',
            'Guinea',
            'Guinea Bissau',
            'Mali',
            'Mauritania',
            'Morocco',
            'Nigeria',
            'Senegal',
            'Sierra Leone',
            'Togo'
        ];

        if (!countries.includes(country)) {
            document.getElementById("secondButton").style.display = 'inline';

            return;
        }
        if (meter > 0) {
            document.getElementById("check_number").style.display = 'none';
            document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
            var http = new XMLHttpRequest()

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    alert("Meter owner is: " + this.responseText);
                    document.getElementById("check_number").style.display = 'inline';
                    document.getElementById("buttondivWait").innerHTML = "";

                    if (this.responseText != 'The Meter number is invalid. Please try again') {
                        document.getElementById("secondButton").style.display = 'inline';
                    }
                }
            };

            http.open("GET", "../../index.php/transferPayment/checkNumber?meter=" + meter + "&country=" + country, true)
            http.send(null);
        } else {
            alert("Meter need be numeric")
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