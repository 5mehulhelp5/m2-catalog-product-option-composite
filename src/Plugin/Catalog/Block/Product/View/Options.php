<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Plugin\Catalog\Block\Product\View;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product\Option;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Options
{
    /**
     * @param Option[] $options
     */
    public function afterGetOptions(\Magento\Catalog\Block\Product\View\Options $subject, array $options): array
    {
        $product = $subject->getProduct();

        if ($product->getTypeId() !== Type::TYPE_CODE) {
            return $options;
        }

        foreach ($options as $key => $option) {
            $allowHideProductIds = $option->getData('allow_hide_product_ids');

            if ($allowHideProductIds) {
                unset($options[ $key ]);
            }
        }

        return $options;
    }
}
