define([
    'jquery',
    'Magento_Ui/js/form/element/single-checkbox'
], function ($,  SingleCheckbox) {
    'use strict';

    return SingleCheckbox.extend({
        defaults: {
            templates: {
                checkbox: 'Amasty_Checkout/form/components/single/checkbox'
            },
            modules: {
                email: 'checkout.steps.shipping-step.shippingAddress.customer-email'
            }
        },

        initialize: function () {
            this._super();
            this.visible(!this.email().isPasswordVisible());
            var dobField = '.field.dob_datepicker._required';

            this.email().isLoading.subscribe(function (isLoading) {
                if (isLoading === true) {
                    $.when(this.email().isEmailCheckComplete).done(function () {
                        this.visible(true);
                        if (this.checked() == true) {
                            $(dobField).show();
                        }
                    }.bind(this)).fail(function () {
                        this.visible(false);
                        this.checked(false)
                        $(dobField).hide();

                    }.bind(this));
                }
            }.bind(this))
        }
    });
});