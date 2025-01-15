insertdataPre

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

<label>Method</label>
<div id="aditionalInfoText" class="input" style="border:0; width:650px"><?php echo $_POST['TransferToMobile']['method'] ?></div>



<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Operator')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'method', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'method') ?>
    <p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'Operator') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Number')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'number', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($modelTransferToMobile, 'number') ?>
    <p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'number') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Meter')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'metric', array('class' => 'input')) ?>
    <?php echo $form->error($modelTransferToMobile, 'metric') ?>
    <p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'Meter') ?></p>
</div>
<?php echo $form->hiddenField($modelTransferToMobile, 'method', array('value' => $selectedMethod)); ?>



<div class="sp-page companies__content">
    <div class="company__list" id='productList'>
        <?php $id = 0; ?>
        <?php foreach (Yii::$app->session['amounts'] as $key => $value): ?>
            <label for="2" class="company__row" id="productLabel<?php echo $id ?>">
                <input type="radio" id="productinput<?php echo $id ?>" name="amountValues" value="<?php echo $key ?>">
                <div class="company__logo-container" onclick="handleChange1(<?php echo $id ?>,<?php echo count(Yii::$app->session['amounts']) ?>);" id='product<?php echo $id ?>'><?php echo $value ?></div>
            </label>
            <?php $id++; ?>
        <?php endforeach; ?>

    </div>
</div>




<div class="controls" id="sendButton">
    <?php echo CHtml::submitButton(Yii::t('yii', 'next'), array(
        'class'   => 'button',
        'onclick' => "return button2(event)",
        'id'      => 'secondButton'
    ));
    ?>
    <input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">
    <input id='buying_price' class="button" style="display:none; width: 100px;" onclick="getBuyingPrice()" value="R" readonly>
</div>
<div class="controls" id="buttondivWait"></div>
<?php

$this->endWidget(); ?>


<script type="text/javascript">
    function handleChange1(argument, total) {

        for (var i = 0; i < total; i++) {
            document.getElementById('productLabel' + i).style.backgroundColor = '#fff';
        }
        document.getElementById('productLabel' + argument).style.backgroundColor = 'dd8980';

        document.getElementById('productinput' + argument).checked = true;
        window.productInputSelected = argument

        document.getElementById('buying_price').style.display = 'inline';
        document.getElementById('buying_price').value = 'R';

        idProduct = document.getElementById('productinput' + argument).value;
        var http = new XMLHttpRequest()
        http.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById('aditionalInfo').style.display = 'inline';
                document.getElementById('aditionalInfoText').innerHTML = this.responseText;
            }
        }

        http.open("GET", "../../index.php/TransferMobileCredit/getProductTax?id=" + idProduct);
        http.send(null);
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