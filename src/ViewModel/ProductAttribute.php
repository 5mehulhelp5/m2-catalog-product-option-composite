<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\ViewModel;

use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

/**
 * Renders values of product attributes like the product page, with HTML if the attribute allows it on the frontend.
 */
class ProductAttribute implements ArgumentInterface
{
    /**
     * @param Output $outputHelper
     * @param Escaper $escaper
     * @param LoggerInterface $logging
     */
    public function __construct(
        protected Output $outputHelper,
        protected Escaper $escaper,
        protected LoggerInterface $logging
    ) {
    }

    /**
     * Renders the value of the attribute of the product, escaped if it cannot be rendered.
     *
     * @param Product $product
     * @param string $value
     * @param string $attributeCode
     *
     * @return string
     */
    public function render(Product $product, string $value, string $attributeCode): string
    {
        try {
            return $this->outputHelper->productAttribute($product, $value, $attributeCode);
        } catch (LocalizedException $exception) {
            $this->logging->error($exception);

            return $this->escaper->escapeHtml($value);
        }
    }
}
