<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Catalog\Product\View\Type\Bundle\Options;

use Infrangible\CatalogProductOptionComposite\Helper\Output;
use LogicException;
use Magento\Bundle\Model\Option;
use Magento\Framework\View\Element\Template;

/**
 * Renders the custom options of a bundle product that are restricted to products of a bundle option below this bundle
 * option on the product page.
 */
class Options extends Template
{
    /** @var Option|null */
    private ?Option $bundleOption = null;

    /**
     * @param Template\Context $context
     * @param Output $outputHelper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(Template\Context $context, protected Output $outputHelper, array $data = [])
    {
        parent::__construct($context, $data);
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
     * Renders the custom options of the bundle option.
     *
     * @return string
     */
    public function renderBundleOptionsHtml(): string
    {
        return $this->outputHelper->renderBundleOptionsHtml($this, $this->getBundleOption());
    }

    /**
     * Renders the price box of the custom options of the bundle option.
     *
     * @return string
     */
    public function renderBundleOptionsPriceHtml(): string
    {
        return $this->outputHelper->renderBundleOptionsPriceHtml($this, $this->getBundleOption());
    }
}
