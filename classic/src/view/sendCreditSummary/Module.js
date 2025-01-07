/**
 * Classe que define o panel de "Callerid"
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */
Ext.define('MBilling.view.sendCreditSummary.Module', {
    extend: 'Ext.form.Panel',
    alias: 'widget.sendcreditsummarymodule',
    resizable: false,
    autoShow: true,
    header: false,
    items: [{
        xtype: "component",
        autoEl: {
            width: '100%',
            height: '100%',
            tag: "iframe",
            src: 'index.php/sendCreditSummary/read'
        }
    }]
});