/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'domReady'
], function ($, domReady) {
    'use strict';

    const selectionWidgetMixin = {
        _init: function() {
            const self = this;

            self._super();

            domReady(function() {
                $(self.options.productBundleTriggerSelector).on('bundle_product_options_composite_initialized', function(event, optionId) {
                    console.debug('bundle product options composite initialized for option with id: ' + optionId);

                    const selectedProductIds = self.collectSelectedProductIds();

                    $(self.options.productBundleTriggerSelector).trigger('bundle_option_changed',
                        [optionId, selectedProductIds[optionId]]);
                });
            });
        }
    };

    return function (targetWidget) {
        // $.widget() returns the constructor, which is $.infrangible.bundleOptionSelection
        return $.widget('infrangible.bundleOptionSelection', targetWidget, selectionWidgetMixin);
    };
});
