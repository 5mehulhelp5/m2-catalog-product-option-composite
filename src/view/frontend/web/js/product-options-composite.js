/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'domReady'
], function ($, domReady) {
    'use strict';

    var globalOptions = {
        config: {}
    };

    $.widget('mage.productOptionsComposite', {
        options: globalOptions,

        _create: function createProductOptionsComposite() {
        },

        _init: function initProductOptionsComposite() {
            var self = this;

            domReady(function() {
                $('.column.main').on('swatch.changed bundle.changed', function (event, selectedProductIds) {
                    self.handleAllowHideProductIds(
                        Array.isArray(selectedProductIds) ? selectedProductIds : [selectedProductIds]);
                });
            });
        },

        handleAllowHideProductIds: function handleAllowHideProductIds(selectedProductIds) {
            $.each(this.options.config.allowHideProductIds, function(optionId, optionData) {
                var option = $('#product-option-' + optionId);
                var select = option.find('#select_' + optionId);

                if (select.length > 0) {
                    var options = select.find('option');

                    $.each(optionData, function(optionValueId, productIds) {
                        options.each(function() {
                            var option = $(this);
                            var optionValue = option.val();

                            if (optionValue === optionValueId) {
                                var show = false;
                                if (selectedProductIds) {
                                    var isSubSelection = false;
                                    $.each(selectedProductIds, function(key, selectedProductId) {
                                        if (Array.isArray(selectedProductId)) {
                                            isSubSelection = true;
                                        }
                                    });

                                    if (isSubSelection) {
                                        $.each(selectedProductIds, function(groupId, selectedProductIdsList) {
                                            if (selectedProductIdsList && productIds[groupId]) {
                                                $.each(selectedProductIdsList, function(key, selectedProductId) {
                                                    if (productIds[groupId].indexOf(selectedProductId) !== -1) {
                                                        show = true;
                                                    }
                                                });
                                            }
                                        });
                                    } else {
                                        $.each(selectedProductIds, function(key, selectedProductId) {
                                            if (productIds.indexOf(selectedProductId) !== -1) {
                                                show = true;
                                            }
                                        });
                                    }
                                }

                                if (show) {
                                    option.show();
                                    option.addClass('product-options-composite-show');
                                    option.removeClass('product-options-composite-hide');
                                } else {
                                    option.hide();
                                    option.addClass('product-options-composite-hide');
                                    option.removeClass('product-options-composite-show');
                                }
                            }
                        });
                    });

                    var availableOptions = 0;
                    options.each(function() {
                        if ($(this).css('display') !== 'none' && $(this).val()) {
                            availableOptions++;
                        }
                    });

                    if (availableOptions === 0) {
                        option.hide();
                        select.val('');
                        select.trigger('change');
                    } else {
                        option.show();
                    }
                }
            });
        }
    });

    return $.mage.productOptionsComposite;
});
