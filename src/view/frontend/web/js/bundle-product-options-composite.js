/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'domReady',
    'Infrangible_CatalogProductOptionComposite/js/product-options-composite',
    'bundleOptionSelection'
], function ($, domReady, productOptionsComposite) {
    'use strict';

    return $.widget('mage.bundleProductOptionsComposite', productOptionsComposite, {
        options: {
            productBundleTriggerSelector: '.bundle-options-container',
            bundleOptionId: null
        },

        _init: function() {
            const self = this;

            domReady(function() {
                const container = $(self.options.productBundleTriggerSelector);

                container.on('bundle_option_changed', function(event, bundleOptionId, selectedProductIds) {
                    if (parseInt(self.options.bundleOptionId) === parseInt(bundleOptionId) &&
                        selectedProductIds !== null && selectedProductIds !== undefined) {

                        self.handleAllowHide(
                            Array.isArray(selectedProductIds) ? selectedProductIds : [selectedProductIds]);
                    }
                });

                container.trigger('bundle_product_options_composite_initialized', [self.options.bundleOptionId]);
            });
        }
    });
});
