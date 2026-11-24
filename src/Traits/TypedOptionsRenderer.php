<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Traits;

use Magento\Catalog\Model\Product;

/**
 * Typed product and option methods of renderers that extend Magento's AbstractOptions, for the interface of the
 * renderers of custom options of bundle items.
 */
trait TypedOptionsRenderer
{
    /**
     * Returns the product of the bundle option.
     *
     * @return Product
     */
    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod -- adds the return type of the interface
    public function getProduct(): Product
    {
        return parent::getProduct();
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
        parent::setProduct($product);

        return $this;
    }

    /**
     * Returns the custom option.
     *
     * @return Product\Option
     */
    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod -- adds the return type of the interface
    public function getOption(): Product\Option
    {
        return parent::getOption();
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
        parent::setOption($option);

        return $this;
    }
}
