<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Helper;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite\Bundle;
use Infrangible\CatalogProductOptionWrapper\Helper\Data as WrapperHelper;
use Infrangible\Core\Helper\Product as ProductHelper;
use Infrangible\Core\Helper\Stores;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\AbstractBlock;
use Psr\Log\LoggerInterface;

/**
 * Renders the custom options of a bundle product that are restricted to products of a bundle option with this bundle
 * option on the product page.
 */
class Output
{
    /** @var Product[] */
    private array $products = [];

    /**
     * @param Data $helper
     * @param WrapperHelper $wrapperHelper
     * @param ProductHelper $productHelper
     * @param Stores $storeHelper
     * @param Variables $variables
     * @param LoggerInterface $logging
     */
    public function __construct(
        protected Data $helper,
        protected WrapperHelper $wrapperHelper,
        protected ProductHelper $productHelper,
        protected Stores $storeHelper,
        protected Variables $variables,
        protected LoggerInterface $logging
    ) {
    }

    /**
     * Renders the custom options of the bundle product that are restricted to products of the bundle option.
     *
     * It uses the child blocks of the block: the renderers of the option types ("option.<group>"), the wrapper and
     * the script ("product_options_composite").
     *
     * @param AbstractBlock $block
     * @param Option $bundleOption
     *
     * @return string
     */
    public function renderBundleOptionsHtml(AbstractBlock $block, Option $bundleOption): string
    {
        $product = $this->getBundleProduct($bundleOption);

        if ($product === null) {
            return '';
        }

        $optionsHtml = '';

        foreach ($product->getOptions() ?? [] as $option) {
            if ($option instanceof Product\Option &&
                $this->helper->isProductOptionValueAvailableForBundleOption($option, $bundleOption)) {

                $optionsHtml .= $this->wrapperHelper->renderWrapper(
                    $block,
                    $option,
                    $this->getOptionHtml($block, $product, $option)
                );
            }
        }

        $compositeBlock = $block->getChildBlock('product_options_composite');

        if ($compositeBlock instanceof Bundle) {
            $compositeBlock->setProduct($product);
            $compositeBlock->setBundleOption($bundleOption);

            $optionsHtml .= $compositeBlock->toHtml();
        }

        return $optionsHtml;
    }

    /**
     * Renders the option with the child block of its type.
     *
     * The child block is "option.<group>", or "option.default" for options without group.
     *
     * @param AbstractBlock $block
     * @param Product $product
     * @param Product\Option $option
     *
     * @return string
     */
    public function getOptionHtml(AbstractBlock $block, Product $product, Product\Option $option): string
    {
        $group = $option->getGroupByType($option->getType());

        $childAlias = sprintf('option.%s', $group === '' ? 'default' : $group);

        $renderer = $block->getChildBlock($childAlias);

        if (! $renderer instanceof AbstractOptions) {
            return '';
        }

        $renderer->setProduct($product);
        $renderer->setOption($option);

        return $block->getChildHtml($childAlias, false);
    }

    /**
     * Renders the price box of the custom options of the bundle option, if it has any.
     *
     * It uses the child block "product_options_price".
     *
     * @param AbstractBlock $block
     * @param Option $bundleOption
     *
     * @return string
     */
    public function renderBundleOptionsPriceHtml(AbstractBlock $block, Option $bundleOption): string
    {
        $product = $this->getBundleProduct($bundleOption);
        $priceBlock = $block->getChildBlock('product_options_price');

        if ($product === null || ! $priceBlock instanceof Bundle) {
            return '';
        }

        foreach ($product->getOptions() ?? [] as $option) {
            if ($option instanceof Product\Option &&
                $this->helper->isProductOptionValueAvailableForBundleOption($option, $bundleOption)) {

                $priceBlock->setProduct($product);
                $priceBlock->setBundleOption($bundleOption);

                return $priceBlock->toHtml();
            }
        }

        return '';
    }

    /**
     * Returns the bundle product of the bundle option in the current store, null if it cannot be loaded.
     *
     * @param Option $bundleOption
     *
     * @return Product|null
     */
    private function getBundleProduct(Option $bundleOption): ?Product
    {
        $productId = $bundleOption->getParentId();

        if (! is_numeric($productId)) {
            return null;
        }

        $productId = $this->variables->intValue($productId);

        if (! array_key_exists($productId, $this->products)) {
            try {
                $this->products[ $productId ] = $this->productHelper->loadProduct(
                    $productId,
                    $this->variables->intValue($this->storeHelper->getStore()->getId())
                );
            } catch (Exception $exception) {
                $this->logging->error($exception);

                return null;
            }
        }

        return $this->products[ $productId ];
    }
}
