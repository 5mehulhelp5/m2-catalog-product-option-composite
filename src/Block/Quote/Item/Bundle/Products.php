<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle;
use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

/**
 * The products of a bundle item in the cart, each with its custom options.
 */
class Products extends Bundle
{
    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param Data $helper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        protected Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registryHelper, $data);
    }

    /**
     * Returns the bundle options and selected products of the item.
     *
     * @return array<int, array{option: Option, product: Product}>
     */
    public function getItemBundleSelections(): array
    {
        return $this->helper->getItemBundleSelections($this->getItem());
    }

    /**
     * Renders the product of the bundle option.
     *
     * @param Option $bundleOption
     * @param Product $product
     *
     * @return string
     */
    public function getSelectionHtml(Option $bundleOption, Product $product): string
    {
        $selectionBlock = $this->getChildBlock('quote.item.bundle.products.selection');

        if (! $selectionBlock instanceof Selection) {
            return '';
        }

        $selectionBlock->setBundleOption($bundleOption);
        $selectionBlock->setProduct($product);

        return $selectionBlock->toHtml();
    }
}
