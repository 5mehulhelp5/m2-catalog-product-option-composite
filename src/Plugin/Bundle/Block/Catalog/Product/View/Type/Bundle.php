<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Plugin\Bundle\Block\Catalog\Product\View\Type;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Bundle
{
    /** @var Data */
    protected $helper;

    /** @var \Infrangible\CatalogProductOptionWrapper\Helper\Data */
    protected $wrapperHelper;

    public function __construct(Data $helper, \Infrangible\CatalogProductOptionWrapper\Helper\Data $wrapperHelper)
    {
        $this->helper = $helper;
        $this->wrapperHelper = $wrapperHelper;
    }

    public function afterGetOptionHtml(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject,
        string $result,
        Option $bundleOption
    ): string {
        $optionsHtml = '';

        $product = $subject->getProduct();

        /** @var \Magento\Catalog\Model\Product\Option $option */
        foreach ($product->getOptions() as $option) {
            if ($this->helper->isProductOptionValueAvailableForBundleOption(
                $option,
                $bundleOption
            )) {
                $optionsHtml .= $this->wrapperHelper->renderWrapper(
                    $subject,
                    $option,
                    $this->getOptionHtml(
                        $subject,
                        $option
                    )
                );
            }
        }

        return $result . $optionsHtml;
    }

    public function getOptionHtml(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $block,
        \Magento\Catalog\Model\Product\Option $option
    ): string {
        $group = $option->getGroupByType($option->getType());

        $childAlias = sprintf(
            'option.%s',
            $group == '' ? 'default' : $group
        );

        /** @var AbstractOptions $renderer */
        $renderer = $block->getChildBlock($childAlias);

        $product = $block->getProduct();

        $renderer->setProduct($product);
        $renderer->setOption($option);

        return $block->getChildHtml(
            $childAlias,
            false
        );
    }
}
