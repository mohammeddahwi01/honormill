/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        // HTML Templates by option type
        'text!MageWorx_OptionFeatures/template/option/gallery/dropdown.html',
        'text!MageWorx_OptionFeatures/template/option/gallery/radio.html',
        'text!MageWorx_OptionFeatures/template/option/gallery/checkbox.html',
        'text!MageWorx_OptionFeatures/template/option/gallery/empty.html',
        // @mwImageReplacer used for replace main image in the product gallery
        'mwImageReplacer',
        'jquery/validate',
        'jquery/ui',
        'jquery/jquery.parsequery'
    ],
    function ($, _, $t, dropDownTmpl, radioTmpl, checkboxTmpl, emptyTmpl, replacer) {
        'use strict';

        /**
         * Base widget. Used to render swatch images beside the option or when a value is selected
         * and for replacing the main gallery image on the product page, in case this option is enabled in
         * the option config.
         */
        $.widget('mageworx.optionAdditionalImages', {
            options: {
                customOptionClassSelector: '.product-custom-option',
                imagesContainerClass: 'option_images_gallery',
                currentOptionId: null,
                images: [],
                $element: null,
                templates: {
                    'drop_down': dropDownTmpl,
                    'radio': radioTmpl,
                    'checkbox': checkboxTmpl,
                    'swatch': dropDownTmpl,
                    'multiswatch': emptyTmpl
                },
                image_replacement_candidates: {}
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
                var self = this,
                params = this.options;

                $(params.customOptionClassSelector).each(function () {
                    var $element = $(this);
                    params.$element = $element;
                    var optionId = self._resolveOptionId();

                    if (!optionId) {
                        return;
                    }
                    params.currentOptionId = optionId;

                    var optionType = self.getOptionType();
                    if (!optionType ||
                    typeof params.render_images_for_option_types == 'undefined' ||
                    params.render_images_for_option_types.indexOf(optionType) == -1) {
                        return;
                    }

                    var imagesContainer = '<div class="' + params.imagesContainerClass + '"/>';
                    $element.parent().append(imagesContainer);

                    if (self.getOGType() == self.getOGTypeBesideOption()) {
                        self._elementChange();
                    }

                    if (self.getOptionType() == 'drop_down') {
                        self._observeStyleOptions();
                    }
                });
            },

            _observeStyleOptions: function () {
                var self = this,
                    params = this.options,
                    target = params.$element.find('option');

                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutationRecord) {
                        var $element = $(mutationRecord.target).closest('.product-custom-option');
                        params.$element = $element;
                        params.currentOptionId = self._resolveOptionId();
                        self._elementChange();
                    });
                });

                $.each(target, function (i, e) {
                    observer.observe(e, {attributes: true, attributeFilter: ['style']});
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
                var params = this.options;
                params.$element = $(option);
                params.currentOptionId = this._resolveOptionId();
                var optionType = this.getOptionType();
                if (!optionType ||
                typeof params.render_images_for_option_types == 'undefined' ||
                params.render_images_for_option_types.indexOf(optionType) == -1) {
                    return;
                }
                this._elementChange();
            },

            /**
             * Main method.
             * Change images, mark them as selected, collect candidates for the replacement (main-image),
             * trigger replace.
             *
             * @private
             */
            _elementChange: function () {
                this.clearImagesContainer();

                var valueId = this.options.$element.val();
                if (this.getOGType() != this.getOGTypeDisabled()) {
                    if (!valueId) {
                        // Empty value id
                        var sortOrder = this.getOptionValueSortOrder(this.getOptionId(), null);
                        this._removeCandidateForReplacement(sortOrder);
                        replacer.forceRefresh();
                    } else if (!valueId && this.getOGType() != this.getOGTypeBesideOption()) {
                        // Empty value id && OG Type is not Beside Option
                        return;
                    }
                }

                this._renderImages(valueId);
                replacer.replace();
            },

            /**
             * Find option id by parsing option html
             *
             * @returns {*}
             * @private
             */
            _resolveOptionId: function () {
                var id = this.options.$element.attr('id');
                id = id.replace('select_', '')
                    .replace('options_', '');
                if (id.match(/_/)) {
                    return id.split('_')[0];
                }

                return id;
            },

            /**
             * Find corresponding image from list of candidates and replace main image
             *
             * @param valueId
             * @private
             */
            _renderImages: function (valueId) {
                var images = this._prepareOptionImages(valueId),
                    params = this.options,
                    currentOptionGalleryTemplate = this._resolveTemplateByOptionType(this.getOptionType());

                if (Object.keys(images).length > 0) {
                    if (this.getOGType() == this.getOGTypeBesideOption() || this._isValueSelected()) {
                        if (this.getOptionType() == 'radio' && this.getOGType() != this.getOGTypeBesideOption()) {
                            this._clearRadioImagesContainer();
                        }
                        var template = _.template(currentOptionGalleryTemplate)({"images": images});

                        var $imagesContainer = this.getOptionGalleryContainer();
                        $imagesContainer.append(template);
                    }
                } else {
                    // Images is empty
                    var sortOrder = this.getOptionValueSortOrder(this.getOptionId(), null);
                    this._removeCandidateForReplacement(sortOrder);
                    replacer.forceRefresh();
                }
            },

            getOptionValueSortOrder: function (optionId, valueId) {
                var params = this.options,
                    sortOrder = params.options[optionId].sort_order * 100;
                if (!valueId) {
                    return sortOrder;
                }

                if (params.$element.is('input[type="checkbox"]') || params.$element.is('select[multiple="multiple"]')) {
                    sortOrder += params.options[optionId]['values'][valueId].sort_order * 1;
                }

                return sortOrder;
            },

            /**
             * Collect all images and image candidates for the replacement
             *
             * @param valueId
             * @returns {{}}
             * @private
             */
            _prepareOptionImages: function (valueId) {
                var images = {},
                    optionId = this.getOptionId(),
                    params = this.options;
                if (valueId &&
                    typeof params.options[optionId]['values'][valueId] != 'undefined' &&
                    typeof params.options[optionId]['values'][valueId]['images'] != 'undefined') {
                    images = $.extend(true, {}, params.options[optionId]['values'][valueId]['images']);
                }

                if (typeof params.$element == 'undefined' || !params.$element instanceof jQuery) {
                    return;
                }

                if (params.options[optionId]['mageworx_option_image_mode'] != 0) {
                    for (var imageKey in images) {
                        images[imageKey]['additional_class'] = 'mageworx-optionfeatures-option-gallery_image_selected';
                        var sortOrder = this.getOptionValueSortOrder(optionId, valueId);
                        if (images[imageKey]['replace_main_gallery_image'] == '1') {
                            if (this.isElementSelected()) {
                                this._addCandidateForReplacement(images[imageKey], sortOrder);
                            } else {
                                this._removeCandidateForReplacement(sortOrder);
                            }
                        }
                    }
                }

                if (this.getOptionType() == 'drop_down' && this.getOGType() == this.getOGTypeBesideOption()) {
                    var values = params.options[optionId]['values'];
                    for (var valueKey in values) {
                        if (valueKey == valueId ||
                        typeof values[valueKey]['images'] == 'undefined') {
                            continue;
                        }

                        var imagesClone = {};
                        var $swatches = params.$element.parent().find('.mageworx-swatch-option');
                        if ($swatches.length > 0) {
                            $.each($swatches, function (index, element) {
                                var imageOptionId = $(element).attr('option-id');
                                var imageOptionTypeId = $(element).attr('option-type-id');
                                if ($(element).css('display') != 'none' &&
                                !_.isUndefined(params.options[imageOptionId]['values'][imageOptionTypeId]['images']) &&
                                params.$element.closest('[option_id]').css('display') != 'none') {
                                    imagesClone = $.extend(true, imagesClone, params.options[imageOptionId]['values'][imageOptionTypeId]['images']);
                                }
                            });
                        } else {
                            $(params.$element).find('option').each(function () {
                                var imageOptionId = params.currentOptionId;
                                var imageOptionTypeId = $(this).val();
                                if (imageOptionTypeId &&
                                    params.$element.closest('[option_id]').css('display') != 'none' &&
                                    !_.isUndefined(params.options[imageOptionId]['values'][imageOptionTypeId]['images']) &&
                                    $(this).css('display') != 'none'
                                ) {
                                    imagesClone = $.extend(true, imagesClone, params.options[imageOptionId]['values'][imageOptionTypeId]['images']);
                                }
                            });
                        }

                        _.extend(images, imagesClone);
                    }
                }

                return images;
            },

            /**
             * Save candidate for replacement in the replacer
             *
             * @param image
             * @param sortOrder
             * @private
             */
            _addCandidateForReplacement: function (image, sortOrder) {
                replacer.addCandidate(image, sortOrder);
            },

            /**
             * Remove candidate for replacement from replaces cache
             *
             * @param sortOrder
             * @private
             */
            _removeCandidateForReplacement: function (sortOrder) {
                replacer.removeCandidate(sortOrder);
            },

            /**
             * Clear html container
             */
            clearImagesContainer: function () {
                var params = this.options;
                var $imagesContainer = this.getOptionGalleryContainer();
                if (typeof $imagesContainer != 'undefined' &&
                $imagesContainer instanceof jQuery) {
                    $imagesContainer.html('');
                }
            },

            /**
             * Clear html container for all radiobuttons of this option
             */
            _clearRadioImagesContainer: function () {
                var params = this.options,
                    $imagesContainer = this.getOptionGalleryContainer(),
                    $radioListContainer = $imagesContainer.parent().parent();
                $radioListContainer.find('input:radio').each(function () {
                    $(this).parent().find('.option_images_gallery').html('');
                })
            },

            /**
             * Check if option in Once Selected option gallery mode and value is selected
             */
            _isValueSelected: function () {
                var params = this.options;
                return this.getOGType() == this.getOGTypeOnceSelected() &&
                (params.$element.is(':checked') ||
                (this.getOptionType() == 'drop_down' && params.$element.val()))
            },

            /**
             * Returns corresponding HTML template for the current option type
             *
             * @param optionType
             * @returns {*}
             * @private
             */
            _resolveTemplateByOptionType: function (optionType) {
                return this.options.templates[optionType];
            },

            /**
             * Returns current option type
             * @returns string
             */
            getOptionType: function () {
                if (_.isUndefined(this.options.options[this.getOptionId()])) {
                    return '';
                }
                return this.options.options[this.getOptionId()]['type'];
            },

            /**
             * Returns current option id
             *
             * @returns int
             */
            getOptionId: function () {
                return this.options.currentOptionId;
            },

            /**
             * Returns option type disabled value
             *
             * @returns int
             */
            getOGTypeDisabled: function () {
                return this.options.option_gallery_type.disabled;
            },

            /**
             * Returns option type beside option value
             *
             * @returns int
             */
            getOGTypeBesideOption: function () {
                return this.options.option_gallery_type.beside_option;
            },

            /**
             * Returns option type once selected value
             *
             * @returns int
             */
            getOGTypeOnceSelected: function () {
                return this.options.option_gallery_type.once_selected;
            },

            /**
             * Get current options OG type
             *
             * @returns int
             */
            getOGType: function () {
                return this.options.options[this.getOptionId()]['mageworx_option_gallery'];
            },

            /**
             * Get option gallery container
             *
             * @returns object
             */
            getOptionGalleryContainer: function () {
                return this.options.$element.parent().find('.' + this.options.imagesContainerClass);
            },

            /**
             * Check current element is selected (in html)
             *
             * @returns boolean
             */
            isElementSelected: function () {
                var $element = this.options.$element;
                if ($element.is('input:not([type="button"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]), textarea, select')) {
                    return Boolean($element.val());
                } else if ($element.is('input[type="radio"]') || $element.is('input[type="checkbox"]')) {
                    return $element.is(':checked');
                }

                return false;
            }
        });

        return $.mageworx.optionAdditionalImages;
    }
);
