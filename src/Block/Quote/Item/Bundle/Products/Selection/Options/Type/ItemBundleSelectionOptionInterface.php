<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;

use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * A renderer of a custom option of a bundle item for a product of a bundle option.
 */
interface ItemBundleSelectionOptionInterface
{
    /**
     * Returns the bundle item.
     *
     * @return AbstractItem
     */
    public function getItem(): AbstractItem;

    /**
     * Sets the bundle item.
     *
     * @param AbstractItem $item
     *
     * @return void
     */
    public function setItem(AbstractItem $item): void;

    /**
     * Returns the bundle option.
     *
     * @return Option
     */
    public function getBundleOption(): Option;

    /**
     * Sets the bundle option.
     *
     * @param Option $bundleOption
     *
     * @return void
     */
    public function setBundleOption(Option $bundleOption): void;

    /**
     * Returns the product of the bundle option.
     *
     * @return Product
     */
    public function getProduct(): Product;

    /**
     * Sets the product of the bundle option.
     *
     * @param Product|null $product
     *
     * @return $this
     */
    public function setProduct(?Product $product = null): static;

    /**
     * Returns the custom option.
     *
     * @return Product\Option
     */
    public function getOption(): Product\Option;

    /**
     * Sets the custom option.
     *
     * @param Product\Option $option
     *
     * @return $this
     */
    public function setOption(Product\Option $option): static;
}
