<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Helper;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var array */
    private $itemCompositeProductIds = [];

    public function getCompositeProductIds(AbstractItem $item): array
    {
        $itemId = $item->getId();

        if (! array_key_exists(
            $itemId,
            $this->itemCompositeProductIds
        )) {
            /** @var Item $quoteItem */
            foreach ($item->getQuote()->getAllItems() as $quoteItem) {
                if ($quoteItem->getParentItemId() == $item->getId()) {
                    $product = $item->getProduct();
                    $productTypeId = $product->getTypeId();

                    if ($productTypeId === Configurable::TYPE_CODE) {
                        $this->itemCompositeProductIds[ $itemId ][] = $product->getId();
                    } elseif ($productTypeId === Type::TYPE_CODE) {
                        $selectionIdOption = $quoteItem->getOptionByCode('selection_id');

                        if ($selectionIdOption) {
                            $selectionId = $selectionIdOption->getValue();

                            /** @var Type $productTypeInstance */
                            $productTypeInstance = $product->getTypeInstance();

                            $options = $productTypeInstance->getOptions($product);

                            /** @var Option $option */
                            foreach ($options as $option) {
                                $optionSelections = $productTypeInstance->getSelectionsCollection(
                                    [$option->getId()],
                                    $product
                                );

                                /** @var Product $optionSelection */
                                foreach ($optionSelections as $optionSelection) {
                                    if ($optionSelection->getData('selection_id') == $selectionId) {
                                        $this->itemCompositeProductIds[ $itemId ][] = sprintf(
                                            '%s_%s',
                                            $option->getId(),
                                            $optionSelection->getId()
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->itemCompositeProductIds[ $itemId ] ?? [];
    }
}
