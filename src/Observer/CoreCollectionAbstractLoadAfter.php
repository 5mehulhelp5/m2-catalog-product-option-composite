<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CoreCollectionAbstractLoadAfter implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $collection = $observer->getData('collection');

        if ($collection instanceof Collection ||
            $collection instanceof \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection) {

            /** @var Value $object */
            foreach ($collection as $object) {
                $allowHideProductIds = $object->getData('allow_hide_product_ids');

                if ($allowHideProductIds && ! is_array($allowHideProductIds)) {
                    $object->setData(
                        'allow_hide_product_ids',
                        explode(
                            ',',
                            $allowHideProductIds
                        )
                    );
                }
            }
        }
    }
}
