<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CheckoutCartProductAddAfter implements ObserverInterface
{
    /** @var Registry */
    protected $registryHelper;

    public function __construct(Registry $registryHelper)
    {
        $this->registryHelper = $registryHelper;
    }

    public function execute(Observer $observer): void
    {
        $quoteItem = $observer->getData('quote_item');

        if ($quoteItem instanceof Item) {
            /** @var Product $product */
            $product = $observer->getData('product');

            if ($product && $product->getTypeId() === Type::TYPE_CODE) {
                $this->registryHelper->register(
                    'added_bundle_quote_item',
                    $quoteItem
                );
            }
        }
    }
}
