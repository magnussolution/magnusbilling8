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

$fieldOption['readonly'] = true;

?>


<br>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Number')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'number', $fieldOption) ?>
    <?php echo $form->error($modelTransferToMobile, 'number') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Number') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Country')) ?>
    <?php echo $form->textField($modelTransferToMobile, 'country', $fieldOption) ?>
    <?php echo $form->error($modelTransferToMobile, 'country') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Country') ?></p>
</div>



<?php

$modelSendCreditProducts = \app\models\SendCreditProducts::find()
    ->where(['country' => $modelTransferToMobile->country, 'type' => 'Mobile Credit', 'status' => 1])
    ->groupBy('operator_name')
    ->all();

$operators = CHtml::listData($modelSendCreditProducts, 'operator_name', 'operator_name'); ?>
<div class="field">
    <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Operator')) ?>
    <div class="styled-select">
        <?php echo $form->dropDownList(
            $modelTransferToMobile,
            'operator',
            $operators,
            array(
                'empty'    => Yii::t('zii', 'Select the operator'),
                'options'  => array(isset($_POST['TransferMobileCredit']['operator_name']) && strlen($_POST['TransferMobileCredit']['operator_name']) > 2 ? $_POST['TransferMobileCredit']['operator_name'] : null => array('selected' => true)),
                'onchange' => 'showProducts()',
                'id'       => 'operatorfield',
            )
        ); ?>
    </div>
</div>


