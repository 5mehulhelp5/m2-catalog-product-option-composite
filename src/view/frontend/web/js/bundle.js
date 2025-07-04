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
        productBundleSelector: 'input.bundle.option, select.bundle.option, textarea.bundle.option',
        productBundleTriggerSelector: '.column.main'
    };

    $.widget('mage.catalogProductCompositeBundle', {
        options: globalOptions,

        _create: function createBundle() {
            this.cache = {};
        },

        _init: function initBundle() {
            var self = this;

            domReady(function() {
                var form = self.element;
                var options = $(self.options.productBundleSelector, form);

                options.on('change', function() {
                    var selectedProductIds = [];

                    options.each(function() {
                        var option = $(this);
                        var optionId = utils.findOptionId(option[0]);
                        var optionValueSelectedProductIds = self.getOptionValueSelectedProductIds(option);

                        if (! (optionId in selectedProductIds)) {
                            selectedProductIds[optionId] = [];
                        }

                        $.each(optionValueSelectedProductIds, function(key, selectedProductId) {
                            selectedProductIds[optionId].push(selectedProductId);
                        });
                    });

                    console.debug('Changed bundle options has selected product ids: ' + selectedProductIds);

                    $(self.option.productBundleTriggerSelector).trigger('bundle.changed', [selectedProductIds]);

                    var option = $(this);
                    var optionId = utils.findOptionId(option[0]);

                    console.debug('Changed bundle option with id: ' +  optionId +
                        ' has selected product ids: ' + selectedProductIds[optionId]);

                    $(self.options.productBundleTriggerSelector).trigger('bundle.option.changed',
                        [optionId, selectedProductIds[optionId]]);

                });
            });
        },

        getOptionValueSelectedProductIds: function(option) {
            var self = this;

            var optionType = option.prop('type');
            var optionId = utils.findOptionId(option[0]);
            var optionValue = option.val() || null;

            var optionValueSelectedProductIds = [];

            if (optionValue) {
                var optionIndex = self.options.index[optionId];

                if (optionIndex) {
                    var selectedProductId;

                    switch (optionType) {
                        case 'select-one':
                        case 'hidden':
                            selectedProductId = optionIndex[optionValue];
                            if (selectedProductId) {
                                optionValueSelectedProductIds.push(selectedProductId);
                            }
                            break;

                        case 'select-multiple':
                            if (Array.isArray(optionValue)) {
                                $.each(optionValue, function(key, optionValueValue) {
                                    selectedProductId = optionIndex[optionValueValue];
                                    if (selectedProductId) {
                                        optionValueSelectedProductIds.push(selectedProductId);
                                    }
                                });
                            }
                            break;

                        case 'radio':
                        case 'checkbox':
                            if (option.is(':checked')) {
                                selectedProductId = optionIndex[optionValue];
                                if (selectedProductId) {
                                    optionValueSelectedProductIds.push(selectedProductId);
                                }
                            }
                            break;
                    }
                }
            }

            if (! (optionId in self.cache)) {
                self.cache[optionId] = [];
            }

            self.cache[optionId][optionValue] = [];

            $.each(optionValueSelectedProductIds, function(key, selectedProductId) {
                self.cache[optionId][optionValue].push(selectedProductId);
            });

            return optionValueSelectedProductIds;
        }
    });

    return $.mage.catalogProductCompositeBundle;
});
