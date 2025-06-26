<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use FeWeDev\Base\Variables;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductOptionOfferCompositeItemProductOptionAvailable implements ObserverInterface
{
    /** @var Variables */
    protected $variables;

    /** @var Json */
    protected $serializer;

    public function __construct(Variables $variables, Json $serializer)
    {
        $this->variables = $variables;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer): void
    {
        /** @var Product\Option $productOption */
        $productOption = $observer->getData('product_option');

        $allowHideProductIds = $productOption->getData('allow_hide_product_ids');

        if ($this->variables->isEmpty($allowHideProductIds)) {
            return;
        }

        /** @var Item $item */
        $item = $observer->getData('item');

        /** @var Product $product */
        $product = $observer->getData('product');

        /** @var DataObject $checkResult */
        $checkResult = $observer->getData('result');

        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
            $isAvailable = false;

            $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');

            $bundleOptionsIds =
                $optionsQuoteItemOption ? $this->serializer->unserialize($optionsQuoteItemOption->getValue()) : [];

            if ($bundleOptionsIds) {
                /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
                $typeInstance = $product->getTypeInstance();

                $optionsCollection = $typeInstance->getOptionsByIds(
                    $bundleOptionsIds,
                    $product
                );

                $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

                $bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());

                if (! empty($bundleSelectionIds)) {
                    $selectionsCollection = $typeInstance->getSelectionsByIds(
                        $bundleSelectionIds,
                        $product
                    );

                    $bundleOptions = $optionsCollection->appendSelections(
                        $selectionsCollection,
                        true
                    );

                    /** @var Option $bundleOption */
                    foreach ($bundleOptions as $bundleOption) {
                        if ($bundleOption->getSelections()) {
                            $bundleSelections = $bundleOption->getSelections();

                            foreach ($bundleSelections as $bundleSelection) {
                                $selectionQty = $product->getCustomOption(
                                    sprintf(
                                        'selection_qty_%s',
                                        $bundleSelection->getSelectionId()
                                    )
                                );

                                $selectionQty = ($selectionQty ? $selectionQty->getValue() : 0) * 1;

                                if ($selectionQty) {
                                    foreach ($allowHideProductIds as $allowHideProductId) {
                                        if ($allowHideProductId == sprintf(
                                                '%s_%s',
                                                $bundleOption->getId(),
                                                $bundleSelection->getId()
                                            )) {

                                            $isAvailable = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $checkResult->setData(
                'is_available',
                $isAvailable
            );
        }
    }
}