<?php if (isset($_POST['TransferMobileCredit']['operator_name']) && ($_POST['TransferMobileCredit']['operator_name'] == 'SENELEC - Senegal' || $_POST['TransferMobileCredit']['operator_name'] == 'NAWEC - Gambia' || $_POST['TransferMobileCredit']['operator_name'] == 'EDM - Mali' || $_POST['TransferMobileCredit']['operator_name'] == 'EEDC - Nigeria')): ?>

    use app\models\SendCreditProducts;
    <div class="field" id='metric'>
    <?php else: ?>
        <div class="field" id='metric' style="display:none; border:0">
        <?php endif ?>

        <?php echo $form->labelEx($modelTransferToMobile, Yii::t('zii', 'Meter')) ?>
        <?php echo $form->textField($modelTransferToMobile, 'metric', array('class' => 'input')) ?>
        <?php echo $form->error($modelTransferToMobile, 'metric') ?>
        <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Meter') ?></p>
        </div>

        <?php if (Yii::$app->session['is_interval'] == true): ?>

            <div id='is_interval'>
                <br>

                <div class="field">
                    <?php echo $form->labelEx($modelTransferToMobile, 'amountValuesEUR', array('label' => 'Paid Amount (EUR)')); ?>
                    <?php echo $form->textField(
                        $modelTransferToMobile,
                        'amountValuesEUR',
                        array(
                            'class'   => 'input',
                            'id'      => 'amountfielEUR',
                            'onkeyup' => 'showPriceEUR(' . Yii::$app->session['interval_product_retail_price'] . ')',
                            'style'   => 'color:blue; font-size:20',
                        )
                    ) ?>
                    <?php echo $form->error($modelTransferToMobile, 'amountValuesEUR') ?>

                </div>

                <div class="field">
                    <?php echo $form->labelEx($modelTransferToMobile, 'amountValuesBDT', array('label' => 'Receive Amount (' . Yii::$app->session['interval_currency'] . ')')); ?>
                    <?php echo $form->textField(
                        $modelTransferToMobile,
                        'amountValuesBDT',
                        array(
                            'class'   => 'input',
                            'id'      => 'amountfielBDT',
                            'onkeyup' => 'showPriceBDT(' . Yii::$app->session['interval_product_retail_price'] . ')',
                            'style'   => 'color:blue; font-size:20',
                        )
                    ) ?>
                    <?php echo $form->error($modelTransferToMobile, 'amountValuesBDT') ?>
                    <p class="hint"><?php echo $amountDetails ?></p>
                </div>

            </div>

            <br><br>
        <?php endif ?>


        <div class="sp-page companies__content">
            <div class="company__list" id='productList'>
                <?php $id = 0; ?>
                <?php foreach (Yii::$app->session['amounts'] as $key => $value): ?>
                    <label for="2" class="company__row" id="productLabel<?php echo $id ?>">
                        <input type="radio" id="productinput<?php echo $id ?>" name="amountValues" value="<?php echo $key ?>">
                        <div class="company__logo-container" onclick="handleChange1(<?php echo $id ?>,<?php echo $id + 1 ?>);" id='product<?php echo $id ?>'><?php echo $value ?></div>
                    </label>
                    <?php $id++; ?>
                <?php endforeach; ?>

            </div>
        </div>




        <div class='field' id="aditionalInfo" style="display:none; border:0">
            <label>Additional Info</label>
            <div id="aditionalInfoText" class="input" style="border:0; width:650px"></div>
        </div>

        <div class="controls" id="sendButton">
            <?php echo CHtml::submitButton(Yii::t('zii', $buttonName), array(
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
            operator = document.getElementById('operatorfield').options[document.getElementById('operatorfield').selectedIndex].text;

            if (operator == 'Select the operator') {
                document.getElementById('secondButton').style.display = 'none';
                document.getElementById('is_interval').style.display = 'none';

            }

            function getMaxMin(amount) {
                var http = new XMLHttpRequest()

                http.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        //document.getElementById('buying_price').value = this.responseText;
                        console(this.responseText);
                    }
                };

                http.open("GET", "../../index.php/TransferMobileCredit/getMaxMin?valueAmoutBDT=" + amount, true)
                http.send(null);
            }

            function showPriceEUR() {

                operator = document.getElementById('operatorfield').options[document.getElementById('operatorfield').selectedIndex].text;
                for (var i = 0; i < 50; i++) {
                    if (document.getElementById('productLabel' + i)) {
                        document.getElementById('productLabel' + i).style.backgroundColor = '#fff';
                        document.getElementById('productinput' + i).value = 0;
                    } else {
                        break;
                    }
                }


                valueAmoutEUR = document.getElementById('amountfielEUR').value;

                if (valueAmoutEUR > 0) {
                    var http = new XMLHttpRequest()

                    http.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {

                            if (this.responseText == 'invalid') {
                                document.getElementById('secondButton').style.display = 'none';
                            } else {
                                document.getElementById('secondButton').style.display = 'inline';
                            }

                            document.getElementById('buying_price').style.display = 'inline';
                            document.getElementById('buying_price').value = 'R';
                            document.getElementById('amountfielBDT').value = this.responseText;
                        }
                    };

                    http.open("GET", "../../index.php/transferMobileCredit/convertCurrency?currency=EUR&amount=" + valueAmoutEUR + "&operator=" + operator, true)
                    http.send(null);
                } else {
                    document.getElementById('amountfielBDT').value = 'Invalid';
                    document.getElementById('secondButton').style.display = 'none';
                }


            }

            function showPriceBDT() {


                for (var i = 0; i < 50; i++) {
                    if (document.getElementById('productLabel' + i)) {
                        document.getElementById('productLabel' + i).style.backgroundColor = '#fff';
                        document.getElementById('productinput' + i).value = 0;
                    } else {
                        break;
                    }
                }



                operator = document.getElementById('operatorfield').options[document.getElementById('operatorfield').selectedIndex].text;
                //verify if inserted amount is allowed
                min = '<?php echo Yii::$app->session['allowedAmountmin']; ?>';
                max = '<?php echo Yii::$app->session['allowedAmountmax']; ?>';

                valueAmoutBDT = document.getElementById('amountfielBDT').value;

                if (valueAmoutBDT < parseInt(min)) {
                    document.getElementById('amountfielEUR').value = 'Invalid';
                    document.getElementById('secondButton').style.display = 'none';
                } else if (valueAmoutBDT > parseInt(max)) {
                    document.getElementById('amountfielEUR').value = 'Invalid';
                    document.getElementById('secondButton').style.display = 'none';
                } else if (valueAmoutBDT > 0) {

                    var http = new XMLHttpRequest()

                    http.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {


                            document.getElementById('amountfielEUR').value = this.responseText;
                            document.getElementById('buying_price').style.display = 'inline';
                            document.getElementById('buying_price').value = 'R';
                            document.getElementById('secondButton').style.display = 'inline';

                        }
                    };

                    http.open("GET", "../../index.php/transferMobileCredit/convertCurrency?currency=BDT&amount=" + valueAmoutBDT + "&operator=" + operator, true)
                    http.send(null);
                } else {
                    document.getElementById('amountfielEUR').value = 'Invalid';
                    document.getElementById('secondButton').style.display = 'none';
                }


            }




            function getBuyingPrice(argument) {


                if (document.getElementById('amountfielBDT') && document.getElementById('amountfielBDT').value > 0) {
                    valueAmoutBDT = document.getElementById('amountfielBDT').value;
                    var id = 0;
                } else {
                    var id = document.getElementById('productinput' + window.productInputSelected).value;
                    valueAmoutBDT = 0;
                }




                operator = document.getElementById('operatorfield').options[document.getElementById('operatorfield').selectedIndex].text;
                if (document.getElementById('buying_price').value != 'R') {
                    document.getElementById('buying_price').value = 'R';
                } else {


                    var http = new XMLHttpRequest()

                    http.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            document.getElementById('buying_price').value = this.responseText;
                        }
                    };

                    http.open("GET", "../../index.php/TransferMobileCredit/getBuyingPrice?id=" + id + "&method=<?php echo isset($_POST['TransferMobileCredit']['method']) ? $_POST['TransferMobileCredit']['method'] : 0 ?>&operatorname=" + operator + "&valueAmoutBDT=" + valueAmoutBDT, true)
                    http.send(null);

                }
            }

            function button2(e) {

                if (document.getElementById("TransferMobileCredit_metric") && document.getElementById('metric').style.display != 'none') {

                    if (document.getElementById("TransferMobileCredit_metric").value.length < 3) {
                        alert('Please add the Metric number');
                        return false;
                    }
                }


                if (!document.getElementById('amountfiel')) {
                    document.getElementById("sendButton").style.display = 'none';
                    document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
                    return;
                }
                valueAmout = document.getElementById('amountfiel').value;

                if (valueAmout > 0) {
                    if (!confirm('Are you sure to send this request?')) {
                        e.preventDefault();
                    } else {
                        document.getElementById("sendButton").style.display = 'none';
                        document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
                    }
                } else {
                    document.getElementById("sendButton").style.display = 'none';
                    document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
                }

            }

            function showPrice(argument) {
                document.getElementById('buying_price').style.display = 'inline';
                document.getElementById('buying_price').value = 'R';
                idProduct = document.getElementById('amountfiel').options[document.getElementById('amountfiel').selectedIndex].value;

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

            function showProducts(argument) {

                operator = document.getElementById('operatorfield').options[document.getElementById('operatorfield').selectedIndex].text;

                if (document.getElementById('amountfielEUR')) {
                    document.getElementById('amountfielEUR').value = '';
                }
                if (document.getElementById('amountfielBDT')) {
                    document.getElementById('amountfielBDT').value = '';
                }


                if (operator == 'Select the operator') {
                    document.getElementById("productList").innerHTML = '';
                    document.getElementById('secondButton').style.display = 'none';
                    document.getElementById('is_interval').style.display = 'none';

                    return;
                } else {
                    document.getElementById('secondButton').style.display = 'inline';
                }
                if (operator == 'SENELEC - Senegal' || operator == 'NAWEC - Gambia' || operator == 'EDM - Mali' || operator == 'EEDC - Nigeria') {
                    document.getElementById('metric').style.display = 'inline';
                } else {
                    document.getElementById('metric').style.display = 'none';
                }


                var http = new XMLHttpRequest()
                http.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {


                        if (this.responseText.substr(-12) == '|is_interval') {
                            document.getElementById('is_interval').style.display = 'inline';
                            document.getElementById("productList").innerHTML = this.responseText.replace('|is_interval', '');
                        } else {

                            document.getElementById("productList").innerHTML = this.responseText;
                            if (document.getElementById('is_interval')) {
                                document.getElementById('is_interval').style.display = 'none';
                            }
                        }

                    }
                }

                http.open("GET", "../../index.php/TransferMobileCredit/getProducts?operator=" + operator);
                http.send(null);

                document.getElementById('buying_price').style.display = 'none';

            }

            function transfer_show_selling_price(argument) {
                console.log('teste');
            }
            var currentValue = 0;

            function handleChange1(argument, total) {



                document.getElementById('secondButton').style.display = 'inline';

                if (document.getElementById('amountfielEUR')) {
                    document.getElementById('amountfielEUR').value = '';
                }
                if (document.getElementById('amountfielBDT')) {
                    document.getElementById('amountfielBDT').value = '';
                }



                for (var i = 0; i < 30; i++) {
                    if (!document.getElementById('productLabel' + i)) {
                        break;
                    }
                    document.getElementById('productLabel' + i).style.backgroundColor = '#fff';
                }
                document.getElementById('productLabel' + argument).style.backgroundColor = '#dd8980';

                document.getElementById('secondButton').style.display = 'inline';

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