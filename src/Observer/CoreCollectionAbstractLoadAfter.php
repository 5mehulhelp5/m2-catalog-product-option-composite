<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as ValueCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Converts the children of loaded options and option values into a list.
 */
class CoreCollectionAbstractLoadAfter implements ObserverInterface
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Converts the children of the options or option values of the loaded collection into a list.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $collection = $observer->getData('collection');

        if (! $collection instanceof Collection && ! $collection instanceof ValueCollection) {
            return;
        }

        foreach ($collection as $object) {
            if ($object instanceof DataObject && $object->hasData(Data::ALLOW_HIDE_PRODUCT_IDS)) {
                $object->setData(Data::ALLOW_HIDE_PRODUCT_IDS, $this->helper->getAllowHideProductIds($object));
            }
        }
    }
}
