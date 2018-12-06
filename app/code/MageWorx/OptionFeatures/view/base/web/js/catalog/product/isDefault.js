/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';

    $.widget('mageworx.optionFeaturesIsDefault', {
        options: {
        },

        /**
         * Triggers one time after init price (from base.js)
         * @param optionConfig
         * @param productConfig
         * @param base
         * @param self
         */
        firstRun: function firstRun(optionConfig, productConfig, base, self)
        {
            if (!this.options.is_default_enabled ) {
                return;
            }

            if (this.options.router == 'admin_order_create') {
                return;
            }

            if (this.options.area == 'adminhtml') {
                this.processFirstRun(base);
            }
        },

        /**
         * Triggers one time at first run (from base.js)
         * @param optionConfig
         * @param productConfig
         * @param base
         * @param self
         */
        afterInitPrice: function afterFirstRun(optionConfig, productConfig, base, self)
        {
            if (!this.options.is_default_enabled) {
                return;
            }

            if (this.options.router == 'admin_order_create') {
                return;
            }

            if (this.options.router != 'checkout') {
                this.processFirstRun(base);
            }
        },

        /**
         * Process first run (common part for frontend and adminhtml)
         * @param base
         */
        processFirstRun: function processFirstRun(base)
        {
            var isDefaultArray = this.getIsDefaultValues();
            $.each(isDefaultArray, function(mageworxOptionTypeId, optionType) {
                var $field = $('[option_type_id="' + mageworxOptionTypeId + '"]');

                if ($field.css('display') != 'none') {
                    if ($.inArray(optionType, ['drop_down', 'multiple']) !== -1) {
                        var selectedValues = $field.closest('select').val();
                        if ($.inArray(optionType, ['multiple']) !== -1) {
                            if (selectedValues === null) {
                                selectedValues = [];
                                selectedValues.push($field.val());
                            } else if ($.isArray(selectedValues)){
                                selectedValues.push($field.val());
                            }
                            base.removeNewlyShowedOptionValue(mageworxOptionTypeId);
                            $field.closest('select').val(selectedValues).change();
                        } else {
                            base.removeNewlyShowedOptionValue(mageworxOptionTypeId);
                            $field.closest('select').val($field.val()).change();
                        }
                    } else if ($.inArray(optionType, ['checkbox', 'radio']) !== -1) {
                        var $el = $field.find(':input');
                        $el.prop('checked', true);
                        base.removeNewlyShowedOptionValue(mageworxOptionTypeId);
                        $el.change();
                    }
                }
            });
        },

        /**
         * Triggers each time when option is updated\changed (from the base.js)
         * @param option
         * @param optionConfig
         * @param productConfig
         * @param base
         */
        update: function update(option, optionConfig, productConfig, base)
        {
            if (!this.options.is_default_enabled) {
                return;
            }

            if (this.options.router == 'admin_order_create') {
                return;
            }

            var isDefaultArray = this.getIsDefaultValues(),
                optionValues = base.getNewlyShowedOptionValues();
            if (_.isEmpty(optionValues) || _.isEmpty(isDefaultArray)) {
                return;
            }

            $.each(optionValues, function (index, value) {
                var optionType = isDefaultArray[value],
                    $field = $('[option_type_id="' + value + '"]');

                if ($field.css('display') != 'none') {
                    if ($.inArray(optionType, ['drop_down', 'multiple']) !== -1) {
                        if ($.inArray(optionType, ['multiple']) !== -1) {
                            var selectedValues = $field.closest('select').val();
                            if (selectedValues === null) {
                                selectedValues = [];
                                selectedValues.push($field.val());
                            } else if ($.isArray(selectedValues)){
                                selectedValues.push($field.val());
                            }
                            base.removeNewlyShowedOptionValue(value);
                            $field.closest('select').val(selectedValues).change();
                        } else {
                            base.removeNewlyShowedOptionValue(value);
                            if (!$field.closest('select').val()) {
                                $field.closest('select').val($field.val()).change();
                            }
                        }
                    } else if ($.inArray(optionType, ['checkbox', 'radio']) !== -1) {
                        base.removeNewlyShowedOptionValue(value);
                        var canCheck = false;
                        if ($.inArray(optionType, ['checkbox']) !== -1) {
                            canCheck = true;
                        } else {
                            var apoData = base.getApoData();
                            var $option = $field.parents('.field');
                            var optionId = $option.attr('option_id');
                            if (!_.isUndefined(apoData[optionId])) {
                                if (apoData[optionId].length == 0) {
                                    canCheck = true;
                                }
                            }
                        }
                        if (canCheck === true) {
                            var $el = $field.find(':input');
                            $el.prop('checked', true);
                            $el.change();
                        }
                    }
                }
            });
        },

        /**
         * Get predefined isDefault values array
         * @return array
         */
        getIsDefaultValues: function update()
        {
            return this.options.is_default_values;
        }
    });

    return $.mageworx.optionFeaturesIsDefault;
});