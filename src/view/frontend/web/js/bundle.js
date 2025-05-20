/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'priceUtils',
    'domReady'
], function ($, utils, domReady) {
    'use strict';

    var globalOptions = {
        index: {},
        productBundleSelector: 'input.bundle.option, select.bundle.option, textarea.bundle.option'
    };

    $.widget('mage.catalogProductCompositeBundle', {
        options: globalOptions,

        _create: function createBundle() {
        },

        _init: function initBundle() {
            var self = this;

            domReady(function() {
                var form = self.element;
                var options = $(self.options.productBundleSelector, form);

                options.on('change', function() {
                    var selectedProductIds = [];

                    options.each(function() {
                        var element = $(this);
                        var optionType = element.prop('type');
                        var optionId = utils.findOptionId(element[0]);
                        var optionValue = element.val() || null;

                        if (optionValue) {
                            var optionIndex = self.options.index[optionId];

                            if (optionIndex) {
                                var selectedProductId;

                                switch (optionType) {
                                    case 'select-one':
                                    case 'hidden':
                                        selectedProductId = optionIndex[optionValue];
                                        if (selectedProductId) {
                                            selectedProductIds.push(selectedProductId);
                                        }
                                        break;

                                    case 'select-multiple':
                                        if (Array.isArray(optionValue)) {
                                            $.each(optionValue, function(key, optionValueValue) {
                                                selectedProductId = optionIndex[optionValueValue];
                                                if (selectedProductId) {
                                                    selectedProductIds.push(selectedProductId);
                                                }
                                            });
                                        }
                                        break;

                                    case 'radio':
                                    case 'checkbox':
                                        if (element.is(':checked')) {
                                            selectedProductId = optionIndex[optionValue];
                                            if (selectedProductId) {
                                                selectedProductIds.push(selectedProductId);
                                            }
                                        }
                                        break;
                                }
                            }
                        }
                    });

                    $('.column.main').trigger('bundle.changed', [selectedProductIds]);
                });
            });
        }
    });

    return $.mage.catalogProductCompositeBundle;
});
