<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;

/**
 * Redirects to the page of the custom options of the added bundle item, if the bundle product asks for it
 * ("quote_item_bundle_redirect") and the price of the selected custom options is below its threshold
 * ("quote_item_bundle_redirect_threshold", always without threshold).
 */
class CheckoutCartAddProductComplete implements ObserverInterface
{
    /**
     * @param Data $helper
     * @param Http $request
     * @param UrlInterface $url
     */
    public function __construct(
        protected Data $helper,
        protected Http $request,
        protected UrlInterface $url
    ) {
    }

    /**
     * Sets the return URL of the request to the page of the custom options of the added bundle item.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $quoteItem = $this->helper->getAddedBundleItem();
        $product = $observer->getData('product');

        if ($quoteItem === null || ! $product instanceof Product || ! $product->getData('quote_item_bundle_redirect')) {
            return;
        }

        $threshold = $product->getData('quote_item_bundle_redirect_threshold');

        $optionsPrice = $quoteItem->getData('options_price');

        if (is_numeric($threshold) && is_numeric($optionsPrice) && (float)$optionsPrice >= (float)$threshold) {
            return;
        }

        $this->request->setParam(
            'return_url',
            $this->url->getUrl('catalog_product_option_composite/quote_item/bundle', ['item_id' => $quoteItem->getId()])
        );
    }
}
