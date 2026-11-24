# Infrangible Catalog Product Option Composite

Restricts custom options of configurable and bundle products to some of their children. An option or option value
that is restricted to children is only shown and only added to the cart when one of these children is selected.

## Restricting custom options

The custom options of configurable and bundle products have the field **Allow & Hide** in the product form, for the
option and for every value of options with values. It lists the children of a configurable product, or the products of
the options of a bundle product, grouped by bundle option. Options and values without selected children are available
for all children.

- **Configurable products:** the product page shows and hides the options and values when a child is selected with
  the swatches or drop-downs.
- **Bundle products:** custom options that are restricted to products of a bundle option are shown below this bundle
  option, with their own price box, and only when one of the products is selected. When the bundle is added to the
  cart, the values of options for products that were not selected are removed.

## Custom options of bundle items in the cart

Bundle items in the cart get the link **Edit Options** (Stores > Configuration > Infrangible Solutions > Catalog
Product Option Composite > General > Show Composite Url, default: yes). The page lists the products of the bundle item
with the custom options that are available for them and saves the changed values to the item.

Attributes of bundle products for this page, in the default group of every attribute set:

| Attribute                              | Use                                                                        |
|----------------------------------------|----------------------------------------------------------------------------|
| `quote_item_bundle_headline`           | title of the page, the product name if empty                               |
| `quote_item_bundle_subline`            | subline below the title                                                    |
| `quote_item_bundle_description`        | description below the subline                                              |
| `quote_item_bundle_back_button_text`   | text of the link back to the cart, "Go Back" if empty                      |
| `quote_item_bundle_update_button_text` | text of the button that saves the options, "Update Shopping Cart" if empty |
| `quote_item_bundle_redirect`           | opens the page after the bundle was added to the cart                      |
| `quote_item_bundle_redirect_threshold` | opens it only if the price of the selected custom options is below this    |

## Events

| Event                                                                       | Data                                                                                |
|-----------------------------------------------------------------------------|-------------------------------------------------------------------------------------|
| `catalog_product_option_composite_option_available`                         | `product_option`, `bundle_option`, `product`, `result` (`is_available`)             |
| `catalog_product_option_composite_option_value_available`                   | `product_option_value`, `bundle_option`, `product`, `result` (`is_available`)       |
| `catalog_product_option_composite_item_option_available`                    | `product_option`, `item`, `bundle_option`, `product`, `result` (`is_available`)     |
| `catalog_product_option_composite_item_option_value_available`              | `product_option_value`, `item`, `bundle_option`, `product`, `result` (`is_available`) |
| `catalog_product_option_composite_quote_item_bundle_select`                 | `option`, `result` (`use_multiple_block`, `use_checkable_block`)                    |
| `catalog_product_option_composite_quote_item_bundle_update_item_selection`  | `request`, `quote`, `cart`, `item`, `bundle_option`, `selection`, `product_options`, `cart_data` |
| `catalog_product_option_composite_quote_item_bundle_update_item`            | `request`, `quote`, `cart`, `item`, `cart_data`                                     |
| `catalog_product_option_composite_quote_item_bundle_update`                 | `request`, `quote`, `cart`, `cart_data`                                             |

The module also restricts the custom options of
[Infrangible Catalog Product Option Promote](https://www.infrangible.dev/modules/catalog-product-option-promote) and
[Infrangible Catalog Product Option Offer](https://www.infrangible.dev/modules/catalog-product-option-offer) to the
selected children of cart items.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.6 or newer
- PHP 8.2 or newer

## Installation

```bash
composer require infrangible/m2-catalog-product-option-composite
bin/magento module:enable Infrangible_CatalogProductOptionComposite
bin/magento setup:upgrade
```

In production mode, also run `bin/magento setup:di:compile`.

## License

Infrangible Catalog Product Option Composite is licensed under the MIT License - see the LICENSE file for details.
