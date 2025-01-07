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
Ext.define('MBilling.view.sendCreditSummary.List', {
    extend: 'Ext.ux.grid.Panel',
    alias: 'widget.sendcreditsummarylist',
    store: 'SendCreditSummary',
    initComponent: function() {
        var me = this;
        me.extraButtons = [{
            xtype: 'searchfield',
            emptyText: t('From day'),
            fieldFilter: 'date',
            filterOnClick: me.filterFieldOnClick,
            store: me.store,
            comparison: 'gt',
            type: 'date'
        }, {
            xtype: 'searchfield',
            emptyText: t('To day'),
            fieldFilter: 'date',
            filterOnClick: me.filterFieldOnClick,
            store: me.store,
            comparison: 'lt',
            type: 'date'
        }];
        me.buttonCsv = false;
        me.buttonPrint = false;
        me.columns = [{
            header: t('ID'),
            dataIndex: 'id',
            flex: 1,
            hidden: true,
            hideable: App.user.isAdmin
        }, {
            header: t('Day'),
            renderer: Ext.util.Format.dateRenderer('Y-m-d'),
            dataIndex: 'day',
            menuDisabled: true,
            flex: 3
        }, {
            header: t('Service'),
            dataIndex: 'service',
            flex: 5
        }, {
            header: t('Total cost'),
            dataIndex: 'total_cost',
            flex: 5,
            renderer: Helper.Util.formatMoneyDecimal2
        }, {
            header: t('Total sale'),
            dataIndex: 'total_sale',
            flex: 5,
            renderer: Helper.Util.formatMoneyDecimal2
        }, {
            header: t('Earned'),
            dataIndex: 'earned',
            flex: 5,
            renderer: Helper.Util.formatMoneyDecimal2
        }]
        me.callParent(arguments);
    }
});