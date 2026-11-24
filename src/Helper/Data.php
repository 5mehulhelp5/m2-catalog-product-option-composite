<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Helper;

use FeWeDev\Base\Variables;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Decides which custom options and option values of a composite product are available for the selected children.
 *
 * Options and option values store the children they are restricted to in "allow_hide_product_ids": product ids of
 * the children of configurable products, "<bundle option id>_<product id>" for the selections of bundle products.
 * Options and values without children are available for all children.
 */
class Data
{
    /** Name of the data with the children that options and option values are restricted to. */
    public const ALLOW_HIDE_PRODUCT_IDS = 'allow_hide_product_ids';

    /** @var array<int, list<array{option: Option, product: Product}>> */
    private array $itemBundleSelections = [];

    /** @var array<int, list<string>> */
    private array $itemCompositeProductIds = [];

    /** @var Item|null */
    private ?Item $addedBundleItem = null;

    /**
     * @param ManagerInterface $eventManager
     * @param Variables $variables
     */
    public function __construct(protected ManagerInterface $eventManager, protected Variables $variables)
    {
    }

    /**
     * Returns the children that the option or option value is restricted to, empty if it is not restricted.
     *
     * @param DataObject $dataObject
     *
     * @return string[]
     */
    public function getAllowHideProductIds(DataObject $dataObject): array
    {
        $allowHideProductIds = $dataObject->getData(self::ALLOW_HIDE_PRODUCT_IDS);

        if (is_string($allowHideProductIds)) {
            $allowHideProductIds = explode(',', $allowHideProductIds);
        }

        if (! is_array($allowHideProductIds)) {
            return [];
        }

        $productIds = [];

        foreach ($allowHideProductIds as $allowHideProductId) {
            $productId = is_scalar($allowHideProductId) ? trim((string)$allowHideProductId) : '';

            if ($productId !== '') {
                $productIds[] = $productId;
            }
        }

        return array_values(array_unique($productIds));
    }

    /**
     * Returns the selected children of the item.
     *
     * These are the product ids of the child of a configurable product, "<bundle option id>_<product id>" of the
     * selections of a bundle product.
     *
     * @param AbstractItem $item
     *
     * @return string[]
     */
    public function getCompositeProductIds(AbstractItem $item): array
    {
        $key = spl_object_id($item);

        if (! array_key_exists($key, $this->itemCompositeProductIds)) {
            $productIds = [];

            $productTypeId = $item->getProduct()->getTypeId();

            if ($productTypeId === Configurable::TYPE_CODE) {
                foreach ($this->getChildItems($item) as $childItem) {
                    $productIds[] = $this->variables->stringValue($childItem->getProduct()->getId());
                }
            } elseif ($productTypeId === Type::TYPE_CODE) {
                foreach ($this->getItemBundleSelections($item) as $itemBundleSelection) {
                    $productIds[] = sprintf(
                        '%s_%s',
                        $this->variables->stringValue($itemBundleSelection[ 'option' ]->getId()),
                        $this->variables->stringValue($itemBundleSelection[ 'product' ]->getId())
                    );
                }
            }

            $this->itemCompositeProductIds[ $key ] = $productIds;
        }

        return $this->itemCompositeProductIds[ $key ];
    }

    /**
     * Returns the bundle options and selected products of the children of a bundle item.
     *
     * The products have the values of the item's buy request as preconfigured values.
     *
     * @param AbstractItem $item
     *
     * @return array<int, array{option: Option, product: Product}>
     */
    public function getItemBundleSelections(AbstractItem $item): array
    {
        $key = spl_object_id($item);

        if (! array_key_exists($key, $this->itemBundleSelections)) {
            $selectionLists = [];

            $product = $item->getProduct();
            $productTypeInstance = $product->getTypeInstance();

            if ($productTypeInstance instanceof Type) {
                foreach ($this->getChildItems($item) as $childItem) {
                    $selectionIdOption = $childItem->getOptionsByCode()[ 'selection_id' ] ?? null;

                    if ($selectionIdOption !== null) {
                        $selectionLists[] = $this->getSelections(
                            $item,
                            $productTypeInstance,
                            $this->variables->stringValue($selectionIdOption->getValue())
                        );
                    }
                }
            }

            $this->itemBundleSelections[ $key ] = array_merge([], ...$selectionLists);
        }

        return $this->itemBundleSelections[ $key ];
    }

