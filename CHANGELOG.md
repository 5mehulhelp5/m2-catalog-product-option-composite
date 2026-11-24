# Changelog

## 6.0.0

### Breaking changes

- Requires Magento 2.4.6 or newer and PHP 8.2 or newer. Older versions of Magento and PHP 7.1 to 8.1 are no longer
  supported; use version 5.x for these.
- Requires `infrangible/m2-core` 5.0, `infrangible/m2-catalog-product-option` 2.0,
  `infrangible/m2-catalog-product-option-price` 3.0, `infrangible/m2-catalog-product-option-wrapper` 2.0,
  `infrangible/m2-bundle-option-selection` 2.0, `infrangible/m2-bundle-option-wrapper` 2.0,
  `infrangible/m2-catalog-product-swatches` 2.0, `infrangible/m2-foundation` 3.0 and `fewedev/base` 2.4.3.
- All classes have native types and use constructor property promotion; most constructors changed. `Helper\Data` takes
  the event manager interface instead of `Magento\Framework\EntityManager\EventManager`, `Helper\Output` no longer
  extends `AbstractHelper`, and the controllers no longer extend `Magento\Framework\App\Action\Action` and
  `Magento\Checkout\Controller\Cart`.
- The option value methods of `Helper\Data` take `Magento\Catalog\Model\Product\Option\Value` instead of
  `ProductCustomOptionValuesInterface`.
- The JavaScript widget of bundle products is `mage.bundleProductOptionsComposite` and extends
  `mage.productOptionsComposite`; before, it replaced it under the same name.
- The tables are changed by `etc/db_schema.xml` and the product attributes are added by a data patch, instead of
  `Setup/InstallSchema.php`, `UpgradeSchema.php` and `UpgradeData.php`. New installations no longer get the unused
  columns `allow_product_ids`, `prohibit_product_ids` and `prohibit_hide_product_ids`; existing installations keep
  them.

### Fixed

- Options restricted to children of configurable products were never available for configurable cart items (promoted
  options, offers): the check compared the configurable product instead of the selected child.
- The link "Edit Options" of bundle items in the cart used the route ID instead of the front name of the page.
- Text, file and date options of configurable products were not hidden when another child was selected.
- An option without restriction that was loaded on its own counted as restricted to an empty product ID.
- Adding several bundle products in one request failed, because the added item was registered twice.
- When a bundle was added to the cart, options restricted to products of bundle options with several selected products
  lost their values, and requests without bundle selections failed.
- The page of the custom options of a bundle item:
  - removed the values of text, file and date options when it was saved;
  - showed all values of radio buttons and checkboxes instead of those for the product;
  - had element IDs that were not unique for bundle options with several selected products;
  - wrote PHP code into the `data-selector` of radio buttons and checkboxes;
  - did not link the labels of drop-downs to them;
  - showed the subline without escaping.
- Option titles and the texts of the page were loaded from the admin scope instead of the store view.
- The children of options were validated without anchors, and children saved as text were removed.
- The product attributes of the page were in no attribute set, and five of them not visible, so they could not be
  used; the data patch makes them visible and adds them to the default group of every attribute set (bundle products
  only).

### Added

- Translations in 24 languages.
- Unit tests.
- README with the restrictions, the cart page, its attributes and the events.

### Changed

- The JavaScript compares product IDs as text, so numeric IDs of swatches match too.
- The observer of saved models creates the product helper only when an option is saved.
- The module is loaded after the modules it uses.
- Checks with PHPStan level 9, the Magento coding standard and unit tests on every push.
