<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Offers of an option that is restricted to products of a bundle are only available if the bundle item contains
 * one of these products.
 */
class CatalogProductOptionOfferCompositeItemProductOptionAvailable implements ObserverInterface
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Sets the result to unavailable if the option is restricted to products that the bundle item does not contain.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $productOption = $observer->getData('product_option');
        $item = $observer->getData('item');
        $product = $observer->getData('product');
        $checkResult = $observer->getData('result');

        if (! $productOption instanceof Product\Option || ! $item instanceof AbstractItem ||
            ! $product instanceof Product || ! $checkResult instanceof DataObject ||
            $product->getTypeId() !== Type::TYPE_CODE) {

            return;
        }

        if (! $this->helper->isProductOptionAvailableForItem($productOption, $item)) {
            $checkResult->setData('is_available', false);
        }
    }
}
