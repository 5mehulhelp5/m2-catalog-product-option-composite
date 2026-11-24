<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;

use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;
use Infrangible\CatalogProductOptionComposite\Traits\TypedOptionsRenderer;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Template\Context;

/**
 * Renders a custom option with values of a bundle item for a product of a bundle option: drop-downs and multiple
 * selects with the block Select\Multiple, radio buttons and checkboxes with the block Select\Checkable. Observers of
 * the event "catalog_product_option_composite_quote_item_bundle_select" can choose the block for other option types.
 */
class Select extends AbstractOptions implements ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;
    use TypedOptionsRenderer;

    /**
     * @param Context $context
     * @param PricingHelper $pricingHelper
     * @param CatalogHelper $catalogData
     * @param Select\CheckableFactory $checkableFactory
     * @param Select\MultipleFactory $multipleFactory
     * @param Registry $registryHelper
     * @param ManagerInterface $eventManager
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        PricingHelper $pricingHelper,
        CatalogHelper $catalogData,
        protected Select\CheckableFactory $checkableFactory,
        protected Select\MultipleFactory $multipleFactory,
        protected Registry $registryHelper,
        protected ManagerInterface $eventManager,
        array $data = []
    ) {
        parent::__construct($context, $pricingHelper, $catalogData, $data);
    }

    /**
     * Renders the values of the option, with the option as "current_option" in the registry.
     *
     * @return string
     */
    public function getValuesHtml(): string
    {
        $option = $this->getOption();
        $optionType = $option->getType();

        $this->registryHelper->register('current_option', $option, true, true);

        $resultData = new DataObject(
            [
                'use_multiple_block'  => $optionType === ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN ||
                    $optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                'use_checkable_block' => $optionType === ProductCustomOptionInterface::OPTION_TYPE_RADIO ||
                    $optionType === ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX
            ]
        );

        $this->eventManager->dispatch(
            'catalog_product_option_composite_quote_item_bundle_select',
            ['option' => $option, 'result' => $resultData]
        );

        if ($resultData->getData('use_multiple_block')) {
            $optionBlock = $this->multipleFactory->create();
        } elseif ($resultData->getData('use_checkable_block')) {
            $optionBlock = $this->checkableFactory->create();
        } else {
            $optionBlock = null;
        }

        $result = '';

        if ($optionBlock !== null) {
            $optionBlock->setItem($this->getItem());
            $optionBlock->setBundleOption($this->getBundleOption());
            $optionBlock->setProduct($this->getProduct());
            $optionBlock->setOption($option);
            $optionBlock->setData('skip_js_reload_price', 1);

            $result = $optionBlock->toHtml();
        }

        $this->registryHelper->unregister('current_option');

        return $result;
    }
}
