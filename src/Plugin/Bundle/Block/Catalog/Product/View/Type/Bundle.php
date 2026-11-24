<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Plugin\Bundle\Block\Catalog\Product\View\Type;

use Infrangible\CatalogProductOptionComposite\Block\Catalog\Product\View\Type\Bundle\Options\Options;
use Magento\Bundle\Model\Option;

/**
 * Shows the custom options of a bundle product that are restricted to products of a bundle option with this bundle
 * option, and restricts the price box of the bundle options to the price box of the bundle product, since the custom
 * options of the bundle options have their own price boxes.
 */
class Bundle
{
    /** The option of the bundle widget configuration that the price box selector is added after. */
    private const CONTROL_CONTAINER = '"controlContainer": ".field.option"';

    /**
     * Adds the custom options of the bundle option.
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject
     * @param string $result
     * @param Option $bundleOption
     *
     * @return string
     */
    public function afterGetOptionHtml(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject,
        string $result,
        Option $bundleOption
    ): string {
        $childBlock = $subject->getChildBlock('product.info.bundle.options.options');

        if (! $childBlock instanceof Options) {
            return $result;
        }

        $childBlock->setBundleOption($bundleOption);

        return $result . $childBlock->toHtml();
    }

    /**
     * Adds the price box of the bundle product to the configuration of the bundle widget.
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject, string $result): string
    {
        $position = strpos($result, self::CONTROL_CONTAINER);

        if ($position === false) {
            return $result;
        }

        $position += strlen(self::CONTROL_CONTAINER);

        return substr($result, 0, $position) . sprintf(
            ',%s                "priceBoxSelector": "[data-role=priceBox][data-price-box=product-id-%d]"',
            PHP_EOL,
            $subject->getProduct()->getId()
        ) . substr($result, $position);
    }
}
