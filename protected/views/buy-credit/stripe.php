<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2023 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */


use app\assets\AppAsset;
use yii\bootstrap4\Html;


AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <?php $this->registerCsrfMetaTags() ?>
  <title><?= Html::encode($this->title) ?></title>
  <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
  <?php $this->beginBody() ?>

  <?php
  $error = $success = '';

  if ($_POST) {

    require_once 'lib/stripe/autoload.php';

    \Stripe\Stripe::setApiKey($modelMethodPay->client_id);
    $error   = '';
    $success = '';

    $amount = preg_replace("/\.|\,/", '', $_GET['amount']);

    try {
      if (! isset($_POST['stripeToken'])) {
        throw new Exception("The Stripe Token was not generated correctly");
      }

      \Stripe\Charge::create([
        "amount" => floatval($amount),
        "currency"                            => "usd",
        "card"                                => $_POST['stripeToken']
      ]);
      $success = 'Your payment was successful.';
    } catch (Exception $e) {
      $error = $e->getMessage();
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Stripe Getting Started Form</title>
    <script type="text/javascript" src="https://js.stripe.com/v1/"></script>
    <!-- jQuery is used only for this example; it isn't required to use Stripe -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
    <script type="text/javascript">
      // this identifies your website in the createToken call below
      Stripe.setPublishableKey('<?php echo $modelMethodPay->client_secret; ?>');

      function stripeResponseHandler(status, response) {
        if (response.error) {
          // re-enable the submit button
          $('.submit-button').removeAttr("disabled");
          // show the errors on the form
          $(".payment-errors").html(response.error.message);
        } else {
          var form$ = $("#payment-form");
          // token contains id, last4, and card type
          var token = response['id'];
          // insert the token into the form so it gets submitted to the server
          form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
          // and submit
          form$.get(0).submit();
        }
      }
      $(document).ready(function() {
        $("#payment-form").submit(function(event) {
          // disable the submit button to prevent repeated clicks
          $('.submit-button').attr("disabled", "disabled");
          // createToken returns immediately - the supplied callback submits the form if there are no errors
          Stripe.createToken({
            number: $('.card-number').val(),
            cvc: $('.card-cvc').val(),
            exp_month: $('.card-expiry-month').val(),
            exp_year: $('.card-expiry-year').val()
          }, stripeResponseHandler);
          return false; // submit from callback
        });
      });
    </script>
  </head>

  <body>
    <!-- to display errors returned by createToken -->
    <span class="payment-errors">
      <center>
        <font color=red size=5><?php echo $error ?></font>
      </center>
    </span>
    <span class="payment-success">
      <center>
        <font color=green size=7><?php echo $success ?></font>
      </center>
    </span>

    <form action="" method="POST" id="payment-form">
      <div class="form-container">
        <div class="personal-information">
          <h1>Payment Information : Amount <?php echo $_GET['amount'] ?></h1>
        </div> <!-- end of personal-information -->

        <input id="column-left" type="text" name="first-name" placeholder="First Name" />
        <input id="column-right" type="text" name="last-name" placeholder="Surname" />
        <input id="input-field" class="card-number" card-numbertautocomplete="off" type="text" name="card-number" placeholder="Card Number" />
        <input id="column-left" type="text" class="card-expiry-month" placeholder="MM " />
        <input id="column-right" type="text" class="card-expiry-year" placeholder="YYYY " />
        <input id="input-field" class="card-cvc" type="text" name="card-cvc" placeholder="CCV" />


        <input id="input-button" type="submit" class="submit-button" value="Submit Payment" />

    </form>

    <style type="text/css">
      @import url(https://fonts.googleapis.com/css?family=Roboto:400,900,700,500);

      body {
        padding: 60px 0;
        background-color: rgba(178, 209, 229, 0.7);
        margin: 0 auto;
        width: 600px;
      }

      .body-text {
        padding: 0 20px 30px 20px;
        font-family: "Roboto";
        font-size: 1em;
        color: #333;
        text-align: center;
        line-height: 1.2em;
      }

      .form-container {
        flex-direction: column;
        justify-content: center;
        align-items: center;
      }

      .card-wrapper {
        background-color: #6FB7E9;
        width: 100%;
        display: flex;

      }

      .personal-information {
        background-color: #3C8DC5;
        color: #fff;
        padding: 1px 0;
        text-align: center;
      }

      h1 {
        font-size: 1.3em;
        font-family: "Roboto"
      }

      input {
        margin: 1px 0;
        padding-left: 3%;
        font-size: 14px;
      }

      input[type="text"] {
        display: block;
        height: 50px;
        width: 97%;
        border: none;
      }

      input[type="email"] {
        display: block;
        height: 50px;
        width: 97%;
        border: none;
      }

      input[type="submit"] {
        display: block;
        height: 60px;
        width: 100%;
        border: none;
        background-color: #3C8DC5;
        color: #fff;
        margin-top: 2px;
        curson: pointer;
        font-size: 0.9em;
        text-transform: uppercase;
        font-weight: bold;
        cursor: pointer;
      }

      input[type="submit"]:hover {
        background-color: #6FB7E9;
        transition: 0.3s ease;
      }

      #column-left {
        width: 46.8%;
        float: left;
        margin-bottom: 2px;
      }

      #column-right {
        width: 46.8%;
        float: right;
      }

      @media only screen and (max-width: 480px) {
        body {
          width: 100%;
          margin: 0 auto;
        }

        .form-container {
          margin: 0 2%;
        }

        input {
          font-size: 1em;
        }

        #input-button {
          width: 100%;
        }

        #input-field {
          width: 96.5%;
        }

        h1 {
          font-size: 1.2em;
        }

        input {
          margin: 2px 0;
        }

        input[type="submit"] {
          height: 50px;
        }

        #column-left {
          width: 96.5%;
          display: block;
          float: none;
        }

        #column-right {
          width: 96.5%;
          display: block;
          float: none;
        }
      }
    </style>

    
    <?php $this->endBody() ?>
  </body>

  </html>
  <?php $this->endPage() ?>
  <?php die(); ?>