    /**
     * Returns the bundle options and products of the selection of the bundle item.
     *
     * @param AbstractItem $item
     * @param Type $productTypeInstance
     * @param string $selectionId
     *
     * @return list<array{option: Option, product: Product}>
     */
    private function getSelections(AbstractItem $item, Type $productTypeInstance, string $selectionId): array
    {
        $product = $item->getProduct();
        $selections = [];

        /** @var Option $option */
        foreach ($productTypeInstance->getOptions($product) as $option) {
            /** @var Product $optionSelection */
            foreach ($productTypeInstance->getSelectionsCollection([$option->getId()], $product) as $optionSelection) {
                if ($this->variables->stringValue($optionSelection->getData('selection_id')) !== $selectionId) {
                    continue;
                }

                if ($item instanceof Item) {
                    $optionSelection->setData(
                        'preconfigured_values',
                        $optionSelection->processBuyRequest($item->getBuyRequest())
                    );
                }

                $selections[] = ['option' => $option, 'product' => $optionSelection];
            }
        }

        return $selections;
    }

    /**
     * Returns whether the option is available for the selected children of the item.
     *
     * @param Product\Option $option
     * @param AbstractItem $item
     *
     * @return bool
     */
    public function isProductOptionAvailableForItem(Product\Option $option, AbstractItem $item): bool
    {
        return $this->isAvailableForItem($option, $item);
    }

    /**
     * Returns whether the option value is available for the selected children of the item.
     *
     * @param Product\Option\Value $optionValue
     * @param AbstractItem $item
     *
     * @return bool
     */
    public function isProductOptionValueAvailableForItem(Product\Option\Value $optionValue, AbstractItem $item): bool
    {
        return $this->isAvailableForItem($optionValue, $item);
    }

    /**
     * Returns whether the option is available for the product of the bundle option of the item.
     *
     * Observers of the event "catalog_product_option_composite_item_option_available" can change the result.
     *
     * @param Product\Option $productOption
     * @param AbstractItem $item
     * @param Option $bundleOption
     * @param Product $product
     *
     * @return bool
     */
    public function isProductOptionAvailableForItemBundleOptionSelection(
        Product\Option $productOption,
        AbstractItem $item,
        Option $bundleOption,
        Product $product
    ): bool {
        return $this->dispatchAvailable(
            'catalog_product_option_composite_item_option_available',
            $this->isAvailableForBundleOptionSelection($productOption, $bundleOption, $product),
            [
                'product_option' => $productOption,
                'item'           => $item,
                'bundle_option'  => $bundleOption,
                'product'        => $product
            ]
        );
    }

    /**
     * Returns whether the option value is available for the product of the bundle option of the item.
     *
     * Observers of the event "catalog_product_option_composite_item_option_value_available" can change the result.
     *
     * @param Product\Option\Value $productOptionValue
     * @param AbstractItem $item
     * @param Option $bundleOption
     * @param Product $product
     *
     * @return bool
     */
    public function isProductOptionValueAvailableForItemBundleOptionSelection(
        Product\Option\Value $productOptionValue,
        AbstractItem $item,
        Option $bundleOption,
        Product $product
    ): bool {
        return $this->dispatchAvailable(
            'catalog_product_option_composite_item_option_value_available',
            $this->isAvailableForBundleOptionSelection($productOptionValue, $bundleOption, $product),
            [
                'product_option_value' => $productOptionValue,
                'item'                 => $item,
                'bundle_option'        => $bundleOption,
                'product'              => $product
            ]
        );
    }

    /**
     * Returns whether the option is available for the product of the bundle option.
     *
     * Observers of the event "catalog_product_option_composite_option_available" can change the result.
     *
     * @param Product\Option $productOption
     * @param Option $bundleOption
     * @param Product $product
     *
     * @return bool
     */
    public function isProductOptionAvailableForBundleOptionSelection(
        Product\Option $productOption,
        Option $bundleOption,
        Product $product
    ): bool {
        return $this->dispatchAvailable(
            'catalog_product_option_composite_option_available',
            $this->isAvailableForBundleOptionSelection($productOption, $bundleOption, $product),
            [
                'product_option' => $productOption,
                'bundle_option'  => $bundleOption,
                'product'        => $product
            ]
        );
    }

