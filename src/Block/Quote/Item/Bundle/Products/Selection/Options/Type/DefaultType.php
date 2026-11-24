<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;

use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;
use LogicException;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

/**
 * Renders a custom option of a bundle item without own renderer.
 */
class DefaultType extends Template implements ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;

    /** @var Product|null */
    private ?Product $product = null;

    /** @var Product\Option|null */
    private ?Product\Option $option = null;

    /**
     * Returns the product of the bundle option.
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        if ($this->product === null) {
            throw new LogicException('Product is not defined');
        }

        return $this->product;
    }

    /**
     * Sets the product of the bundle option.
     *
     * @param Product|null $product
     *
     * @return $this
     */
    public function setProduct(?Product $product = null): static
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Returns the custom option.
     *
     * @return Product\Option
     */
    public function getOption(): Product\Option
    {
        if ($this->option === null) {
            throw new LogicException('Option is not defined');
        }

        return $this->option;
    }

    /**
     * Sets the custom option.
     *
     * @param Product\Option $option
     *
     * @return $this
     */
    public function setOption(Product\Option $option): static
    {
        $this->option = $option;

        return $this;
    }
}
