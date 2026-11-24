<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Converts the children of a loaded option or option value into a list.
 */
class ModelLoadAfter implements ObserverInterface
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Converts the children of the loaded option or option value into a list.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $object = $observer->getData('object');

        if (($object instanceof Option || $object instanceof Value) && $object->hasData(Data::ALLOW_HIDE_PRODUCT_IDS)) {
            $object->setData(Data::ALLOW_HIDE_PRODUCT_IDS, $this->helper->getAllowHideProductIds($object));
        }
    }
}
