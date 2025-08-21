<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Helper;

use FeWeDev\Base\Variables;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EventManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var EventManager */
    protected $eventManager;

    /** @var Variables */
    protected $variables;

    /** @var array */
    private $itemBundleSelections = [];

    /** @var array */
    private $itemCompositeProductIds = [];

    public function __construct(EventManager $eventManager, Variables $variables)
    {
        $this->eventManager = $eventManager;
        $this->variables = $variables;
    }

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
                        $itemBundleSelections = $this->getItemBundleSelections($item);

                        foreach ($itemBundleSelections as $itemBundleSelection) {
                            /** @var Option $option */
                            $option = $itemBundleSelection[ 'option' ];
                            /** @var Product $product */
                            $product = $itemBundleSelection[ 'product' ];

                            $this->itemCompositeProductIds[ $itemId ][] = sprintf(
                                '%s_%s',
                                $option->getId(),
                                $product->getId()
                            );
                        }
                    }
                }
            }
        }

        return $this->itemCompositeProductIds[ $itemId ] ?? [];
    }

    public function getItemBundleSelections(AbstractItem $item): array
    {
        $itemId = $item->getId();

        if (! array_key_exists(
            $itemId,
            $this->itemBundleSelections
        )) {
            /** @var Item $quoteItem */
            foreach ($item->getQuote()->getAllItems() as $quoteItem) {
                if ($quoteItem->getParentItemId() == $item->getId()) {
                    $product = $item->getProduct();

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
                                    if ($item instanceof Item) {
                                        $optionValues = $optionSelection->processBuyRequest($item->getBuyRequest());

                                        $optionSelection->setData(
                                            'preconfigured_values',
                                            $optionValues
                                        );
                                    }

                                    $this->itemBundleSelections[ $itemId ][] =
                                        ['option' => $option, 'product' => $optionSelection];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->itemBundleSelections[ $itemId ] ?? [];
    }

    public function isProductOptionAvailableForItem(Product\Option $option, AbstractItem $item): bool
    {
        return $this->isAvailableForItem(
            $option,
            $item
        );
    }

    public function isProductOptionValueAvailableForItem(Product\Option\Value $optionValue, AbstractItem $item): bool
    {
        return $this->isAvailableForItem(
            $optionValue,
            $item
        );
    }

    private function isAvailableForItem(DataObject $dataObject, AbstractItem $item): bool
    {
        $allowHideProductIds = $dataObject->getData('allow_hide_product_ids');

        if ($allowHideProductIds) {
            foreach ($this->getCompositeProductIds($item) as $itemProductId) {
                if (in_array(
                    $itemProductId,
                    $allowHideProductIds
                )) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function isProductOptionAvailableForItemBundleOptionSelection(
        Product\Option $productOption,
        AbstractItem $item,
        Option $bundleOption,
        Product $product
    ): bool {
        $isAvailable = $this->isAvailableForBundleOptionSelection(
            $productOption,
            $bundleOption,
            $product
        );

        $checkResult = new DataObject();
        $checkResult->setData(
            'is_available',
            $isAvailable
        );

        $eventData = [
            'product_option' => $productOption,
            'item'           => $item,
            'bundle_option'  => $bundleOption,
            'product'        => $product,
            'result'         => $checkResult
        ];

        $this->eventManager->dispatch(
            'catalog_product_option_composite_item_option_available',
            $eventData
        );

        return $checkResult->getData('is_available');
    }

    public function isProductOptionValueAvailableForItemBundleOptionSelection(
        ProductCustomOptionValuesInterface $productOptionValue,
        AbstractItem $item,
        Option $bundleOption,
        Product $product
    ): bool {
        $isAvailable = $this->isAvailableForBundleOptionSelection(
            $productOptionValue,
            $bundleOption,
            $product
        );

        $checkResult = new DataObject();
        $checkResult->setData(
            'is_available',
            $isAvailable
        );

        $eventData = [
            'product_option_value' => $productOptionValue,
            'item'                 => $item,
            'bundle_option'        => $bundleOption,
            'product'              => $product,
            'result'               => $checkResult
        ];

        $this->eventManager->dispatch(
            'catalog_product_option_composite_item_option_value_available',
            $eventData
        );

        return $checkResult->getData('is_available');
    }

    public function isProductOptionAvailableForBundleOptionSelection(
        Product\Option $productOption,
        Option $bundleOption,
        Product $product
    ): bool {
        $isAvailable = $this->isAvailableForBundleOptionSelection(
            $productOption,
            $bundleOption,
            $product
        );

        $checkResult = new DataObject();
        $checkResult->setData(
            'is_available',
            $isAvailable
        );

        $eventData = [
            'product_option' => $productOption,
            'bundle_option'  => $bundleOption,
            'product'        => $product,
            'result'         => $checkResult
        ];

        $this->eventManager->dispatch(
            'catalog_product_option_composite_option_available',
            $eventData
        );

        return $checkResult->getData('is_available');
    }

    public function isProductOptionValueAvailableForBundleOptionSelection(
        ProductCustomOptionValuesInterface $productOptionValue,
        Option $bundleOption,
        Product $product
    ): bool {
        $isAvailable = $this->isAvailableForBundleOptionSelection(
            $productOptionValue,
            $bundleOption,
            $product
        );

        $checkResult = new DataObject();
        $checkResult->setData(
            'is_available',
            $isAvailable
        );

        $eventData = [
            'product_option_value' => $productOptionValue,
            'bundle_option'        => $bundleOption,
            'product'              => $product,
            'result'               => $checkResult
        ];

        $this->eventManager->dispatch(
            'catalog_product_option_composite_option_value_available',
            $eventData
        );

        return $checkResult->getData('is_available');
    }

    private function isAvailableForBundleOptionSelection(
        DataObject $dataObject,
        Option $bundleOption,
        Product $product
    ): bool {
        $allowHideProductIds = $dataObject->getData('allow_hide_product_ids');

        if ($allowHideProductIds) {
            $bundleSelectionId = sprintf(
                '%s_%s',
                $bundleOption->getId(),
                $product->getId()
            );

            if (in_array(
                $bundleSelectionId,
                $allowHideProductIds
            )) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function isProductOptionValueAvailableForBundleOption(Product\Option $option, Option $bundleOption): bool
    {
        try {
            return $this->isProductOptionValueAvailableForBundleOptionId(
                $option,
                $this->variables->intValue($bundleOption->getId())
            );
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function isProductOptionValueAvailableForBundleOptionId(Product\Option $option, int $bundleOptionId): bool
    {
        $allowHideProductIds = $option->getData('allow_hide_product_ids');

        if ($allowHideProductIds) {
            foreach ($allowHideProductIds as $allowHideProductId) {
                if (str_starts_with(
                    $allowHideProductId,
                    sprintf(
                        '%s_',
                        $bundleOptionId
                    )
                )) {
                    return true;
                }
            }
        }

        return false;
    }
}
