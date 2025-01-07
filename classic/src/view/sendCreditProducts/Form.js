/**
 * Classe que define o form de "sendCreditProducts"
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnusbilling7/issues
 * =======================================
 * Magnusbilling.org <info@magnussolution.com>
 * 19/09/2018
 */
Ext.define('MBilling.view.sendCreditProducts.Form', {
    extend: 'Ext.ux.form.Panel',
    alias: 'widget.sendcreditproductsform',
    initComponent: function() {
        var me = this;
        me.items = [{
            name: 'country',
            fieldLabel: t('Country'),
            readOnly: true
        }, {
            name: 'operator_name',
            fieldLabel: t('Operator name')
        }, {
            name: 'operator_id',
            fieldLabel: t('Operator ID')
        }, {
            name: 'SkuCode',
            fieldLabel: t('SkuCode')
        }, {
            name: 'product',
            fieldLabel: t('Product')
        }, {
            xtype: 'moneyfield',
            name: 'send_value',
            fieldLabel: t('Send value'),
            mask: '#9.999.990,000000',
            readOnly: !App.user.isAdmin
        }, {
            xtype: 'moneyfield',
            name: 'wholesale_price',
            fieldLabel: t('Wholesale price'),
            mask: '#9.999.990,000000',
            readOnly: !App.user.isAdmin
        }, {
            xtype: 'combobox',
            name: 'provider',
            fieldLabel: t('Provider'),
            value: 'TransferTo',
            forceSelection: true,
            editable: false,
            store: [
                ['TransferTo', 'TransferTo'],
                ['Ding', 'Ding'],
                ['TanaSend', 'TanaSend'],
                ['Orange2', 'Orange2'],
                ['Reload', 'Reload']
            ]
        }, {
            xtype: 'yesnocombo',
            name: 'status',
            fieldLabel: t('Status')
        }, {
            xtype: 'textareafield',
            name: 'info',
            fieldLabel: t('Description'),
            allowBlank: true
        }, {
            xtype: 'moneyfield',
            name: 'retail_price',
            fieldLabel: t('Retail price'),
            mask: '#9.999.990,000000',
            readOnly: !App.user.isAdmin
        }, {
            xtype: 'combobox',
            name: 'method',
            fieldLabel: t('Type'),
            value: 'mobileCredit',
            forceSelection: true,
            editable: false,
            store: [
                ['mobileCredit', 'Mobile Credit'],
                ['mobileMoney', 'Mobile Money'],
                ['payment', 'Payment']
            ]
        }];
        me.callParent(arguments);
    }
});