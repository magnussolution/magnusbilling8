Ext.define('Overrides.form.field.VTypes', {
    override: 'Ext.form.field.VTypes',
    comparePasswordText: t('Passwords not match'),
    comparePassword: function(value, field) {
        var password = field.up('form').down('field[password=true]').getValue();
        return (value === password);
    },
    // custom Vtype for vtype:'IPAddress'
    numberfield: function(v) {
        return /^\d{6}$/.test(v);
    },
    // custom Vtype for vtype:'IPAddress'
    IPAddress: function(v) {
        return /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(v);
    },
    IPAddressText: 'Must be a numeric IP address',
    IPAddressMask: /[\d\.]/i
});