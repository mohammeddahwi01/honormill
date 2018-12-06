/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'priceBox',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mageworx.optionInventory', {
        options: {
            optionConfig: {}
        },

        firstRun: function firstRun(optionConfig, productConfig, base, self)
        {
            base.setOptionValueTitle();
            $.ajax({
                url: self.options.stock_message_url,
                data: {'opConfig':JSON.stringify(optionConfig)},
                type: 'post',
                dataType: 'json'
            })
                .done(function (response) {
                    base.setOptionValueTitle();
                })
                .fail(
                    function (response) {
                        base.setOptionValueTitle();
                    }
                );
        },

        update: function update(option, optionConfig, productConfig, base)
        {
            return;
        }
    });

    return $.mageworx.optionInventory;

});