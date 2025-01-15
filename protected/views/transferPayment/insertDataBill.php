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
<br>

<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Method')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'method', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'method') ?>
</div>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Country')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'country', array('class' => 'input', 'id' => 'country', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'country') ?>
</div>

<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Type')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'type', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'type') ?>
</div>

<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Mobile number')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'phone', array('class' => 'input')) ?>
    <?php echo $form->error($modelTransferToMobile, 'phone') ?>
    <p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Mobile number') ?></p>
</div>







<?php if ($modelTransferToMobile->country == 'Senegal'): ?>
    <?php echo $form->hiddenField($modelTransferToMobile, 'zipcode', array('value' => '')); ?>
    <div class="field">
        <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Bill no')) ?>
        <?php echo $form->textField($modelTransferToMobile, 'number', array('class' => 'input')) ?>
        <?php echo $form->error($modelTransferToMobile, 'number') ?>
        <p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Contract no') ?></p>
    </div>
<?php else: ?>

    <div class="field">
        <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Contract no')) ?>
        <?php echo $form->textField($modelTransferToMobile, 'number', array('class' => 'input')) ?>
        <?php echo $form->error($modelTransferToMobile, 'number') ?>
        <p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Contract no') ?></p>
    </div>

    <div class="field">
        <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Distribution code')) ?>
        <?php echo $form->textField($modelTransferToMobile, 'zipcode', array('class' => 'input')) ?>
        <?php echo $form->error($modelTransferToMobile, 'zipcode') ?>
        <p class="hint"><?php echo Yii::t('app', 'Enter your') . ' ' . Yii::t('app', 'Distribution code') ?></p>
    </div>
<?php endif ?>



<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('app', 'Bill Date:')) ?>
    <?php
    $this->widget(
        'ext.jui.EJuiDateTimePicker',
        array(

            'model'     => $modelTransferToMobile,
            'attribute' => 'creationdate',
            'language'  => Yii::$app->language,
            'options'   => array(
                'dateFormat' => 'yy-mm-dd',
                'timeFormat' => '',
            ),
        )
    );

    ?>
    <?php echo $form->error($modelTransferToMobile, 'creationdate') ?>
</div>
<br>
<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, 'bill_amount', array('label' => 'Bill Amount (' . strtoupper(Yii::$app->session['currency_dest']) . ')')); ?>
    <?php echo $form->textField($modelTransferToMobile, 'bill_amount', array('id' => 'bill_amount', 'class' => 'input', 'onkeyup' => 'showPriceEUR()')) ?>
    <?php echo $form->error($modelTransferToMobile, 'bill_amount') ?>
    <p class="hint"><?php echo $amountDetails ?></p>
</div>



<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, 'amountValuesEUR', array('label' => 'Paid Amount (' . strtoupper(Yii::$app->session['currency_orig']) . ')')); ?>
    <?php echo $form->textField($modelTransferToMobile, 'amountValuesEUR', array(
        'id'       => 'amountValuesEUR',
        'class'    => 'input',
        'onkeyup'  => 'showPriceBDT()',
        'readonly' => true,
    )) ?>
    <?php echo $form->error($modelTransferToMobile, 'amountValuesEUR') ?>
</div>




<div class="controls" id="sendButton" style="display: none">
    <?php echo CHtml::submitButton(Yii::t('app', 'Next'), array(
        'class'   => 'button',
        'onclick' => "return button2(event)",
        'id'      => 'secondButton'
    ));
    ?>

    <input id='buying_price' class="button" style="display:none; width: 100px;" onclick="getBuyingPrice()" value="R" readonly>
</div>
<input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">
<div class="controls" id="buttondivWait"></div>
<?php

$this->endWidget(); ?>



<script type="text/javascript">
    document.getElementById('TransferToMobile_creationdate').value = '';

    function getBuyingPrice(argument) {



        valueAmoutBDT = document.getElementById('bill_amount').value;
        var id = 0;



        amountValuesEUR = document.getElementById('amountValuesEUR').value;

        if (document.getElementById('buying_price').value != 'R') {
            document.getElementById('buying_price').value = 'R';
        } else {


            var http = new XMLHttpRequest()

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById('buying_price').value = this.responseText;
                }
            };

            http.open("GET", "../../index.php/transferPayment/getBuyingPriceBill?id=" + id + "&valueAmoutBDT=" + valueAmoutBDT + "&valueAmoutEUR=" + amountValuesEUR + "&country=<?php echo isset($_POST['TransferToMobile']['country']) ? $_POST['TransferToMobile']['method'] : 0 ?>", true)
            http.send(null);

        }


    }


    function showPriceEUR() {



        bill_amount = document.getElementById('bill_amount').value;

        if (bill_amount > 0) {
            var http = new XMLHttpRequest()

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {


                    if (this.responseText == 'invalid') {
                        document.getElementById('sendButton').style.display = 'none';
                    } else {
                        document.getElementById('sendButton').style.display = 'inline';
                    }


                    document.getElementById('buying_price').style.display = 'inline';
                    document.getElementById('buying_price').value = 'R';
                    document.getElementById('amountValuesEUR').value = this.responseText;
                }
            };

            http.open("GET", "../../index.php/transferPayment/convertCurrency?currency=<?php echo Yii::$app->session['currency_dest'] ?>&amount=" + bill_amount + "&country=<?php echo $_POST['TransferToMobile']['country']; ?>&type=Bill", true)
            http.send(null);
        }


    }

    function showPriceBDT() {



        amountValuesEUR = document.getElementById('amountValuesEUR').value;


        if (amountValuesEUR > 0) {
            var http = new XMLHttpRequest()

            http.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {

                    if (this.responseText == 'invalid') {
                        document.getElementById('sendButton').style.display = 'none';
                    } else {
                        document.getElementById('sendButton').style.display = 'inline';
                    }

                    document.getElementById('bill_amount').value = this.responseText;
                    document.getElementById('buying_price').style.display = 'inline';
                    document.getElementById('buying_price').value = 'R';
                }
            };

            http.open("GET", "../../index.php/transferPayment/convertCurrency?currency=EUR&amount=" + amountValuesEUR + "&country=<?php echo $_POST['TransferToMobile']['country']; ?>&type=Bill", true)
            http.send(null);
        }


    }

    function button2(e) {
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