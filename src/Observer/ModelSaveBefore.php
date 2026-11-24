<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Observer;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Product as ProductHelper;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validates the children of an option or option value before it is saved and stores them separated by comma:
 * "<bundle option id>_<product id>" for bundle products, product ids for other products.
 */
class ModelSaveBefore implements ObserverInterface
{
    /**
     * @param Data $helper
     * @param ProductHelper $productHelper
     * @param Variables $variables
     */
    public function __construct(
        protected Data $helper,
        protected ProductHelper $productHelper,
        protected Variables $variables
    ) {
    }

    /**
     * Validates and converts the children of the saved option or option value.
     *
     * @param Observer $observer
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $object = $observer->getData('object');

        if (! $object instanceof Option && ! $object instanceof Value) {
            return;
        }

        $allowHideProductIds = $this->helper->getAllowHideProductIds($object);

        if (count($allowHideProductIds) === 0) {
            $object->setData(Data::ALLOW_HIDE_PRODUCT_IDS);

            return;
        }

        $option = $object instanceof Option ? $object : $object->getOption();
        $pattern = $this->isBundle($option) ? '/^\d+_\d+$/' : '/^\d+$/';

        foreach ($allowHideProductIds as $allowHideProductId) {
            if (preg_match($pattern, $allowHideProductId) !== 1) {
                throw new LocalizedException(
                    $object instanceof Value ?
                        __(
                            'No valid product id(s) were provided for option with title: %1 and value with title: %2',
                            $option->getTitle(),
                            $object->getTitle()
                        ) :
                        __('No valid product id(s) were provided for option with title: %1', $object->getTitle())
                );
            }
        }

        $object->setData(Data::ALLOW_HIDE_PRODUCT_IDS, implode(',', $allowHideProductIds));
    }

    /**
     * Returns whether the option belongs to a bundle product.
     *
     * @param Option|null $option
     *
     * @return bool
     */
    private function isBundle(?Option $option): bool
    {
        if ($option === null) {
            return false;
        }

        $product = $option->getProduct();

        if (! $product instanceof Product) {
            $productId = $option->getData('product_id');

            if (! is_numeric($productId)) {
                return false;
            }

            $product = $this->productHelper->loadProduct($this->variables->intValue($productId));
        }

        return $product->getTypeId() === Type::TYPE_CODE;
    }
}
