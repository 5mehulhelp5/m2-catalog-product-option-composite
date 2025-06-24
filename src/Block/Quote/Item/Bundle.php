<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item;

use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Bundle extends Template
{
    /** @var Registry */
    protected $registryHelper;

    public function __construct(Context $context, Registry $registryHelper, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->registryHelper = $registryHelper;
    }

    public function getItem(): Item
    {
        return $this->registryHelper->registry('current_item');
    }

    public function getProduct(): Product
    {
        return $this->getItem()->getProduct();
    }

    public function getProductsHtml(): string
    {
        return $this->getChildHtml('quote.item.bundle.products');
    }
}
