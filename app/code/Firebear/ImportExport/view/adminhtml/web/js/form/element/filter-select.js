/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
    'jquery',
    'underscore',
    'Firebear_ImportExport/js/form/element/select',
    'uiRegistry'
    ],
    function ($, _, Select, reg) {
        'use strict';

        return Select.extend(
            {
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
