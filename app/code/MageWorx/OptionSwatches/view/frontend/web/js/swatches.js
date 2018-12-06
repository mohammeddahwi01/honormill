/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(
[
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Catalog/js/price-utils',
    'jquery/validate',
    'jquery/ui',
    'jquery/jquery.parsequery'
],
function ($, _, $t, priceUtils) {
    'use strict';

    $.widget('mageworx.optionSwatches', {
        options: {
            hiddenSelectClass: 'mageworx-swatch',
            optionClass: 'mageworx-swatch-option'
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
            this._observeStyleOptions();
            this._grayoutDisabledOptions();
            this._initEventListener();
            this._validateRequiredSwatches();
        },

        /**
         * Observe style changes of select to show/hide swatch divs
         * Example: OptionDependency hides child option - divs must be unchecked and hidden
         */
        _observeStyleOptions: function ()
        {
            var self = this,
            target = $('.' + this.options.optionClass).next('select').find('option');

            //in case of cart reconfigure
            $.each(target, function() {
                self.processSwatchLabel($(this));
            });

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutationRecord) {
                    self._toggleSwatch($(mutationRecord.target));
                });
            });

            $.each(target, function (i, e) {
                observer.observe(e, {attributes: true, attributeFilter: ['style']});
            });
        },

        /**
         * Disable swatch image if the corresponding option value is disabled.
         */
        _grayoutDisabledOptions: function ()
        {
            var self = this;

            $('.' + this.options.optionClass).each(function () {
                var el = $(this),
                    optionId = el.attr('option-id'),
                    optionValueId = el.attr('option-type-id');
                var optionValue = $('#select_' + optionId + ' option[value="' + optionValueId + '"]');

                if (optionValue.prop('disabled')) {
                    el.addClass('disabled');
                }
            });
        },

        /**
         * Show/hide swatch divs
         * @param $selectOption
         */
        _toggleSwatch: function ($selectOption)
        {
            var $swatch = $('[option-type-id="' + $selectOption.val() + '"]');
            this.processSwatchLabel($selectOption, $swatch);
            $swatch.css('display', $selectOption.css('display'));
        },

        /**
         * Process swatch div according to hidden select changes
         * @param $selectOption
         */
        processSwatchLabel: function ($selectOption)
        {
            var $select = $selectOption.parents('select');
            var optionId = priceUtils.findOptionId($select);
            var selectOptions = $('#select_' + optionId + ' option');
            if (!selectOptions) {
                return;
            }

            var optionLabel = $select.parents('.field').find('label');
            if (optionLabel.parent().find('span#dynamicValue').length <= 0) {
                /*var optId = optionLabel.closest('div.fiels').attr('option_id');*/
                optionLabel.find('span').after('<span id="dynamicValue" class="dynamicValue"></span>');
                //optionLabel.find('span').after('<span id="dynamicValue" class="dynamicValue"></span>');
                /*optionLabel.parent().find('.label > span').after('<span id="dynamicValue" class="dynamicValue"></span>');*/
            }

            var isSelectedOptionExist = false;
            $(selectOptions).each(function () {
                console.log("each");
                // if ($select.val() && $select.val() == $(this).attr('value')) {
                //     console.log("if Value");
                // }
                if ($select.val() && $select.val() == $(this).attr('value')) {
                    console.log("if dynamicValue");
                    isSelectedOptionExist = true;
                    var $swatch = $("[option-type-id='" + $select.val() + "']"),
                    $el = optionLabel.parent().find('span#dynamicValue');
                    $swatch.addClass('selected');
                    $el.html(' - ' + $swatch.attr('option-label'));
                    if ($swatch.attr('option-price') > 0) {
                        $el.html($el.html() + ' +' + priceUtils.formatPrice($swatch.attr('option-price')));
                    }
                }
            });
            if (isSelectedOptionExist === false) {
                optionLabel.parent().find('span#dynamicValue').html('');
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
            if ($(option).hasClass(this.options.hiddenSelectClass)) {
                if ($(option).val() == '') {
                    $(option).parent().find('.selected').removeClass('selected');
                    $(option).parents('.field').find('label').parent().find('span#dynamicValue').html('');
                }

                var optionId = priceUtils.findOptionId(option);
                var $selectOption = $('#select_' + optionId + ' option').first();
                this.processSwatchLabel($selectOption);
            }
        },

        /**
         * Initialize event listener for swatch div's click
         */
        _initEventListener: function ()
        {
            var self = this;

            $('body').on('click', '.' + this.options.optionClass, function () {
                self._onClick(this);
            });
        },

        /**
         * Click event for swatch div
         * Process all needed actions for hidden select
         * @param option
         */
        _onClick: function (option)
        {
            if ($(option).hasClass('disabled')) {
                return;
            }

            var optionId = $(option).attr('option-id');
            var optionValueId = $(option).attr('option-type-id');
            var select = $('#select_' + optionId);
            var selectOptions = $('#select_' + optionId + ' option');
            if (!selectOptions) {
                return;
            }

            if(!$('.catalog-product-view .product-options-wrapper > .fieldset.multi-select-option').length) {
                // $('.catalog-product-view .product-options-wrapper > .fieldset > .field').find('span.dynamicValue').remove();
                // $('.catalog-product-view .product-options-wrapper > .fieldset > .field .mageworx-swatch-option').removeClass('selected');
                // $('.catalog-product-view .product-options-wrapper > .fieldset > .field select').val('');
                //$('.catalog-product-view .product-options-wrapper > .fieldset > .field span#dynamicValue').remove();
                $('.catalog-product-view .product-options-wrapper > .fieldset > .field select').each(function () {
                    var selId = select.attr('id');
                    if (selId != $(this).attr('id')) {
                        if ($(this).val() != '') {
                            $(this).val('');
                            $(this).parents('.field').find('label').parent().find('span#dynamicValue').html('');
                            $(this).parent().find('.selected').removeClass('selected');
                            /*$(this).trigger('change');*/
                        }
                    }
                });
            }

            if ($(option).parents('.field').find('label').parent().find('span#dynamicValue').length <= 0) {
                $(option).parents('.field').find('label > span').after('<span id="dynamicValue" class="dynamicValue"></span>');
            }
            $(selectOptions).each(function () {
                if ($(this).val() == optionValueId) {
                    if ($(option).hasClass('selected')) {
                        $(select).val('');
                        $(option).parents('.field').find('label').parent().find('span#dynamicValue').html('');
                        $(option).parent().find('.selected').removeClass('selected');
                    } else {
                        $(select).val(optionValueId);
                        var $el = $(option).parents('.field').find('label').parent().find('span#dynamicValue');
                        $el.html(' - ' + $(option).attr('option-label'));
                        if ($(option).attr('option-price') > 0) {
                            $el.html($el.html() + ' +' + priceUtils.formatPrice($(option).attr('option-price')));
                        }
                        $(option).parent().find('.selected').removeClass('selected');
                        $(option).addClass('selected');
                    }
                    if(!$('.catalog-product-view .product-options-wrapper > .fieldset.multi-select-option').length) {
                        $('.catalog-product-view .product-options-wrapper > .fieldset > .field.ownMaterialOptionExist input').each(function () {
                            if ($(this).is(':checked')) {
                                $(this).attr('checked', false);
                            }
                        });
                        $('.catalog-product-view .product-options-wrapper > .fieldset > .field select, .catalog-product-view .product-options-wrapper > .fieldset > .field.ownMaterialOptionExist input').trigger('change');
                    } else {
                        $('.catalog-product-view .product-options-wrapper > .fieldset > .field.ownMaterialOptionExist input').each(function () {
                            if ($(this).is(':checked')) {
                                $(this).attr('checked', false);
                            }
                        });
                        var selId = select.attr('id');
                        $('#'+selId+', .catalog-product-view .product-options-wrapper > .fieldset > .field.ownMaterialOptionExist input').trigger('change');
                    }
                    return;
                }
            });
        },

        /**
         * Validator for required swatch options
         */
        _validateRequiredSwatches: function ()
        {
            var self = this;
            if (self.options.isEnabledRedirectToCart) {
                return;
            }
            $('#product_addtocart_form').mage('validation', {
                ignore: ':hidden:not(.' + self.options.hiddenSelectClass + ')',
                radioCheckboxClosest: '.nested',
                submitHandler: function (form) {
                    var widget = $(form).catalogAddToCart({
                        bindSubmit: false
                    });
                    widget.catalogAddToCart('submitForm', $(form));
                    return false;
                }
            });
        }
    });

    return $.mageworx.optionSwatches;
}
);
