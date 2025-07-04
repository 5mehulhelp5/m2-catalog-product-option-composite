<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Helper;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite\Bundle;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Output extends AbstractHelper
{
    /** @var Data */
    protected $helper;

    /** @var \Infrangible\CatalogProductOptionWrapper\Helper\Data */
    protected $wrapperHelper;

    /** @var \Infrangible\Core\Helper\Product */
    protected $productHelper;

    /** @var Variables */
    protected $variables;

    /** @var Product[] */
    private $products = [];

    public function __construct(
        Context $context,
        Data $helper,
        \Infrangible\CatalogProductOptionWrapper\Helper\Data $wrapperHelper,
        \Infrangible\Core\Helper\Product $productHelper,
        Variables $variables
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->wrapperHelper = $wrapperHelper;
        $this->productHelper = $productHelper;
        $this->variables = $variables;
    }

    public function renderBundleOptionsHtml(AbstractBlock $block, Option $bundleOption): string
    {
        $optionsHtml = '';

        try {
            $productId = $bundleOption->getParentId();

            if (array_key_exists(
                $productId,
                $this->products
            )) {
                $product = $this->products[ $productId ];
            } else {
                $product = $this->productHelper->loadProduct($this->variables->intValue($productId));

                $this->products[ $productId ] = $product;
            }

            /** @var Product\Option $option */
            foreach ($product->getOptions() as $option) {
                if ($this->helper->isProductOptionValueAvailableForBundleOption(
                    $option,
                    $bundleOption
                )) {
                    $optionsHtml .= $this->wrapperHelper->renderWrapper(
                        $block,
                        $option,
                        $this->getOptionHtml(
                            $block,
                            $product,
                            $option
                        )
                    );
                }
            }

            /** @var Bundle $compositeBlock */
            $compositeBlock = $block->getChildBlock('product_options_composite');

            if ($compositeBlock) {
                $compositeBlock->setBundleOption($bundleOption);

                $optionsHtml .= $compositeBlock->toHtml();
            }
        } catch (\Exception $exception) {
        }

        return $optionsHtml;
    }

    public function getOptionHtml(AbstractBlock $block, Product $product, Product\Option $option): string
    {
        $group = $option->getGroupByType($option->getType());

        $childAlias = sprintf(
            'option.%s',
            $group == '' ? 'default' : $group
        );

        /** @var AbstractOptions $renderer */
        $renderer = $block->getChildBlock($childAlias);

        $renderer->setProduct($product);
        $renderer->setOption($option);

        return $block->getChildHtml(
            $childAlias,
            false
        );
    }
}
