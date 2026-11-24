<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item;

use Infrangible\Core\Helper\Registry;
use LogicException;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item;

/**
 * The page with the custom options of the products of a bundle item in the cart.
 */
class Bundle extends Template
{
    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(Template\Context $context, protected Registry $registryHelper, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Returns the bundle item of the page.
     *
     * @return Item
     */
    public function getItem(): Item
    {
        $item = $this->registryHelper->registry('current_item');

        if (! $item instanceof Item) {
            throw new LogicException('Item is not defined');
        }

        return $item;
    }

    /**
     * Returns the bundle product of the item.
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Renders the products of the item.
     *
     * @return string
     */
    public function getProductsHtml(): string
    {
        return $this->getChildHtml('quote.item.bundle.products');
    }
}
