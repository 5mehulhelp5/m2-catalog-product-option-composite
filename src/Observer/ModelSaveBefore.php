<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\Core\Helper\Product;
use Magento\Bundle\Model\Product\Type;
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
    /** @var Product */
    protected $productHelper;

    public function __construct(Product $productHelper)
    {
        $this->productHelper = $productHelper;
    }

    /**
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        $object = $observer->getData('object');

        if ($object instanceof Value) {
            $allowHideProductIds = $object->getData('allow_hide_product_ids');

            if (is_array($allowHideProductIds)) {
                foreach ($allowHideProductIds as $allowHideProductId) {
                    $isValid = true;

                    $product = $object->getOption()->getProduct();

                    if (! $product) {
                        $productId = $object->getOption()->getData('product_id');

                        if ($productId) {
                            $product = $this->productHelper->loadProduct(intval($productId));
                        }
                    }

                    if ($product && $product->getTypeId() === Type::TYPE_CODE) {
                        if (! preg_match(
                            '/[0-9]+_[0-9]+/',
                            $allowHideProductId
                        )) {
                            $isValid = false;
                        }
                    } elseif (! is_numeric($allowHideProductId)) {
                        $isValid = false;
                    }

                    if (! $isValid) {
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
            } else {
                $object->setData('allow_hide_product_ids');
            }
        }
    }
}
