/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'priceUtils',
    'underscore',
    'priceBox',
    'qTip',
    'jquery/ui'
], function ($, utils, _) {
    'use strict';

    $.widget('mageworx.optionFeatures', {

        options: {
            absolutePriceOptionTemplate: '<%= data.label %>' +
                                         '<% if (data.finalPrice.value) { %>' +
                                         ' <%- data.finalPrice.formatted %>' +
            '<% } %>'
        },

        /**
         * Triggers one time at first run (from base.js)
         * @param optionConfig
         * @param productConfig
         * @param base
         * @param self
         */
        firstRun: function firstRun(optionConfig, productConfig, base, self)
        {
            setTimeout(function () {
            
                // Qty input
                $('.mageworx-option-qty').each(function () {
                
                    $(this).on('change', function () {
                    
                        var optionInput = $("[data-selector='" + $(this).attr('data-parent-selector') + "']");
                        optionInput.trigger('change');
                    });
                });
            }, 500);

            // Option\Value Description & tooltip
            var extendedOptionsConfig = typeof base.options.extendedOptionsConfig != 'undefined' ?
            base.options.extendedOptionsConfig : {};

            for (var option_id in optionConfig) {
                if (!optionConfig.hasOwnProperty(option_id)) {
                    continue;
                }

                var description = extendedOptionsConfig[option_id]['description'],
                thumb = extendedOptionsConfig[option_id]['images_data'],
                $option = base.getOptionHtmlById(option_id);
                if (1 > $option.length) {
                    console.log('Empty option container for option with id: ' + option_id);
                    continue;
                }

                if (this.options.option_description_enabled && !_.isEmpty(extendedOptionsConfig[option_id]['description'])) {
                    if (this.options.option_description_mode == this.options.option_description_modes.tooltip) {
                        var $element = $option.find('label span')
                        .first();
                        if ($element.length == 0) {
                            $element = $option.find('fieldset legend span')
                                .first();
                        }
                        $element.css('border-bottom', '1px dotted black');
                        $element.qtip({
                            content:  {
                                text: description
                            },
                            style: {
                                classes: 'qtip-light'
                            },
                            position: {
                                target: false,
                                my: 'top center',
                                at: 'bottom center'
                            }
                        });
                    } else if (this.options.option_description_mode == this.options.option_description_modes.text) {
                        var $label = $option.find('label');
                        if ($label.length > 0) {
                            $label
                                .first()
                                .after($('<p class="option-description-text">' + description + '</p>'));
                        } else {
                            $label = $option.find('span');
                            $label
                                .first()
                                .parent()
                                .after($('<p class="option-description-text">' + description + '</p>'));
                        }
                    } else {
                        console.log('Unknown option mode');
                    }
                }

                if (this.options.value_description_enabled) {
                    this._addValueDescription($option, extendedOptionsConfig);
                }
            }
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
            var $option = $(option),
            $optionQtyInput = $("[data-parent-selector='" + $option.attr('data-selector') + "']"),
            optionQty = 1,
            values = $option.val(),
            optionId = base.getOptionId($option);

            if ($optionQtyInput.length) {
                if (($option.is(':checked') || $('option:selected', $option).val())) {
                    if ($optionQtyInput.val() == 0) {
                        $optionQtyInput.val(1);
                    }
                    $optionQtyInput.attr('disabled', false);
                } else if (!$option.is(':checked') && !$('option:selected', $option).val()) {
                        if ($optionQtyInput.attr('type') != 'hidden' && $option.attr('type') != 'radio') {
                            $optionQtyInput.val(0);
                            $optionQtyInput.attr('disabled', true);
                        }
                    }

                if (parseFloat($optionQtyInput.val())) {
                    optionQty = parseFloat($optionQtyInput.val());
                }

                if (values) {
                    if (!Array.isArray(values)) {
                        values = [values];
                    }

                    $(values).each(function (i, e) {
                        optionConfig[optionId][e]['qty'] = optionQty;
                    });
                }
            }
        },

        /**
         * Triggers each time after the all updates when option was changed (from the base.js)
         * @param base
         * @param productConfig
         */
        applyChanges: function (base, productConfig)
        {
            if (!base.isAnyOptionSelected() && !base.isNonSelectableOptionsUsed()) {
                return;
            }

            var isAbsolutePriceUsed = true;
            if (_.isUndefined(productConfig.absolute_price) || productConfig.absolute_price == "0") {
                isAbsolutePriceUsed = false;
            }

            if (productConfig.type_id == 'configurable' && !isAbsolutePriceUsed) {
                return;
            }

            var priceInclTax = base.calculateSelectedOptionsPrice(true),
                priceExclTax = base.calculateSelectedOptionsPrice(false),
                regularPriceExclTax = priceExclTax,
                regularPriceInclTax = priceInclTax,
                finalPriceExclTax = priceExclTax,
                finalPriceInclTax = priceInclTax;

            if (!isAbsolutePriceUsed) {
                regularPriceExclTax = parseFloat(productConfig.regular_price_excl_tax) + parseFloat(priceExclTax);
                regularPriceInclTax = parseFloat(productConfig.regular_price_incl_tax) + parseFloat(priceInclTax);
                finalPriceExclTax = parseFloat(productConfig.final_price_excl_tax) + parseFloat(priceExclTax);
                finalPriceInclTax = parseFloat(productConfig.final_price_incl_tax) + parseFloat(priceInclTax);
            }

            // Set product prices according to price's display mode on the product view page
            // 1 - without tax
            // 2 - with tax
            // 3 - both (with and without tax)
            if (base.getPriceDisplayMode() == 1) {
                this.product_regular_price = regularPriceExclTax;
                this.product_final_price = finalPriceExclTax;
            } else {
                this.product_regular_price = regularPriceInclTax;
                this.product_final_price = finalPriceInclTax;
            }
            this.product_final_price_excl_tax = finalPriceExclTax;

            base.setProductRegularPrice(this.product_regular_price);
            base.setProductFinalPrice(this.product_final_price);
            base.setProductPriceExclTax(this.product_final_price_excl_tax);
        },

        /**
         * Add description to the values
         * @param $option
         * @param extendedOptionsConfig
         * @private
         */
        _addValueDescription: function _addValueDescription($option, extendedOptionsConfig)
        {
            var self = this,
            $options = $option.find('.product-custom-option');

            $options.filter('select').each(function (index, element) {
                var $element = $(element),
                optionId = utils.findOptionId($element),
                optionConfig = extendedOptionsConfig[optionId],
                value = extendedOptionsConfig[optionId]['values'];

                if ($element.attr('multiple')) {
                    return;
                }

                if (typeof value == 'undefined' || _.isEmpty(value)) {
                    return;
                }

                if ($element.hasClass('mageworx-swatch')) {
                    var $swatches = $element.parent().find('.mageworx-swatch-option');

                    $swatches.each(function (swatchKey, swatchValue) {
                        var valueId = $(swatchValue).attr('option-type-id');
                        if (!_.isUndefined(value[valueId]) &&
                        (!_.isEmpty(value[valueId]['description']) ||
                        !_.isEmpty(value[valueId]['images_data']['tooltip_image']))
                        ) {
                            var tooltipImage = self.getTooltipImageHtml(value[valueId]);
                            var title = '<div class="title">' + value[valueId]['title'] + '</div>';
                            $(swatchValue).qtip({
                                content:  {
                                    text: tooltipImage + title + value[valueId]['description']
                                },
                                style:    {
                                    classes: 'qtip-light'
                                },
                                position: {
                                    target: false,
                                    my: 'top center',
                                    at: 'bottom center'
                                }
                            });
                        }
                    });
                } else {
                    var $image = $('<img>', {
                        src: self.options.question_image,
                        alt: 'tooltip',
                        "class": 'option-select-tooltip-' + optionId,
                        width: '16px',
                        height: '16px',
                        style: 'display: none'
                    });

                    $element.parent().prepend($image);
                    $element.on('change', function (e) {
                        var valueId = $element.val();
                        if (!_.isUndefined(value[valueId]) &&
                        !_.isEmpty(value[valueId]['description'])
                        ) {
                            var tooltipImage = self.getTooltipImageHtml(value[valueId]);
                            $image.qtip({
                                content: {
                                    text: tooltipImage + value[valueId]['description']
                                },
                                style: {
                                    classes: 'qtip-light'
                                },
                                position: {
                                    target: false,
                                    my: 'top center',
                                    at: 'bottom center'
                                }
                            });
                            $image.show();
                        } else {
                            $image.hide();
                        }
                    });
                }

                if ($element.val()) {
                    $element.trigger('change');
                }
            });

            $options.filter('input[type="radio"], input[type="checkbox"]').each(function (index, element) {
                var $element = $(element),
                optionId = utils.findOptionId($element),
                optionConfig = extendedOptionsConfig[optionId],
                value = extendedOptionsConfig[optionId]['values'];

                if (typeof value == 'undefined' || !value) {
                    return;
                }

                var valueId = $element.val();
                if (_.isUndefined(value[valueId]) ||
                _.isEmpty(value[valueId]['description'])
                ) {
                    return;
                }

                var description = value[valueId]['description'],
                    tooltipImage = self.getTooltipImageHtml(value[valueId]),
                    $image = self.getTooltipImageForOptionValue(valueId);
                $element.parent().append($image);
                $image.qtip({
                    content: {
                        text: tooltipImage + description
                    },
                    style: {
                        classes: 'qtip-light'
                    },
                    position: {
                        target: false,
                        my: 'top center',
                        at: 'bottom center'
                    }
                });
            });
        },

        /**
         * Create image with "?" mark
         * @param valueId
         * @returns {*|jQuery|HTMLElement}
         */
        getTooltipImageForOptionValue: function getTooltipImageForOptionValue(valueId)
        {
            return $('<img>', {
                src: this.options.question_image,
                alt: 'tooltip',
                "class": 'option-value-tooltip-' + valueId,
                width: '16px',
                height: '16px'
            });
        },

        /**
         * Get image html, if it exists, for tooltip
         * @param value
         * @returns {string}
         */
        getTooltipImageHtml: function getTooltipImageHtml(value)
        {
            if (value['images_data']['tooltip_image']) {
                return '<div class="image" style="width:auto; height:auto"><img src="' +
                value['images_data']['tooltip_image'] +
                '" /></div>';
            } else {
                return '';
            }
        }
    });

    return $.mageworx.optionFeatures;
});