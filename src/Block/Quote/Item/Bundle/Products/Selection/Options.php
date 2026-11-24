<?php /** @noinspection PhpDeprecationInspection The constructor of Magento's options block needs the registry. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Traits\OptionsPriceBox;
use Infrangible\CatalogProductOptionWrapper\Helper\Data as WrapperHelper;
use Infrangible\Core\Helper\Registry;
use LogicException;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item;

/**
 * The custom options of a bundle item that are available for a product of a bundle option, with their price.
 */
class Options extends \Magento\Catalog\Block\Product\View\Options
{
    use OptionsPriceBox;

    /** @var Option|null */
    private ?Option $bundleOption = null;

    /** @var Product|null */
    private ?Product $selectionProduct = null;

    /**
     * @param Context $context
     * @param PricingHelper $pricingHelper
     * @param CatalogHelper $catalogData
     * @param EncoderInterface $jsonEncoder
     * @param Product\Option $option
     * @param \Magento\Framework\Registry $registry
     * @param ArrayUtils $arrayUtils
     * @param Registry $registryHelper
     * @param Data $helper
     * @param Format $localeFormat
     * @param WrapperHelper $wrapperHelper
     * @param Variables $variables
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        PricingHelper $pricingHelper,
        CatalogHelper $catalogData,
        EncoderInterface $jsonEncoder,
        Product\Option $option,
        \Magento\Framework\Registry $registry,
        ArrayUtils $arrayUtils,
        protected Registry $registryHelper,
        protected Data $helper,
        protected Format $localeFormat,
        protected WrapperHelper $wrapperHelper,
        protected Variables $variables,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $jsonEncoder,
            $option,
            $registry,
            $arrayUtils,
            $data
        );
    }

    /**
     * Returns the bundle option.
     *
     * @return Option
     */
    public function getBundleOption(): Option
    {
        if ($this->bundleOption === null) {
            throw new LogicException('Bundle option is not defined');
        }

        return $this->bundleOption;
    }

    /**
     * Sets the bundle option.
     *
     * @param Option $bundleOption
     *
     * @return void
     */
    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }

    /**
     * Returns the product of the bundle option.
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        if ($this->selectionProduct === null) {
            throw new LogicException('Product is not defined');
        }

        return $this->selectionProduct;
    }

    /**
     * Sets the product of the bundle option.
     *
     * @param Product|null $product
     *
     * @return $this
     */
    public function setProduct(?Product $product = null): static
    {
        $this->selectionProduct = $product;

        return $this;
    }

    /**
     * Returns the bundle item of the page.
     *
     * @return Item
     */
    public function getItem(): Item
    {
        $item = $this->registryHelper->registry('current_item');

        if (! $item instanceof Item) {
            throw new LogicException('Item is not defined');
        }

        return $item;
    }

    /**
     * Returns the custom options of the bundle that are available for the product of the bundle option.
     *
     * Options that are excluded from promotion ("promote") are left out.
     *
     * @return Product\Option[]
     */
    public function getOptions(): array
    {
        $options = [];

        /** @var Product\Option $productOption */
        foreach ($this->getItem()->getProduct()->getOptions() ?? [] as $productOption) {
            if (! $this->helper->isProductOptionAvailableForItemBundleOptionSelection(
                $productOption,
                $this->getItem(),
                $this->getBundleOption(),
                $this->getProduct()
            )) {
                continue;
            }

            if ($productOption->hasData('promote') && ! $productOption->getData('promote')) {
                continue;
            }

            $options[] = $productOption;
        }

        return $options;
    }

    /**
     * Renders the option in the wrapper with the child block of its type.
     *
     * The child block is "<name>.type.<group>", or "<name>.type.default" for types without own block.
     *
     * @param Option $bundleOption
     * @param Product $product
     * @param Product\Option $option
     *
     * @return string
     */
    public function getBundleOptionHtml(Option $bundleOption, Product $product, Product\Option $option): string
    {
        $groupName = $option->getGroupByType($option->getType());

        foreach ([$groupName === '' ? 'default' : $groupName, 'default'] as $rendererGroupName) {
            $rendererBlockId = sprintf('%s.type.%s', $this->getNameInLayout(), $rendererGroupName);

            $renderer = $this->getChildBlock($rendererBlockId);

            if ($renderer instanceof Options\Type\ItemBundleSelectionOptionInterface &&
                $renderer instanceof AbstractBlock) {

                $renderer->setItem($this->getItem());
                $renderer->setBundleOption($bundleOption);
                $renderer->setProduct($product);
                $renderer->setOption($option);

                return $this->wrapperHelper->renderWrapper(
                    $this,
                    $option,
                    $this->getChildHtml($rendererBlockId, false)
                );
            }
        }

        return '';
    }

    /**
     * Returns the price of the selected values of the options.
     *
     * @return float
     */
    public function getOptionsPrice(): float
    {
        $optionsPrice = 0.;

        foreach ($this->getOptions() as $option) {
            $optionsPrice += $this->getOptionPrice($this->getProduct(), $option);
        }

        return $optionsPrice;
    }

    /**
     * Returns the price of the selected value of the option, with tax.
     *
     * @param Product $product
     * @param Product\Option $option
     *
     * @return float
     */
    private function getOptionPrice(Product $product, Product\Option $option): float
    {
        try {
            $group = $option->groupFactory($option->getType());
        } catch (LocalizedException) {
            return 0;
        }

        $item = $this->getItem();

        $itemOption = $item->getOptionsByCode()[ 'option_' . $option->getId() ] ?? null;

        if ($itemOption === null) {
            return 0;
        }

        $group->setOption($option);
        $group->setDataUsingMethod('configuration_item', $item);
        $group->setDataUsingMethod('configuration_item_option', $itemOption);

        return $this->_catalogData->getTaxPrice(
            $product,
            $group->getOptionPrice($this->variables->stringValue($itemOption->getValue()), $product->getFinalPrice()),
            true
        );
    }

    /**
     * Returns the data as JSON.
     *
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     *
     * @return string
     */
    protected function encodeJson(array $data): string
    {
        return $this->_jsonEncoder->encode($data);
    }
}
