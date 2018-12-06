/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/ui-select',
    'jquery',
], function (Select,jQuery) {
    'use strict';

    return Select.extend({
        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data);

            if (data.error) {
                return this;
            }
            var options = this.options();
            
            var newCategoryPath = '';
            jQuery.ajax(
                {
                    type      : "POST",
                    data      : {'categoryId': data.category['entity_id']},
                    showLoader: true,
                    url       : self.BASE_URL+'job/categoryNew',
                    dataType  : "json",
                    async: false,
                    success   : function (result, status) {
                        newCategoryPath = result.data;
                    },
                    error     : function () {
                        self.error($t('Error on General : Error with loading category path.'));
                    },
                }
            );
            option.label = newCategoryPath;
            option.value = newCategoryPath;
            options.push(option);
            this.options(options);
        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            return {
                'is_active': data.category['is_active'],
                value      : data.category.name,
                label      : data.category.name,
                path       : ''
            };
        }
    });
});
