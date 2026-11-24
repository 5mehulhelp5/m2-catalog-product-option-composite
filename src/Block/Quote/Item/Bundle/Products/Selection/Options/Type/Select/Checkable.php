<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\Select;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;
use Infrangible\CatalogProductOptionComposite\Traits\TypedOptionsRenderer;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Template\Context;

/**
 * Renders a custom option with radio buttons or checkboxes of a bundle item for a product of a bundle option.
 */
class Checkable extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Checkable implements
    Type\ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;
    use TypedOptionsRenderer;

    /** @var string */
    protected $_template = 'Infrangible_CatalogProductOptionComposite::' .
        'quote/item/bundle/products/selection/options/type/select/checkable.phtml';

    /**
     * @param Context $context
     * @param PricingHelper $pricingHelper
     * @param CatalogHelper $catalogData
     * @param Data $helper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        PricingHelper $pricingHelper,
        CatalogHelper $catalogData,
        protected Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    /**
     * Returns the values of the option that are available for the product of the bundle option.
     *
     * @return Product\Option\Value[]
     */
    public function getAvailableValues(): array
    {
        $values = [];

        foreach ($this->getOption()->getValues() ?? [] as $value) {
            if ($value instanceof Product\Option\Value &&
                $this->helper->isProductOptionValueAvailableForItemBundleOptionSelection(
                    $value,
                    $this->getItem(),
                    $this->getBundleOption(),
                    $this->getProduct()
                )) {

                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * Returns whether a change of the option does not update the price.
     *
     * @return bool
     */
    public function getSkipJsReloadPrice(): bool
    {
        return (bool)$this->getData('skip_js_reload_price');
    }
}
