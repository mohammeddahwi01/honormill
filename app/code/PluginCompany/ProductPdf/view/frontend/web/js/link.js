define([
    'uiClass',
    'jquery'
], function(Class, $) {
    return Class.extend({
        initialize: function (config, node) {
            this._super();
            $(node)[config.insertMethod](config.linkSelector);
        }
    });
});