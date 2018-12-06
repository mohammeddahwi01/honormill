/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function (Button, registry) {
    'use strict';

    return Button.extend({

        /**
         * Apply action on target component,
         * but previously create this component from template if it is not existed
         *
         * @param {Object} action - action configuration
         */
        applyAction: function (action) {
            var targetName = action.targetName,
                params = action.params || [],
                actionName = action.actionName,
                target;

            if (!registry.has(targetName)) {
                this.getFromTemplate(targetName);
            }
            target = registry.async(targetName);

            if (target && typeof target === 'function' && actionName) {
                if (params.length > 1) {
                    params.shift();
                }
                params.unshift(actionName);
                target.apply(target, params);
            }
        }
    });
});