    /**
     * Returns whether the option value is available for the product of the bundle option.
     *
     * Observers of the event "catalog_product_option_composite_option_value_available" can change the result.
     *
     * @param Product\Option\Value $productOptionValue
     * @param Option $bundleOption
     * @param Product $product
     *
     * @return bool
     */
    public function isProductOptionValueAvailableForBundleOptionSelection(
        Product\Option\Value $productOptionValue,
        Option $bundleOption,
        Product $product
    ): bool {
        return $this->dispatchAvailable(
            'catalog_product_option_composite_option_value_available',
            $this->isAvailableForBundleOptionSelection($productOptionValue, $bundleOption, $product),
            [
                'product_option_value' => $productOptionValue,
                'bundle_option'        => $bundleOption,
                'product'              => $product
            ]
        );
    }

    /**
     * Returns whether the option is restricted to a product of the bundle option.
     *
     * @param Product\Option $option
     * @param Option $bundleOption
     *
     * @return bool
     */
    public function isProductOptionValueAvailableForBundleOption(Product\Option $option, Option $bundleOption): bool
    {
        $bundleOptionId = $bundleOption->getId();

        return is_scalar($bundleOptionId) &&
            $this->isProductOptionValueAvailableForBundleOptionId($option, $this->variables->intValue($bundleOptionId));
    }

    /**
     * Returns whether the option is restricted to a product of the bundle option with the id.
     *
     * @param Product\Option $option
     * @param int $bundleOptionId
     *
     * @return bool
     */
    public function isProductOptionValueAvailableForBundleOptionId(Product\Option $option, int $bundleOptionId): bool
    {
        $prefix = sprintf('%d_', $bundleOptionId);

        foreach ($this->getAllowHideProductIds($option) as $allowHideProductId) {
            if (str_starts_with($allowHideProductId, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remembers the bundle item that was added to the cart in this request.
     *
     * @param Item $item
     *
     * @return void
     */
    public function setAddedBundleItem(Item $item): void
    {
        $this->addedBundleItem = $item;
    }

    /**
     * Returns the bundle item that was added to the cart last in this request.
     *
     * @return Item|null
     */
    public function getAddedBundleItem(): ?Item
    {
        return $this->addedBundleItem;
    }

    /**
     * Returns the child items of the item.
     *
     * @param AbstractItem $item
     *
     * @return Item[]
     */
    private function getChildItems(AbstractItem $item): array
    {
        $itemId = $item->getId();

        if ($itemId === null) {
            return array_values(array_filter($item->getChildren(), fn($child) => $child instanceof Item));
        }

        $childItems = [];

        foreach ($item->getQuote()->getAllItems() as $quoteItem) {
            if ($quoteItem instanceof Item && $quoteItem->getParentItemId() == $itemId) {
                $childItems[] = $quoteItem;
            }
        }

        return $childItems;
    }

    /**
     * Returns whether the option or option value is restricted to a selected child of the item.
     *
     * @param DataObject $dataObject
     * @param AbstractItem $item
     *
     * @return bool
     */
    private function isAvailableForItem(DataObject $dataObject, AbstractItem $item): bool
    {
        $allowHideProductIds = $this->getAllowHideProductIds($dataObject);

        return count($allowHideProductIds) === 0 ||
            count(array_intersect($this->getCompositeProductIds($item), $allowHideProductIds)) > 0;
    }

    /**
     * Returns whether the option or option value is restricted to the product of the bundle option.
     *
     * @param DataObject $dataObject
     * @param Option $bundleOption
     * @param Product $product
     *
     * @return bool
     */
    private function isAvailableForBundleOptionSelection(
        DataObject $dataObject,
        Option $bundleOption,
        Product $product
    ): bool {
        $allowHideProductIds = $this->getAllowHideProductIds($dataObject);

        return count($allowHideProductIds) === 0 || in_array(
            sprintf(
                '%s_%s',
                $this->variables->stringValue($bundleOption->getId()),
                $this->variables->stringValue($product->getId())
            ),
            $allowHideProductIds,
            true
        );
    }

    /**
     * Dispatches the event with the result, the observers can change "is_available" of the result.
     *
     * @param string $eventName
     * @param bool $isAvailable
     * @param array $eventData
     * @phpstan-param array<string, mixed> $eventData
     *
     * @return bool
     */
    private function dispatchAvailable(string $eventName, bool $isAvailable, array $eventData): bool
    {
        $checkResult = new DataObject(['is_available' => $isAvailable]);

        $eventData[ 'result' ] = $checkResult;

        $this->eventManager->dispatch($eventName, $eventData);

        return (bool)$checkResult->getData('is_available');
    }
}
