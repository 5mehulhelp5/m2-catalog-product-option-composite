/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    var mixin = {
        findOptionId: function (originalFn, element) {
            var re, id, name;

            if (! element) {
                return;
            }

            name = $(element).attr('name');

            var matches = (name.match(/\[([^\]]+)\]/g) || []).length;

            if (matches > 1) {
                re = /\[([^\]]+)\]/g;

                var match;
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