/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'MageWorx_OptionBase/dynamicRows21x',
    'MageWorx_OptionBase/dynamicRows22x',
    'MageWorx_OptionBase/versionResolver'
], function (dynamicRows21x, dynamicRows22x, versionResolver) {
    'use strict';

    if (versionResolver.isSince22x() != -1) {
        return dynamicRows22x;
    } else {
        return dynamicRows21x;
    }
});