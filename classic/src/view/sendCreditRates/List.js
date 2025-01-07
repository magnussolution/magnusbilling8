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
Ext.define('MBilling.view.sendCreditRates.List', {
    extend: 'Ext.ux.grid.Panel',
    alias: 'widget.sendcreditrateslist',
    store: 'SendCreditRates',
    fieldSearch: 'idProduct.operator_name',
    comparisonfilter: 'ct',
    initComponent: function() {
        var me = this;
        me.buttonUpdateLotCallShopRate = true;
        me.buttonCsv = false;
        me.allowPrint = false;
        me.extraButtons = [{
            text: 'Reset to wholesale price',
            width: 160,
            handler: 'onResetPrice',
            disabled: false
        }, {
            text: 'Reset to retail price',
            width: 135,
            handler: 'onResetRetail',
            disabled: false
        }];
        me.columns = [{
            header: t('ID'),
            dataIndex: 'id',
            flex: 1,
            hidden: true,
            hideable: App.user.isAdmin
        }, {
            xtype: 'templatecolumn',
            tpl: '{idProductoperator_name}',
            header: t('Operator name'),
            dataIndex: 'idProductoperator_name',
            filter: {
                type: 'string',
                field: 'idProduct.operator_name'
            },
            flex: 5
        }, {
            xtype: 'templatecolumn',
            tpl: '{idProductcountry}',
            header: t('Country'),
            dataIndex: 'idProductcountry',
            filter: {
                type: 'string',
                field: 'idProduct.country'
            },
            flex: 4
        }, {
            xtype: 'templatecolumn',
            tpl: '{idProductcurrency_dest}',
            header: t('Currency destination'),
            dataIndex: 'idProductcurrency_dest',
            filter: {
                type: 'string',
                field: 'idProduct.currency_dest'
            },
            flex: 3
        }, {
            xtype: 'templatecolumn',
            tpl: '{idProductproduct}',
            header: t('Product'),
            dataIndex: 'idProductproduct',
            filter: {
                type: 'string',
                field: 'idProduct.product'
            },
            flex: 2
        }, {
            xtype: 'templatecolumn',
            tpl: '{idProductcurrency_orig}',
            header: t('Currency origin'),
            dataIndex: 'idProductcurrency_orig',
            filter: {
                type: 'string',
                field: 'idProduct.currency_orig'
            },
            flex: 3
        }, {
            xtype: 'templatecolumn',
            tpl: '{idProductwholesale_price}',
            header: t('Wholesale price'),
            dataIndex: 'idProductwholesale_price',
            filter: {
                type: 'string',
                field: 'idProduct.wholesale_price'
            },
            flex: 3
        }, {
            header: t('Sell price'),
            dataIndex: 'sell_price',
            flex: 3
        }]
        me.callParent(arguments);
    }
});