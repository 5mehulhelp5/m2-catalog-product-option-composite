<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOption\Helper\Data as ProductOptionHelper;
use Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Traits\OptionsPriceBox;
use Infrangible\Core\Helper\Registry;
use LogicException;
use Magento\Bundle\Model\Option;
use Magento\Framework\Locale\Format;
use Magento\Framework\View\Element\Template;

/**
 * The custom options of a bundle product that are restricted to products of a bundle option, shown with this bundle
 * option: the configuration of the script that shows and hides them, and their price box.
 */
class Bundle extends Composite
{
    use OptionsPriceBox;

    /** @var Option|null */
    private ?Option $bundleOption = null;

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param Json $json
     * @param Variables $variables
     * @param Data $helper
     * @param Format $localeFormat
     * @param ProductOptionHelper $productOptionHelper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        Json $json,
        Variables $variables,
        Data $helper,
        protected Format $localeFormat,
        protected ProductOptionHelper $productOptionHelper,
        array $data = []
    ) {
        parent::__construct($context, $registryHelper, $json, $variables, $helper, $data);
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
     * Returns the custom options that are restricted to products of the bundle option.
     *
     * @return \Magento\Catalog\Model\Product\Option[]
     */
    public function getOptions(): array
    {
        return array_filter(
            parent::getOptions(),
            fn($option) => $this->helper->isProductOptionValueAvailableForBundleOption(
                $option,
                $this->getBundleOption()
            )
        );
    }

    /**
     * Returns true: the options are shown with a bundle option.
     *
     * @return bool
     */
    public function isBundleOption(): bool
    {
        return true;
    }

    /**
     * Returns the price of the selected custom options when the page is shown: none are selected.
     *
     * @return float
     */
    public function getOptionsPrice(): float
    {
        return 0;
    }

    /**
     * Returns the price configuration of the custom options as JSON.
     *
     * @return string
     */
    public function getOptionsJsonConfig(): string
    {
        return $this->productOptionHelper->getOptionsJsonConfig($this->getOptions());
    }

    /**
     * Returns the id of the bundle option.
     *
     * @return string
     */
    protected function getBundleOptionKey(): string
    {
        return $this->variables->stringValue($this->getBundleOption()->getId());
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
        return $this->json->encode($data) ?? '{}';
    }
}
