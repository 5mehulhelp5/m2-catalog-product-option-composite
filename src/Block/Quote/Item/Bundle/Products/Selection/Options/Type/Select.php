<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\Select\CheckableFactory;
use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\Select\MultipleFactory;
use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Select extends AbstractOptions implements ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;

    /** @var CheckableFactory */
    protected $checkableFactory;

    /** @var MultipleFactory */
    protected $multipleFactory;

    /** @var Registry */
    protected $registryHelper;

    /** @var EventManager */
    protected $eventManager;

    public function __construct(
        Context $context,
        Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        CheckableFactory $checkableFactory,
        MultipleFactory $multipleFactory,
        Registry $registryHelper,
        EventManager $eventManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $data
        );

        $this->checkableFactory = $checkableFactory;
        $this->multipleFactory = $multipleFactory;
        $this->registryHelper = $registryHelper;
        $this->eventManager = $eventManager;
    }

    public function getValuesHtml(): string
    {
        $option = $this->getOption();
        $optionType = $option->getType();

        $this->registryHelper->register(
            'current_option',
            $option,
            true
        );

        $useMultipleBlock = $optionType === ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE;
        $useCheckableBlock = $optionType === ProductCustomOptionInterface::OPTION_TYPE_RADIO ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX;

        $resultData = new DataObject(
            ['use_multiple_block' => $useMultipleBlock, 'use_checkable_block' => $useCheckableBlock]
        );

        $this->eventManager->dispatch(
            'catalog_product_option_composite_quote_item_bundle_select',
            ['option' => $option, 'result' => $resultData]
        );

        $useMultipleBlock = $resultData->getData('use_multiple_block');
        $useCheckableBlock = $resultData->getData('use_checkable_block');

        if ($useMultipleBlock) {
            $optionBlock = $this->multipleFactory->create();
        } elseif ($useCheckableBlock) {
            $optionBlock = $this->checkableFactory->create();
        } else {
            $optionBlock = null;
        }

        $result = '';

        if ($optionBlock) {
            $optionBlock->setItem($this->getItem());
            $optionBlock->setBundleOption($this->getBundleOption());
            $optionBlock->setProduct($this->getProduct());
            $optionBlock->setOption($option);
            $optionBlock->setDataUsingMethod(
                'skip_js_reload_price',
                1
            );

            try {
                $result = $optionBlock->_toHtml();
            } catch (LocalizedException $exception) {
            }
        }

        $this->registryHelper->unregister('current_option');

        return $result;
    }
}
