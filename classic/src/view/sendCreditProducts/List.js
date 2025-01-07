/**
 * Classe que define a lista de "Callerid"
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
 * 19/09/2012
 */
Ext.define('MBilling.view.sendCreditProducts.List', {
    extend: 'Ext.ux.grid.Panel',
    requires: ['MBilling.view.sendCreditProducts.ImportCsv'],
    alias: 'widget.sendcreditproductslist',
    store: 'SendCreditProducts',
    fieldSearch: 'operator_name',
    comparisonfilter: 'ct',
    initComponent: function() {
        var me = this;
        me.buttonImportCsv = App.user.isAdmin;
        me.extraButtons = [{
            text: 'Reset clients price',
            width: 160,
            handler: 'onResetClientsPrice',
            disabled: false
        }];
        me.columns = [{
            header: t('ID'),
            dataIndex: 'id',
            flex: 1,
            hidden: true,
            hideable: App.user.isAdmin
        }, {
            header: t('Country'),
            dataIndex: 'country',
            flex: 4
        }, {
            header: t('Country code'),
            dataIndex: 'country_code',
            flex: 2
        }, {
            header: t('Operator ID'),
            dataIndex: 'operator_id',
            flex: 2
        }, {
            header: t('SkuCode'),
            dataIndex: 'SkuCode',
            flex: 2
        }, {
            header: t('Operator name'),
            dataIndex: 'operator_name',
            flex: 5
        }, {
            header: t('Currency destination'),
            dataIndex: 'currency_dest',
            flex: 3
        }, {
            header: t('Product'),
            dataIndex: 'product',
            flex: 2
        }, {
            header: 'Currency origem',
            dataIndex: 'currency_orig',
            flex: 3
        }, {
            header: t('Send value'),
            dataIndex: 'send_value',
            flex: 2
        }, {
            header: t('Wholesale price'),
            dataIndex: 'wholesale_price',
            flex: 2
        }, {
            header: t('Provider'),
            dataIndex: 'provider',
            flex: 2
        }, {
            header: t('Status'),
            dataIndex: 'status',
            renderer: Helper.Util.formatBooleanActive,
            comboRelated: 'booleancombo',
            flex: 2,
            filter: {
                type: 'list',
                options: [
                    [1, t('Active')],
                    [0, t('Inactive')]
                ]
            }
        }, {
            header: t('Retail price'),
            dataIndex: 'retail_price',
            flex: 3
        }]
        me.callParent(arguments);
    }
});