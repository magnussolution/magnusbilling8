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

<?php
if (Yii::$app->session['isAdmin'] == 1):

  $modelUser = User::find()->all();
  $users     = CHtml::listData($modelUser, 'id', 'username'); ?>
  <div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Select a user')) ?>
    <div class="styled-select">
      <?php echo $form->dropDownList(
        $model,
        'id',
        $users,
        array('options' => array($_POST['SendCreditSummary']['id'] => array('selected' => true)))
      ); ?>
    </div>
  </div>

<?php endif; ?>


<div class="field">
  <?php echo $form->labelEx($model, Yii::t('zii', 'From date')) ?>

  <?php
  $this->widget(
    'ext.jui.EJuiDateTimePicker',
    array(
      'model'     => $model,
      'attribute' => 'date',
      'language'  => 'en', //default Yii::$app->language
      'mode'      => 'date', //'datetime' or 'time' ('datetime' default)
      'options'   => array(
        'dateFormat' => 'yy-mm-dd',
        'timeFormat' => 'HH:mm:ss',
      ),
    )
  );

  ?>
  <?php echo $form->error($model, 'date') ?>
  <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'date') ?></p>

</div>

<div class="field">
  <?php echo $form->labelEx($model, 'To date') ?>
  <?php
  $this->widget(
    'ext.jui.EJuiDateTimePicker',
    array(
      'model'     => $model,
      'attribute' => 'stopdate',
      'language'  => 'en', //default Yii::$app->language
      'mode'      => 'date', //'datetime' or 'time' ('datetime' default)
      'options'   => array(
        'dateFormat' => 'yy-mm-dd',
        'timeFormat' => 'HH:mm:ss',
      ),
    )
  );
  ?>
</div>

<div class="field">
  <?php echo $form->labelEx($model, Yii::t('zii', 'Number')) ?>
  <?php echo $form->textField($model, 'number', array('class' => 'input')) ?>
  <?php echo $form->error($model, 'number') ?>
</div>

<div class="field">
  <?php echo $form->labelEx($model, Yii::t('zii', 'Service')) ?>
  <?php
  echo $form->dropDownList($model, 'service', array(
    'all'           => 'All',
    'Mobile Credit' => 'Mobile Credit',
    'Mobile Money'  => 'Mobile Money',
    'Payment'       => 'Payment',
  ), array('class' => 'input'));

  ?>
</div>


<br>
<?php echo CHtml::submitButton(Yii::t('zii', 'Filter'), array('class' => 'button')); ?>
<?php $this->endWidget(); ?>



<?php $total_cost   = 0; ?>
<?php $total_sale   = 0; ?>
<?php $total_earned = 0; ?>
<?php foreach ($modelSendCreditSummary as $key => $value): ?>

  <?php $total_cost += $value->cost; ?>
  <?php $total_sale += $value->sell; ?>
  <?php $total_earned += $value->earned; ?>
<?php endforeach ?>

use Yii;
use CHtml;
use app\models\User;
<div class="rounded">
  <br>
  <table class="blueTable" align="right" style="width: 420px; ">
    <thead style="background: #4676b1">
      <tr>
        <th width="100px">Total</th>
        <th width="100px"><?php echo number_format($total_cost, 2) ?></th>
        <th width="100px"><?php echo number_format($total_sale, 2) ?></th>
        <th width="100px"><?php echo number_format($total_earned, 2) ?></th>
      </tr>
    </thead>
  </table>
  <br>
  <br>
  <table class="blueTable">
    <thead>
      <tr>
        <th>Date</th>
        <th>Service</th>
        <th>Number</th>
        <th>Operator</th>
        <th>Received Amount</th>
        <th width="100px">Cost</th>
        <th width="100px">Sell</th>
        <th width="100px">Profit</th>
      </tr>
    </thead>

    <tbody>


      <?php foreach ($modelSendCreditSummary as $key => $value): ?>
        <tr>
          <td><?php echo $value->date; ?></td>
          <td><?php echo $value->service; ?></td>
          <td><?php echo $value->number; ?></td>
          <td><?php echo $value->operator_name; ?></td>
          <td><?php echo $value->received_amout; ?></td>
          <td><?php echo number_format($value->cost, 2); ?></td>
          <td><?php echo number_format($value->sell, 2); ?></td>
          <td><?php echo number_format($value->earned, 2); ?></td>
        </tr>
      <?php endforeach; ?>


    </tbody>
  </table>
</div>

<style type="text/css">
  .hasDatepicker {
    font-family: Arial, Verdana;
    font-size: 15px;
    padding: 5px;
    border: 1px solid #b9bdc1;
    width: 380px;
    color: #797979;
  }


  table.blueTable {
    border: 1px solid #5b855b;
    background-color: #EEEEEE;
    width: 100%;
    text-align: center;
    border-collapse: collapse;
  }

  table.blueTable td,
  table.blueTable th {
    border: 1px solid #AAAAAA;
    padding: 3px 2px;
  }

  table.blueTable tbody td {
    font-size: 13px;
  }

  table.blueTable tr:nth-child(even) {
    background: #e2efe2;
  }

  table.blueTable thead {
    background: #5b855b;
    background: -moz-linear-gradient(top, #9ecea1 0%, #9ecea1 66%, #9ecea1 100%);
    background: -webkit-linear-gradient(top, #9ecea1 0%, #9ecea1 66%, #9ecea1 100%);
    background: linear-gradient(to bottom, #9ecea1 0%, #9ecea1 66%, #9ecea1 100%);
    border-bottom: 2px solid #444444;
  }

  table.blueTable thead th {
    font-size: 15px;
    font-weight: bold;
    color: #FFFFFF;
    border-left: 2px solid #e2efe2;
  }

  table.blueTable thead th:first-child {
    border-left: none;
  }

  table.blueTable tfoot {
    font-size: 14px;
    font-weight: bold;
    color: #FFFFFF;
    background: #e2efe2;
    background: -moz-linear-gradient(top, #adebad 0%, #d4e6f6 66%, #e2efe2 100%);
    background: -webkit-linear-gradient(top, #adebad 0%, #d4e6f6 66%, #e2efe2 100%);
    background: linear-gradient(to bottom, #adebad 0%, #d4e6f6 66%, #e2efe2 100%);
    border-top: 2px solid #444444;
  }

  table.blueTable tfoot td {
    font-size: 14px;
  }

  table.blueTable tfoot .links {
    text-align: right;
  }

  table.blueTable tfoot .links a {
    display: inline-block;
    background: #5b855b;
    color: #FFFFFF;
    padding: 2px 8px;
    border-radius: 5px;
  }
</style>