<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Traits;

use Magento\Bundle\Model\Option;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
trait ItemBundleSelectionOption
{
    /** @var AbstractItem */
    private $item;

    /** @var Option */
    private $bundleOption;

    public function getItem(): AbstractItem
    {
        return $this->item;
    }

    public function setItem(AbstractItem $item): void
    {
        $this->item = $item;
    }

    public function getBundleOption(): Option
    {
        return $this->bundleOption;
    }

    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }
}
