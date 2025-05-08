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
                $('.column.main').on('swatch.changed', function (event, selectedProductId) {
                    $.each(self.options.config, function(optionId, optionData) {
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
                                        if (selectedProductId) {
                                            if (productIds.indexOf(selectedProductId) !== -1) {
                                                show = true;
                                            }
                                        }

                                        if (show) {
                                            option.show();
                                        } else {
                                            option.hide();
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
                });
            });
        }
    });

    return $.mage.productOptionsComposite;
});
