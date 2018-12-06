/**
 * Copyright © 2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'uiRegistry',
    'ko',
    'jquery'
], function (uiCheckbox, registry, ko, $) {
    'use strict';

    /**
     * Extend base checkbox element.
     * Uncheck other checkboxes for single-selection option if one is selected.
     * Uncheck all checked values if there are more then one checked value and new option type is drop_down/radio
     * Used in the: \MageWorx\OptionFeatures\Ui\DataProvider\Product\Form\Modifier\Features
     * for "Is Default" feature
     */
    return uiCheckbox.extend({

        /**
         * Index of isDefault in dynamic-row record element
         */
        isDefaultIndex: 146,

        /**
         * List of valid option types (show element if they are selected for the current option)
         */
        availableTypes: [
            'drop_down',
            'radio'
        ],

        /**
         * Invokes onCheckedChanged method of parent class,
         * Contains radiobutton logic for single selection options (drop_down, radio)
         * Contains checkbox logic for multi selection options (multiselect, checkbox)
         */
        onCheckedChanged: function () {
            this._super();
            var self = this;
            /**
             * Wait for the option type select render and observe its value
             */
            new Promise(function (resolve, reject) {
                var timer_search_container = setInterval(function () {
                    if (typeof self.containers[0] !== 'undefined') {
                        var option = self.containers[0].containers[0];
                        if (typeof option !== 'undefined') {
                            clearInterval(timer_search_container);
                            var path = 'source.' + option.dataScope,
                            optionType = self.get(path).type,
                            typeSelect = registry.get("ns = " + option.ns +
                            ", parentScope = " + option.dataScope +
                            ", index = type");
                            if (self.availableTypes.indexOf(optionType) != -1) {
                                if (self.checked() == true) {
                                    option.elems.each(function (record) {
                                        var isDefault = record._elems[self.isDefaultIndex];
                                        if (isDefault != self) {
                                            isDefault.checked(false);
                                        }
                                    });
                                }
                            }
                            resolve(typeSelect);
                        }
                    }
                }, 200);
            }).then(
            function (result) {
                result.on('update', function (e) {
                    var option = self.containers[0].containers[0],
                    newOptionType = result.value(),
                    checkedCounter = 0;
                    option.elems.each(function (record) {
                        var isDefault = record._elems[self.isDefaultIndex];
                        if (isDefault.checked() == true) {
                            checkedCounter += 1;
                        }
                    });

                    //do not uncheck values if there is less then 2 checked values
                    //or new option type is drop_down/radio
                    if (self.availableTypes.indexOf(newOptionType) != -1 && checkedCounter > 1) {
                        option.elems.each(function (record) {
                            var isDefault = record._elems[self.isDefaultIndex];
                            isDefault.checked(false);
                        });
                    }
                });
            },
            function (error) {
                console.log(error);
            }
            );

            return this;
        }
    });
});
