<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductOptionPromoteItemOptionAvailable extends ItemOptionAvailable implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        /** @var Item $item */
        $item = $observer->getData('item');

        /** @var Option $productOption */
        $productOption = $observer->getData('product_option');

        /** @var DataObject $result */
        $checkResult = $observer->getData('result');

        $isAvailable = $checkResult->getData('is_available');

        if ($isAvailable) {
            $isAvailable = $this->isDataObjectAvailable(
                $item,
                $productOption
            );
        }

        $checkResult->setData(
            'is_available',
            $isAvailable
        );
    }
}
