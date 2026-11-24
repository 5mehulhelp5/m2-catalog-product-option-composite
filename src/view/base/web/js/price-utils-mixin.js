/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    const mixin = {
        findOptionId: function (originalFn, element) {
            if (! element) {
                return;
            }

            const name = $(element).attr('name');

            const matches = (name.match(/\[([^\]]+)]/g) || []).length;

            if (matches > 1) {
                const re = /\[([^\]]+)]/g;

                let id;
                let match;
                while ((match = re.exec(name))) {
                    id = match[1];
                }

                if (id) {
                    return id;
                }
            }

            return originalFn(element);
        }
    };

    return function (target) {
        return wrapper.extend(target, mixin);
    };
});