define([
    'Magento_Ui/js/form/element/date',
    'jquery',
    'mage/translate',
    'Amasty_Checkout/js/form/element/single-checkbox',
    'Amasty_Checkout/js/view/checkout/datepicker'
], function (Component, $, $t) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Amasty_Checkout/form/date_of_birth',
            listens: {
                'update': 'update'
            },
            modules: {
                checkBox: '${ $.parentName }.register'
            }
        },
        _requiredFieldSelector: '.dob_datepicker :input:not(:button)',

        /**
         * initialize
         */
        initialize: function () {
            this._super();
            this.options.maxDate = new Date();
            this.options.changeMonth = true;
            this.options.changeYear = true;
            this.options.yearRange = "-100:+0";
            this.options.showButtonPanel = true;
            this.options.showOn = 'both';
            this.checkBox().checked.subscribe(this.getIsChecked.bind(this));
        },

        update: function () {
            $(this._requiredFieldSelector).each(function(index, element){
                this._removeErrorToInput(element);
                this._removeErrorAfterInput(element);
            }.bind(this));
        },

        validate: function () {
            var isAllValid = true;
            $(this._requiredFieldSelector).each(function(index, element) {
                if($(element).val().length === 0) {
                    this._addErrorToInput(element);
                    this._addErrorAfterInput(element);
                    isAllValid = false;
                }
            }.bind(this));

            return isAllValid;
        },

        _addErrorToInput: function (input) {
            $(input).addClass('mage-error');
        },

        _addErrorAfterInput: function(input) {
            var after = $('#' + $(input).attr('id') + '-error');
            if (typeof $(after).get(0) === "undefined") {
                $(input).parent().after('<div ' +
                    'for="' + $(input).attr('id') + '" ' +
                    'generated="true" ' +
                    'class="mage-error" ' +
                    'id="' + $(input).attr('id') + '-error">' +
                    $t('This is a required field.') +
                    '</div>');
            }
        },

        _removeErrorToInput: function (input) {
            $(input).removeClass('mage-error');
        },

        _removeErrorAfterInput: function (input) {
            var after = $('#' + $(input).attr('id') + '-error');
            if (typeof $(after).get(0) !== "undefined") {
                $(after).remove();
            }
        },

        /**
         * @override
         */
        getIsChecked: function (checked) {
            var dobField = '.field.dob_datepicker._required';
            if (checked == false) {
                $(dobField).hide();
            } else {
                $(dobField).show();
            }
        }
    });
});
