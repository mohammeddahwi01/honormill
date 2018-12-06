/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select',
    'Firebear_ImportExport/js/form/element/general',
    'uiRegistry'
    ],
    function ($, _, Acstract, general, reg) {
        'use strict';

        return Acstract.extend(general).extend(
            {
                defaults: {
                    sourceExt       : null,
                    sourceOptions: null,
                    imports      : {
                        changeSource: '${$.parentName}.source_data_entity:value'
                    }
                },
                initConfig  : function (config) {
                    this._super();
                    this.sourceOptions = $.parseJSON(this.sourceOptions);
                    return this;
                },
                changeSource: function (value) {
                    this.sourceExt = value;
                    var oldValue = this.value();
                    if (value in this.sourceOptions) {
                        this.setOptions(this.sourceOptions[value]);
                    }
                    this.value(oldValue);
                }
            }
        )
    }
);
