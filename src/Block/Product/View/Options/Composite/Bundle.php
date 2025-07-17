<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Bundle extends Composite
{
    /** @var Data */
    protected $helper;

    /** @var Format */
    protected $localeFormat;

    /** @var \Infrangible\CatalogProductOption\Helper\Data */
    protected $productOptionHelper;

    /** @var Option */
    private $bundleOption;

    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        Json $json,
        Variables $variables,
        Data $helper,
        Format $localeFormat,
        \Infrangible\CatalogProductOption\Helper\Data $productOptionHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registryHelper,
            $json,
            $variables,
            $data
        );

        $this->helper = $helper;
        $this->localeFormat = $localeFormat;
        $this->productOptionHelper = $productOptionHelper;
    }

    public function getBundleOption(): Option
    {
        return $this->bundleOption;
    }

    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();

        foreach ($options as $key => $option) {
            if (! $this->helper->isProductOptionValueAvailableForBundleOption(
                $option,
                $this->getBundleOption()
            )) {
                unset($options[ $key ]);
            }
        }

        return $options;
    }

    public function isBundleOption(): bool
    {
        return true;
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
        try {
            $renderer = $this->getLayout()->getBlock('product.price.render.default');

            return $renderer->renderAmount(
                $optionAmount,
                $customOptionPrice,
                $this->getProduct(),
                $arguments
            );
        } catch (LocalizedException $exception) {
            return '';
        }
    }

    public function getOptionsPrice(): float
    {
        return 0;
    }

    public function getPriceFormatJson(): string
    {
        return $this->json->encode($this->localeFormat->getPriceFormat());
    }

    public function getPricesJson(): string
    {
        return $this->json->encode(
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
                ]
            ]
        );
    }

    public function getOptionsJsonConfig(): string
    {
        return $this->productOptionHelper->getOptionsJsonConfig($this->getOptions());
    }
}
