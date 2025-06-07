<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Products extends Template
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Data */
    protected $helper;

    public function __construct(Template\Context $context, Registry $registryHelper, Data $helper, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->registryHelper = $registryHelper;
        $this->helper = $helper;
    }

    public function getItem(): Item
    {
        return $this->registryHelper->registry('current_item');
    }

    public function getItemBundleSelections(): array
    {
        return $this->helper->getItemBundleSelections($this->getItem());
    }

    public function getSelectionHtml(Option $bundleOption, Product $product): string
    {
        /** @var Selection $selectionBlock */
        $selectionBlock = $this->getChildBlock('quote.item.bundle.products.selection');

        if ($selectionBlock) {
            $selectionBlock->setBundleOption($bundleOption);
            $selectionBlock->setProduct($product);

            return $selectionBlock->toHtml();
        } else {
            return '';
        }
    }
}
