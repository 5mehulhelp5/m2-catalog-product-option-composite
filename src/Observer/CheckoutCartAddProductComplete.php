<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CheckoutCartAddProductComplete implements ObserverInterface
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Http */
    protected $request;

    /** @var UrlInterface */
    protected $url;

    public function __construct(Registry $registryHelper, Context $context)
    {
        $this->registryHelper = $registryHelper;
        $this->request = $context->getRequest();
        $this->url = $context->getUrl();
    }

    public function execute(Observer $observer): void
    {
        $quoteItem = $this->registryHelper->registry('added_bundle_quote_item');

        if ($quoteItem instanceof Item) {
            $optionsPrice = $quoteItem->getData('options_price');

            /** @var Product $product */
            $product = $observer->getData('product');

            $quoteItemBundleRedirect = $product->getData('quote_item_bundle_redirect');

            if ($quoteItemBundleRedirect) {
                $redirect = true;

                $quoteItemBundleRedirectThreshold = $product->getData('quote_item_bundle_redirect_threshold');

                if ($quoteItemBundleRedirectThreshold !== null) {
                    $redirect = $optionsPrice < $quoteItemBundleRedirectThreshold;
                }

                if ($redirect) {
                    $redirectUrl = $this->url->getUrl(
                        'catalog_product_option_composite/quote_item/bundle',
                        ['item_id' => $quoteItem->getId()]
                    );

                    $this->request->setParam(
                        'return_url',
                        $redirectUrl
                    );
                }
            }
        }
    }
}
