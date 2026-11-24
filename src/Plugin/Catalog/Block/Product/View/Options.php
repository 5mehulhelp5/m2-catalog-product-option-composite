<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Plugin\Catalog\Block\Product\View;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product\Option;

/**
 * Custom options of bundle products that are restricted to products of bundle options are shown with these bundle
 * options instead of with the other custom options.
 */
class Options
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Returns no HTML for options of bundle products that are restricted to products of bundle options.
     *
     * @param \Magento\Catalog\Block\Product\View\Options $subject
     * @param callable $proceed
     * @param Option $option
     *
     * @return string
     */
    public function aroundGetOptionHtml(
        \Magento\Catalog\Block\Product\View\Options $subject,
        callable $proceed,
        Option $option
    ): string {
        if ($subject->getProduct()->getTypeId() === Type::TYPE_CODE &&
            count($this->helper->getAllowHideProductIds($option)) > 0) {

            return '';
        }

        $result = $proceed($option);

        return is_string($result) ? $result : '';
    }
}
