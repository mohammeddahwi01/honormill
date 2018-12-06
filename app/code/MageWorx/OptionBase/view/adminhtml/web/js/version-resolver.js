/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return {
        versionCompare: function (v1, v2, options) {
            var lexicographical = options && options.lexicographical,
            zeroExtend = options && options.zeroExtend,
            v1parts = v1.split('.'),
            v2parts = v2.split('.');

            function isValidPart(x)
            {
                return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
            }

            if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
                return NaN;
            }

            if (zeroExtend) {
                while (v1parts.length < v2parts.length) v1parts.push("0");
                while (v2parts.length < v1parts.length) v2parts.push("0");
            }

            if (!lexicographical) {
                v1parts = v1parts.map(Number);
                v2parts = v2parts.map(Number);
            }

            for (var i = 0; i < v1parts.length; ++i) {
                if (v2parts.length == i) {
                    return 1;
                }

                if (v1parts[i] == v2parts[i]) {
                    continue;
                } else if (v1parts[i] > v2parts[i]) {
                        return 1;
                    } else {
                        return -1;
                    }
            }

            if (v1parts.length != v2parts.length) {
                return -1;
            }

            return 0;
        },

        isSince22x: function () {
            var magentoVersion = '2.2.0',
            magentoCompareVersion = '2.2.0',
            isSince22x = 0,
            versionText = $('.magento-version').first().text();
            if (!_.isUndefined(versionText)) {
                var versionArray = versionText.split('.');
                if (!_.isUndefined(versionArray[1]) && !_.isUndefined(versionArray[2]) && !_.isUndefined(versionArray[3])) {
                    magentoVersion = parseInt(versionArray[1]) + '.' +
                    parseInt(versionArray[2]) + '.' +
                    parseInt(versionArray[3]);
                }

                isSince22x = this.versionCompare(magentoVersion, magentoCompareVersion);
            }

            return isSince22x;
        }
    };
});