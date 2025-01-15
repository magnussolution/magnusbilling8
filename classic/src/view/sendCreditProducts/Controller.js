/**
 * Classe que define a lista de "CallShopCdr"
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
 * 01/10/2013
 */
Ext.define('MBilling.view.sendCreditProducts.Controller', {
    extend: 'Ext.ux.app.ViewController',
    alias: 'controller.sendcreditproducts',
    onResetClientsPrice: function (btn) {
        var me = this,
            selected = me.list.getSelectionModel().getSelection()[0];
        Ext.Ajax.request({
            url: 'index.php/send-credit-products/reset-client-price',
            scope: me,
            success: function (response) {
                response = Ext.decode(response.responseText);
                if (response[me.nameSuccessRequest]) {
                    Ext.ux.Alert.alert(me.titleSuccess, response[me.nameMsgRequest], 'success');
                } else {
                    var errors = Helper.Util.convertErrorsJsonToString(response[me.nameMsgRequest]);
                    Ext.ux.Alert.alert(me.titleSuccess, errors, 'error');
                }
            }
        });
        me.store.load();
    }
});