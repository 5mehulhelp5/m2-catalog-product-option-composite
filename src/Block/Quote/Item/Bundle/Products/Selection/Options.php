<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\ItemBundleSelectionOptionInterface;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Options extends \Magento\Catalog\Block\Product\View\Options
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Data */
    protected $helper;

    /** @var \Magento\Catalog\Helper\Data */
    protected $catalogHelper;

    /** @var Format */
    protected $localeFormat;

    /** @var Option */
    private $bundleOption;

    /** @var Product */
    private $product;

    public function __construct(
        Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        EncoderInterface $jsonEncoder,
        Product\Option $option,
        \Magento\Framework\Registry $registry,
        ArrayUtils $arrayUtils,
        Registry $registryHelper,
        Data $helper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        Format $localeFormat,
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

        $this->registryHelper = $registryHelper;
        $this->helper = $helper;
        $this->catalogHelper = $catalogHelper;
        $this->localeFormat = $localeFormat;
    }

    public function getBundleOption(): Option
    {
        return $this->bundleOption;
    }

    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product = null): void
    {
        $this->product = $product;
    }

    public function getItem(): Item
    {
        return $this->registryHelper->registry('current_item');
    }

    /**
     * @return Product\Option[]
     */
    public function getOptions(): array
    {
        $productOptions = $this->getItem()->getProduct()->getOptions();

        $options = [];

        /** @var Product\Option $productOption */
        foreach ($productOptions as $productOption) {
            if (! $this->helper->isProductOptionAvailableForBundleSelection(
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

    public function getBundleOptionHtml(Option $bundleOption, Product $product, Product\Option $option): string
    {
        $type = $option->getType();

        $groupName = $option->getGroupByType($type);

        $groupName = $groupName == '' ? 'default' : $groupName;

        $rendererBlockId = sprintf(
            '%s.type.%s',
            $this->getNameInLayout(),
            $groupName
        );

        /** @var ItemBundleSelectionOptionInterface $renderer */
        $renderer = $this->getChildBlock($rendererBlockId);

        if (! $renderer) {
            $rendererBlockId = sprintf(
                '%s.type.default',
                $this->getNameInLayout()
            );

            /** @var ItemBundleSelectionOptionInterface $renderer */
            $renderer = $this->getChildBlock($rendererBlockId);
        }

        if ($renderer) {
            $renderer->setItem($this->getItem());
            $renderer->setBundleOption($bundleOption);
            $renderer->setProduct($product);
            $renderer->setOption($option);

            return $this->getChildHtml(
                $rendererBlockId,
                false
            );
        }

        return '';
    }

    public function getOptionsPrice(): float
    {
        $optionsPrice = 0.;

        foreach ($this->getOptions() as $option) {
            $optionsPrice += $this->getOptionPrice(
                $this->getProduct(),
                $option
            );
        }

        return $optionsPrice;
    }

    private function getOptionPrice(Product $product, Product\Option $option): float
    {
        $type = $option->getType();

        try {
            $group = $option->groupFactory($type);
        } catch (LocalizedException $exception) {
            return 0;
        }

        $item = $this->getItem();

        $itemOption = $item->getOptionByCode('option_' . $option->getId());

        if (! $itemOption) {
            return 0;
        }

        $group->setOption($option);
        $group->setDataUsingMethod(
            'configuration_item',
            $item
        );
        $group->setDataUsingMethod(
            'configuration_item_option',
            $itemOption
        );

        $basePrice = $product->getFinalPrice();

        $optionPrice = $group->getOptionPrice(
            $itemOption->getValue(),
            $basePrice
        );

        return $this->catalogHelper->getTaxPrice(
            $product,
            $optionPrice,
            true
        );
    }

    public function renderAmount(array $arguments): string
    {
        /** @var CustomOptionPrice $customOptionPrice */
        $customOptionPrice = $this->getProduct()->getPriceInfo()->getPrice('custom_option_price');

        $optionAmount = $customOptionPrice->getCustomAmount(
            $this->getOptionsPrice(),
            null,
            [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true]
        );

        /** @var Render $renderer */
        $renderer = $this->getLayout()->getBlock('product.price.render.default');

        return $renderer->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $this->getProduct(),
            $arguments
        );
    }

    public function getPriceFormatJson(): string
    {
        return $this->_jsonEncoder->encode($this->localeFormat->getPriceFormat());
    }

    public function getPricesJson(): string
    {
        return $this->_jsonEncoder->encode(
            [
                'baseOldPrice' => [
                    'amount' => 0,
                ],
                'oldPrice'     => [
                    'amount' => 0,
                ],
                'basePrice'    => [
                    'amount' => 0,
                ],
                'finalPrice'   => [
                    'amount' => 0,
                ],
            ]
        );
    }
}
