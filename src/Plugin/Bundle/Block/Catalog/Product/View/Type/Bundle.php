<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Plugin\Bundle\Block\Catalog\Product\View\Type;

use Infrangible\CatalogProductOptionComposite\Block\Catalog\Product\View\Type\Bundle\Options\Options;
use Magento\Bundle\Model\Option;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Bundle
{
    public function afterGetOptionHtml(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject,
        string $result,
        Option $bundleOption
    ): string {
        /** @var Options $childBlock */
        $childBlock = $subject->getChildBlock('product.info.bundle.options.options');

        if ($childBlock) {
            $childBlock->setBundleOption($bundleOption);

            return $result . $childBlock->toHtml();
        } else {
            return $result;
        }
    }

    public function afterToHtml(\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject, string $result): string
    {
        $product = $subject->getProduct();

        $selector = '"controlContainer": ".field.option"';

        $position = strpos(
            $result,
            $selector
        );

        if ($position !== false) {
            $position += strlen($selector) - 1;

            $before = substr(
                $result,
                0,
                $position
            );
            $after = substr(
                $result,
                $position
            );

            $result = sprintf(
                '%s",%s                %s%s',
                $before,
                PHP_EOL,
                sprintf(
                    "\"priceBoxSelector\": \"[data-role=priceBox][data-price-box=product-id-%s]",
                    $product->getId()
                ),
                $after
            );
        }

        return $result;
    }
}
