<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Block\Product\View\Options\Composite;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
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

    /** @var Option */
    private $bundleOption;

    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        Json $json,
        Variables $variables,
        Data $helper,
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
}
