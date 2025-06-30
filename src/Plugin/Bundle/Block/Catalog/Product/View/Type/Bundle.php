<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Plugin\Bundle\Block\Catalog\Product\View\Type;

use Infrangible\CatalogProductOptionComposite\Helper\Output;
use Magento\Bundle\Model\Option;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Bundle
{
    /** @var Output */
    protected $outputHelper;

    public function __construct(Output $outputHelper)
    {
        $this->outputHelper = $outputHelper;
    }

    public function afterGetOptionHtml(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject,
        string $result,
        Option $bundleOption
    ): string {
        $optionsHtml = $this->outputHelper->renderBundleOptionsHtml(
            $subject,
            $bundleOption
        );

        return $result . $optionsHtml;
    }
}
