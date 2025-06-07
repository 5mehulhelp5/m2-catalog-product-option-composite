<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;

use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
interface ItemBundleSelectionOptionInterface
{
    public function getItem(): AbstractItem;

    public function setItem(AbstractItem $item): void;

    public function getBundleOption(): Option;

    public function setBundleOption(Option $bundleOption): void;

    public function getProduct();

    public function setProduct(Product $product = null);

    public function getOption();

    public function setOption(Product\Option $option);
}
