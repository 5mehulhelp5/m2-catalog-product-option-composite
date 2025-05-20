<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ModelSaveBefore implements ObserverInterface
{
    /**
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        $object = $observer->getData('object');

        if ($object instanceof Value) {
            $allowHideProductIds = $object->getData('allow_hide_product_ids');

            if (is_array($allowHideProductIds)) {
                foreach ($allowHideProductIds as $productId) {
                    if (! is_numeric($productId)) {
                        throw new \Exception(
                            sprintf(
                                'No valid product id(s) were provided for option with title: %s and value with title: %s',
                                $object->getOption()->getTitle(),
                                $object->getTitle()
                            )
                        );
                    }
                }

                $object->setData(
                    'allow_hide_product_ids',
                    implode(
                        ',',
                        $allowHideProductIds
                    )
                );
            }
        }
    }
}
