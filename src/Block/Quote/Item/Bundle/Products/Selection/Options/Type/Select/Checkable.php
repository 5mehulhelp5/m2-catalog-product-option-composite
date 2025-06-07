<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\Select;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options\Type\ItemBundleSelectionOptionInterface;
use Infrangible\CatalogProductOptionComposite\Traits\ItemBundleSelectionOption;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Checkable extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Checkable implements ItemBundleSelectionOptionInterface
{
    use ItemBundleSelectionOption;

    /** @var string */
    protected $_template = 'Infrangible_CatalogProductOptionComposite::quote/item/bundle/products/selection/options/type/select/checkable.phtml';

    public function getSkipJsReloadPrice(): bool
    {
        return $this->getData('skip_js_reload_price') == 1;
    }
}
