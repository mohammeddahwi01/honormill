/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'jquery',
        'Magento_Ui/js/form/form',
        './adapter',
        'uiRegistry',
        'Magento_Ui/js/lib/spinner',
        'rjsResolver'
    ],
    function ($, Form, adapter, reg, loader, resolver) {
        'use strict';

        /**
         * Collect form data.
         *
         * @param {Array} items
         * @returns {Object}
         */
        function collectData(items) {
            var result = {};

            items = Array.prototype.slice.call(items);

            items.forEach(function (item) {
                switch (item.type) {
                    case 'checkbox':
                        result[item.name] = +!!item.checked;
                        break;

                    case 'radio':
                        if (item.checked) {
                            result[item.name] = item.value;
                        }
                        break;

                    default:
                        result[item.name] = item.value;
                }
            });

            return result;
        }

        return Form.extend(
            {
                defaults: {
                  nameModal:'',
                },
                initialize: function () {
                    this._super();
                    resolver(this.checkButton, this);

                    return this;
                },
                initAdapter: function () {
                    adapter.on({
                        'reset': this.reset.bind(this),
                        'save': this.save.bind(this, true, {}),
                        'saveAndContinue': this.save.bind(this, false, {}),
                        'saveAndRun': this.saveAndRun.bind(this, {})
                    }, this.selectorPrefix, this.eventPrefix);

                    return this;
                },

                destroyAdapter: function () {
                    adapter.off([
                        'reset',
                        'save',
                        'saveAndContinue',
                        'saveAndRun'
                    ], this.eventPrefix);

                    return this;
                },
                saveAndRun: function (data) {
                    this.validate();

                    if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                        this.setAdditionalData(data)
                            .extSubmit(true);
                    }
                },
                extSubmit: function (redirect) {
                    localStorage.removeItem('jobId');
                    loader.get(this.name).show();
                    var self = this;
                    var additional = collectData(this.additionalFields),
                        source = this.source;
                    _.each(additional, function (value, name) {
                        source.set('data.' + name, value);
                    });
                    var postData = this.source.get('data');

                    var type = reg.get(self.ns + "." + self.ns + ".source.import_source");
                    if (typeof type != 'undefined') {
                    if (type.value() == 'google') {
                        var filePath = reg.get(self.ns + "." + self.ns + ".source.google_file_path");
                        postData.file_path = filePath.value();
                    }
                }
                   postData = this.recorrectData(postData);
                    console.log(postData);
                    $.ajax({
                        type: "POST",
                        url: this.source.submit_url,
                        data: postData,
                        success: function (msg) {
                            loader.get(self.name).hide();
                            if (msg != false) {
                                localStorage.setItem('jobId', msg);
                                reg.set(self.namespace + "." + self.namespace + ".general.entity:value", msg);
                                reg.get(self.namespace + "." + self.namespace + "." + self.nameModal).toggleModal();
                            }
                        }
                    });
                },

                checkButton: function() {
                    reg.get('import_job_form.import_job_form.general.title', function (name) {
                        if (name.value()) {
                            reg.get('import_job_form.import_job_form.source.check_button', function (object) {
                                object.update = 1;
                                object.validateGeneral();
                            });
                            reg.get('import_job_form.import_job_form.source_data_map_container_category.load_categories_button', function (object) {
                                object.loadForm();
                            });
                        }
                    });
                },
                recorrectData: function (data) {
                    var self = this;
                    console.log("dddd");
                    var objects = reg.get(self.ns + "." + self.ns + ".source_data_filter_container.source_filter_map");
                    if (typeof objects != 'undefined') {
                        console.log("ddd2");
                        var list = [];
                        _.each(objects.elems(), function (elem, index) {
                            var listSecond = {};
                            console.log(elem.elems());

                            _.each(elem.elems(), function (elemTop) {
                                listSecond[elemTop.prefixName] = elemTop.value();
                            });
                            list[index] = listSecond;
                        });
                        if (_.size(list) > 0) {
                            data.source_filter_field.delete = [];
                            data.source_filter_field.entity = [];
                            data.source_filter_field.order = [];
                            data.source_filter_field.value = [];
                            data.source_filter_filter.value = [];
                            _.each(list, function(elem, index) {
                                data.source_filter_field.delete[index] = "";
                                data.source_filter_field.entity[index] = elem["source_filter_field.entity"];
                                data.source_filter_field.order[index] = "";
                                data.source_filter_field.value[index] = elem["source_filter_field.value"];
                                data.source_filter_filter.value[index] = elem["source_filter_filter.value"];
                            });
                        };
                    }

                    return data;
                }
            }
        );
    }
);