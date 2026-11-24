<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * A promoted option value is only available if it is not restricted to children or the item contains one of them.
 */
class CatalogProductOptionPromoteItemOptionValueAvailable implements ObserverInterface
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Sets the result to unavailable if the option value is restricted to children that the item does not contain.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $item = $observer->getData('item');
        $productOption = $observer->getData('product_option_value');
        $checkResult = $observer->getData('result');

        if (! $item instanceof AbstractItem || ! $productOption instanceof Product\Option\Value ||
            ! $checkResult instanceof DataObject || ! $checkResult->getData('is_available')) {

            return;
        }

        if (! $this->helper->isProductOptionValueAvailableForItem($productOption, $item)) {
            $checkResult->setData('is_available', false);
        }
    }
}
