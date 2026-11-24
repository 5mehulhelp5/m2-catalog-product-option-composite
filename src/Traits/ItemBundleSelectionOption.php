<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Traits;

use LogicException;
use Magento\Bundle\Model\Option;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * The bundle item and bundle option of a renderer of a custom option of a bundle item.
 */
trait ItemBundleSelectionOption
{
    /** @var AbstractItem|null */
    private ?AbstractItem $item = null;

    /** @var Option|null */
    private ?Option $bundleOption = null;

    /**
     * Returns the bundle item.
     *
     * @return AbstractItem
     */
    public function getItem(): AbstractItem
    {
        if ($this->item === null) {
            throw new LogicException('Item is not defined');
        }

        return $this->item;
    }

    /**
     * Sets the bundle item.
     *
     * @param AbstractItem $item
     *
     * @return void
     */
    public function setItem(AbstractItem $item): void
    {
        $this->item = $item;
    }

    /**
     * Returns the bundle option.
     *
     * @return Option
     */
    public function getBundleOption(): Option
    {
        if ($this->bundleOption === null) {
            throw new LogicException('Bundle option is not defined');
        }

        return $this->bundleOption;
    }

    /**
     * Sets the bundle option.
     *
     * @param Option $bundleOption
     *
     * @return void
     */
    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }
}
