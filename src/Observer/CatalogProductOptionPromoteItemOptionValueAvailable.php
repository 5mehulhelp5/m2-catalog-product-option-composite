<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductOptionPromoteItemOptionValueAvailable implements ObserverInterface
{
    /** @var Data */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer): void
    {
        /** @var Item $item */
        $item = $observer->getData('item');

        /** @var Value $productOptionValue */
        $productOptionValue = $observer->getData('product_option_value');

        /** @var DataObject $result */
        $checkResult = $observer->getData('result');

        $isAvailable = $checkResult->getData('is_available');

        if ($isAvailable) {
            $isAvailable = $this->helper->isProductOptionValueAvailableForItem(
                $productOptionValue,
                $item
            );
        }

        $checkResult->setData(
            'is_available',
            $isAvailable
        );
    }
}
