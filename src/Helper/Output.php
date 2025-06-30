<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Helper;

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

    public function __construct(
        Context $context,
        Data $helper,
        \Infrangible\CatalogProductOptionWrapper\Helper\Data $wrapperHelper
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->wrapperHelper = $wrapperHelper;
    }

    public function renderBundleOptionsHtml(AbstractBlock $block, Product $product, Option $bundleOption): string
    {
        $optionsHtml = '';

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
