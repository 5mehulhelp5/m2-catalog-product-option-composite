<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Controller\Quote\Item;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Update extends Cart implements HttpPostActionInterface
{
    /** @var Session */
    protected $checkoutSession;

    /** @var Data */
    protected $helper;

    /** @var \Infrangible\Core\Helper\Cart */
    protected $cartHelper;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        \Infrangible\Core\Helper\Cart $cartHelper,
        \Magento\Checkout\Model\Cart $cart,
        Data $helper
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );

        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $request = $this->getRequest();

        if (! $this->_formKeyValidator->validate($request)) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $cartData = $request->getParam('cart');

        if (! is_array($cartData)) {
            $cartData = [];
        }

        $itemId = $request->getParam('item_id');

        if ($itemId) {
            if (! array_key_exists(
                $itemId,
                $cartData
            )) {
                $cartData[ $itemId ] = [];
            }

            if (! array_key_exists(
                'options',
                $cartData[ $itemId ]
            )) {
                $cartData[ $itemId ][ 'options' ] = [];
            }

            $quote = $this->checkoutSession->getQuote();

            if ($quote) {
                /** @var Item $item */
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getId() == $itemId) {
                        foreach ($this->helper->getItemBundleSelections($item) as $itemBundleSelection) {
                            /** @var \Magento\Bundle\Model\Option $bundleOption */
                            $bundleOption = $itemBundleSelection[ 'option' ];
                            /** @var Product $selection */
                            $selection = $itemBundleSelection[ 'product' ];

                            $productOptions = $this->getOptions(
                                $item,
                                $bundleOption,
                                $selection
                            );

                            foreach ($productOptions as $option) {
                                if (! array_key_exists(
                                    $option->getId(),
                                    $cartData[ $itemId ][ 'options' ]
                                )) {
                                    $cartData[ $itemId ][ 'options' ][ $option->getId() ] = null;
                                }
                            }

                            $cartDataObject = new DataObject($cartData);

                            $this->_eventManager->dispatch(
                                'catalog_product_option_composite_quote_item_bundle_update_item_selection',
                                [
                                    'request'         => $request,
                                    'quote'           => $quote,
                                    'cart'            => $this->cart,
                                    'item'            => $item,
                                    'bundle_option'   => $bundleOption,
                                    'selection'       => $selection,
                                    'product_options' => $productOptions,
                                    'cart_data'       => $cartDataObject
                                ]
                            );

                            $cartData = $cartDataObject->getData();
                        }

                        $cartDataObject = new DataObject($cartData);

                        $this->_eventManager->dispatch(
                            'catalog_product_option_composite_quote_item_bundle_update_item',
                            [
                                'request'   => $request,
                                'quote'     => $quote,
                                'cart'      => $this->cart,
                                'item'      => $item,
                                'cart_data' => $cartDataObject
                            ]
                        );

                        $cartData = $cartDataObject->getData();
                    }
                }

                $cartDataObject = new DataObject($cartData);

                $this->_eventManager->dispatch(
                    'catalog_product_option_composite_quote_item_bundle_update',
                    [
                        'request'   => $request,
                        'quote'     => $quote,
                        'cart'      => $this->cart,
                        'cart_data' => $cartDataObject
                    ]
                );

                $cartData = $cartDataObject->getData();

                $this->cartHelper->addItemCustomOptions(
                    $this->cart,
                    $cartData
                );

                $this->cart->save();
            }
        }

        return $this->_redirect('checkout/cart');
    }

    /**
     * @return Option[]
     */
    protected function getOptions(
        Item $item,
        \Magento\Bundle\Model\Option $bundleOption,
        Product $product
    ): array {
        $productOptions = $item->getProduct()->getOptions();

        $options = [];

        foreach ($productOptions as $productOption) {
            if (! $this->helper->isProductOptionAvailableForBundleSelection(
                $productOption,
                $item,
                $bundleOption,
                $product
            )) {
                continue;
            }

            $options[] = $productOption;
        }

        return $options;
    }
}
