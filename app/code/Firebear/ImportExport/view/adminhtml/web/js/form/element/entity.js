/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Firebear_ImportExport/js/form/element/additional-select',
    'uiLayout'
    ],
    function (_, utils, registry, Abstract, layout) {
        'use strict';

        return Abstract.extend(
            {
                defaults: {
                    code: '',
                },

                initialize    : function () {
                    this._super();
                    var elements = this.getOption(this.value());
                    if (elements != undefined) {
                        this.setCode(elements.code);
                    }
                    return this;
                },
                initObservable: function () {
                    this._super();

                    this.observe('code');

                    return this;
                },
                setCode       : function (value) {
                    this.code(value);
                },

                onUpdate: function () {
                    this._super();
                    var map = registry.get(this.ns + '.' + this.ns + '.source_data_map_container.source_data_map');
                    var mapCategory = registry.get(this.ns + '.' + this.ns + '.source_data_map_container_category.source_data_categories_map');
                    map.deleteRecords();
                    map._updateCollection();
                    mapCategory.deleteRecords();
                    mapCategory._updateCollection();
                    registry.get(this.ns + '.' + this.ns + '.source.check_button').showMap(0);
                    if (this.value() == undefined) {
                       this.setCode('');
                    } else {
                        var elements = this.getOption(this.value());
                        this.setCode(elements.code);
                    }
                },
            }
        );
    }
);
