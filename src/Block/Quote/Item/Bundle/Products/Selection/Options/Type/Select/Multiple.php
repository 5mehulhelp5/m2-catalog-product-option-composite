<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\Select;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\ItemBundleSelectionOptionInterface;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Multiple extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Multiple implements ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;

    /** @var Data */
    protected $helper;

    public function __construct(
        Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        Data $helper,
        array $data = [],
        CalculateCustomOptionCatalogRule $calculateCustomOptionCatalogRule = null,
        CalculatorInterface $calculator = null,
        PriceCurrencyInterface $priceCurrency = null
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $data,
            $calculateCustomOptionCatalogRule,
            $calculator,
            $priceCurrency
        );

        $this->helper = $helper;
    }

    /**
     * @throws LocalizedException
     */
    protected function _toHtml(): string
    {
        $item = $this->getItem();
        $option = $this->getOption();
        $optionType = $option->getType();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
        $require = $option->getIsRequire() ? ' required' : '';
        $extraParams = '';

        /** @var Select $select */
        $select = $this->getLayout()->createBlock(Select::class);

        $select->setData(
            [
                'id'    => sprintf(
                    'cart_%s_select_%s',
                    $item->getId(),
                    $option->getId()
                ),
                'class' => $require . ' product-custom-option admin__control-select'
            ]
        );

        $select = $this->insertSelectOption(
            $select,
            $item,
            $option
        );

        $hasValues = $this->processSelectOption(
            $select,
            $option
        );

        if (! $hasValues) {
            return '';
        }

        if ($optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {
            $extraParams = ' multiple="multiple"';
        }

        if (! $this->getDataUsingMethod('skip_js_reload_price')) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }

        $extraParams .= sprintf(
            ' data-selector="%s"',
            $select->getDataUsingMethod('name')
        );

        $select->setDataUsingMethod(
            'extra_params',
            $extraParams
        );

        if ($configValue) {
            $select->setDataUsingMethod(
                'value',
                $configValue
            );
        }

        return $select->getHtml();
    }

    private function insertSelectOption(Select $select, AbstractItem $item, Option $option): Select
    {
        $require = $option->getIsRequire() ? ' required' : '';

        if ($option->getType() === ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN) {
            $select->setDataUsingMethod(
                'name',
                sprintf(
                    'cart[%s][options][%s]',
                    $item->getId(),
                    $option->getId()
                )
            );
            $select->addOption(
                '',
                __('-- Please Select --')
            );
        } else {
            $select->setDataUsingMethod(
                'name',
                sprintf(
                    'cart[%s][options][%s][]',
                    $item->getId(),
                    $option->getId()
                )
            );

            $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
        }

        return $select;
    }

    private function processSelectOption(Select $select, Option $option): bool
    {
        $hasValues = false;

        $store = $this->getProduct()->getStore();

        foreach ($option->getValues() as $optionValue) {
            if ($this->helper->isProductOptionValueAvailableForItemBundleOptionSelection(
                $optionValue,
                $this->getItem(),
                $this->getBundleOption(),
                $this->getProduct()
            )) {
                $isPercentPriceType = $optionValue->getPriceType() === 'percent';

                $priceStr = $this->_formatPrice(
                    [
                        'is_percent'    => $isPercentPriceType,
                        'pricing_value' => $optionValue->getPrice($isPercentPriceType)
                    ],
                    false
                );

                $select->addOption(
                    $optionValue->getOptionTypeId(),
                    $optionValue->getTitle() . ' ' . strip_tags($priceStr),
                    [
                        'price' => $this->pricingHelper->currencyByStore(
                            $optionValue->getPrice(true),
                            $store,
                            false
                        )
                    ]
                );

                $hasValues = true;
            }
        }

        return $hasValues;
    }
}
