<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;

use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class DefaultType extends Template implements ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;

    /** @var Product|null */
    private $product;

    /** @var Product\Option|null */
    private $option;

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product = null): void
    {
        $this->product = $product;
    }

    public function getOption(): ?Product\Option
    {
        return $this->option;
    }

    public function setOption(?Product\Option $option): void
    {
        $this->option = $option;
    }
}
