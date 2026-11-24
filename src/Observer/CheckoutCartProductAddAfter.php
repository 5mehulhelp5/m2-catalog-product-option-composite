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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Remembers the bundle item that was added to the cart, for the redirect to the page of its custom options.
 */
class CheckoutCartProductAddAfter implements ObserverInterface
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Remembers the added item if it is a bundle.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $quoteItem = $observer->getData('quote_item');
        $product = $observer->getData('product');

        if ($quoteItem instanceof Item && $product instanceof Product && $product->getTypeId() === Type::TYPE_CODE) {
            $this->helper->setAddedBundleItem($quoteItem);
        }
    }
}
