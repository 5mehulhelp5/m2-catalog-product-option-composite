<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\Select;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;
use Infrangible\CatalogProductOptionComposite\Traits\TypedOptionsRenderer;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template\Context;

class Multiple extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Multiple implements
    Type\ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;
    use TypedOptionsRenderer;

    /**
     * @param Context $context
     * @param PricingHelper $pricingHelper
     * @param CatalogHelper $catalogData
     * @param Data $helper
     * @param Variables $variables
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        PricingHelper $pricingHelper,
        CatalogHelper $catalogData,
        protected Data $helper,
        protected Variables $variables,
        array $data = []
    ) {
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    /**
     * Renders the drop-down or multiple select with the values that are available for the product.
     *
     * The select is named "cart[<item id>][options][<option id>]".
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _toHtml(): string
    {
        $option = $this->getOption();
        $itemId = $this->variables->stringValue($this->getItem()->getId());
        $optionId = $this->variables->stringValue($option->getId());
        $isMultiple = $option->getType() === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE;
        $require = $option->getIsRequire() ? ' required' : '';
        $name = sprintf('cart[%s][options][%s]%s', $itemId, $optionId, $isMultiple ? '[]' : '');

        $select = $this->getLayout()->createBlock(Select::class);

        if (! $select instanceof Select) {
            return '';
        }

        $select->setData(
            [
                'id'    => sprintf('cart_%s_select_%s', $itemId, $optionId),
                'class' => $require . ' product-custom-option admin__control-select',
                'name'  => $name
            ]
        );

        if ($isMultiple) {
            $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
        } else {
            $select->addOption('', __('-- Please Select --')->render());
        }

        if (! $this->addValues($select, $option)) {
            return '';
        }

        $extraParams = $isMultiple ? ' multiple="multiple"' : '';

        if (! $this->getDataUsingMethod('skip_js_reload_price')) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }

        $select->setDataUsingMethod('extra_params', $extraParams . sprintf(' data-selector="%s"', $name));

        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $optionId);

        if ($configValue) {
            $select->setDataUsingMethod('value', $configValue);
        }

        return $select->getHtml();
    }

    /**
     * Adds the values that are available for the product of the bundle option, returns whether there are any.
     *
     * @param Select $select
     * @param Option $option
     *
     * @return bool
     */
    private function addValues(Select $select, Option $option): bool
    {
        $hasValues = false;

        $store = $this->getProduct()->getStore();

        foreach ($option->getValues() ?? [] as $optionValue) {
            if (! $optionValue instanceof Option\Value ||
                ! $this->helper->isProductOptionValueAvailableForItemBundleOptionSelection(
                    $optionValue,
                    $this->getItem(),
                    $this->getBundleOption(),
                    $this->getProduct()
                )) {

                continue;
            }

            $isPercentPriceType = $optionValue->getPriceType() === 'percent';

            $priceStr = $this->_formatPrice(
                ['is_percent' => $isPercentPriceType, 'pricing_value' => $optionValue->getPrice($isPercentPriceType)],
                false
            );

            $select->addOption(
                $this->variables->stringValue($optionValue->getOptionTypeId()),
                $optionValue->getTitle() . ' ' . strip_tags($priceStr),
                ['price' => $this->pricingHelper->currencyByStore($optionValue->getPrice(true), $store, false)]
            );

            $hasValues = true;
        }

        return $hasValues;
    }
}
