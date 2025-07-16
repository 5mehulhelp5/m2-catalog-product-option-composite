<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Catalog\Product\View\Type\Bundle\Options;

use Infrangible\CatalogProductOptionComposite\Helper\Output;
use Magento\Bundle\Model\Option;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Options extends Template
{
    /** @var Output */
    protected $outputHelper;

    /** @var Option */
    private $bundleOption;

    public function __construct(Template\Context $context, Output $outputHelper, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->outputHelper = $outputHelper;
    }

    public function getBundleOption(): Option
    {
        return $this->bundleOption;
    }

    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }

    public function renderBundleOptionsHtml(): string
    {
        return $this->outputHelper->renderBundleOptionsHtml(
            $this,
            $this->getBundleOption()
        );
    }

    public function renderBundleOptionsPriceHtml(): string
    {
        return $this->outputHelper->renderBundleOptionsPriceHtml(
            $this,
            $this->getBundleOption()
        );
    }
}
