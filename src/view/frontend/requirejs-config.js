/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

var config = {
    map: {
        '*': {
            productOptionsComposite: 'Infrangible_CatalogProductOptionComposite/js/product-options-composite',
            bundleProductOptionsComposite: 'Infrangible_CatalogProductOptionComposite/js/bundle-product-options-composite',
        }
    },
    config: {
        mixins: {
            'Infrangible_BundleOptionSelection/js/selection': {
                'Infrangible_CatalogProductOptionComposite/js/selection-mixin': true
            }
        }
    }
};
