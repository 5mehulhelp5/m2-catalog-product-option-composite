<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Controller\Quote\Item;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Product;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Magento\Bundle\Model\Product\Type;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

/**
 * Shows the page with the custom options of the products of a bundle item in the cart: the item of the parameter
 * "item_id", or the last bundle item of the cart.
 */
class Bundle implements HttpGetActionInterface
{
    /**
     * @param RequestInterface $request
     * @param Session $checkoutSession
     * @param Registry $registryHelper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Variables $variables
     * @param Product $productHelper
     * @param Stores $storeHelper
     * @param LoggerInterface $logging
     */
    public function __construct(
        protected RequestInterface $request,
        protected Session $checkoutSession,
        protected Registry $registryHelper,
        protected PageFactory $resultPageFactory,
        protected ForwardFactory $resultForwardFactory,
        protected Variables $variables,
        protected Product $productHelper,
        protected Stores $storeHelper,
        protected LoggerInterface $logging
    ) {
    }

    /**
     * Shows the page of the bundle item, or the page "not found" if the cart has no such item.
     *
     * @return Page|Forward
     */
    public function execute(): Page|Forward
    {
        try {
            $item = $this->getItem();

            if ($item === null) {
                return $this->resultForwardFactory->create()->forward('noRoute');
            }

            $product = $this->productHelper->loadProduct(
                $this->variables->intValue($item->getData('product_id')),
                $this->variables->intValue($this->storeHelper->getStore()->getId())
            );
        } catch (Exception $exception) {
            $this->logging->error($exception);

            return $this->resultForwardFactory->create()->forward('noRoute');
        }

        $item->setProduct($product);

        $this->registryHelper->register('current_item', $item, false, true);
        $this->registryHelper->register('current_product', $product, false, true);

        $resultPage = $this->resultPageFactory->create();

        $headline = $product->getData('quote_item_bundle_headline');

        $title = is_string($headline) && trim($headline) !== '' ? $headline : $product->getName();

        $resultPage->getConfig()->getTitle()->set($this->variables->stringValue($title));

        return $resultPage;
    }

    /**
     * Returns the bundle item of the parameter "item_id", or the last bundle item of the cart without parameter.
     *
     * @return Item|null
     * @throws Exception
     */
    private function getItem(): ?Item
    {
        $itemId = $this->request->getParam('item_id');
        $bundleItem = null;

        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            if (! $item instanceof Item || $item->getProduct()->getTypeId() !== Type::TYPE_CODE) {
                continue;
            }

            if ($itemId === null || $itemId === '') {
                $bundleItem = $item;
            } elseif (is_scalar($itemId) && (string)$itemId === $this->variables->stringValue($item->getId())) {
                return $item;
            }
        }

        return $bundleItem;
    }
}
