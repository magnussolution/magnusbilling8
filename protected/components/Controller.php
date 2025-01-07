<?php

/**
 * Override default Controller methods.
 *
 * MagnusBilling <info@magnusbilling.com>
 * 11/05/2017
 */

namespace app\components;

class Controller extends BaseController
{
    public $nofilterPerAdminGroup = [
        'offer',
        'module',
        'methodpay',
        'groupUserGroup',
        'groupUser',
        'campaignRestrictPhone',
        'plan',
        'prefix',
        'provider',
        'rate',
        'refillprovider',
        'servers',
        'services',
        'smtps',
        'templateMail',
        'trunk',
        'trunkReport',
        'userType',
        'groupuser',
        'configuration',
        'invoices',
        'statusSystem',
        'firewall',
    ];

    //Allowed controllers to no admin users use updateall
    public $controllerAllowUpdateAll = [
        'rateCallshop',
        'sendCreditRates',
    ];

    public function authorizedNoSession($value = false)
    {

        $allow = [
            'site',
            'authentication',
            'overrides/authenticationOR',
            'asteriskDialplan',
            'asteriskFiles',
            'signup',
            'call0800Web',
            'ata',
            'buyCredit',
            'callApp',
            'clicToCall',
            'gerencianet',
            'joomla',
            'mBillingSoftphone',
            'moip',
            'pagSeguro',
            'paypal',
            'placetoPay',
            'transferToMobile',
            'pagHiper',
            'mercadoPago',
            'molPay',
            'sms',
        ];

        if ($value) {

            $allow[] = $value;
        }
        return in_array($this->controllerName, $allow);
    }
}
