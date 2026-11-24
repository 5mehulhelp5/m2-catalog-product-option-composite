<?php /** @noinspection PhpDeprecationInspection The cart helper of m2-core saves the options with the cart model. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Controller\Quote\Item;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Cart as CartHelper;
use Magento\Bundle\Model\Option as BundleOption;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

/**
 * Saves the custom options of the products of a bundle item in the cart. Options with values of the bundle that are
 * available for the products of the item and that are missing in the request are removed from the item.
 */
class Update implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param Validator $formKeyValidator
     * @param Session $checkoutSession
     * @param Cart $cart
     * @param CartHelper $cartHelper
     * @param Data $helper
     * @param ManagerInterface $eventManager
     * @param RedirectFactory $resultRedirectFactory
     * @param MessageManager $messageManager
     * @param Variables $variables
     * @param LoggerInterface $logging
     */
    public function __construct(
        protected RequestInterface $request,
        protected Validator $formKeyValidator,
        protected Session $checkoutSession,
        protected Cart $cart,
        protected CartHelper $cartHelper,
        protected Data $helper,
        protected ManagerInterface $eventManager,
        protected RedirectFactory $resultRedirectFactory,
        protected MessageManager $messageManager,
        protected Variables $variables,
        protected LoggerInterface $logging
    ) {
    }

    /**
     * Saves the custom options and returns to the cart.
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('checkout/cart');

        $itemId = $this->request->getParam('item_id');

        if (! $this->formKeyValidator->validate($this->request) || ! is_scalar($itemId) || (string)$itemId === '') {
            return $resultRedirect;
        }

        $itemId = (string)$itemId;

        try {
            $quote = $this->checkoutSession->getQuote();

            $cartData = $this->getCartData($quote, $itemId);

            $cartDataObject = new DataObject($cartData);

            $this->eventManager->dispatch(
                'catalog_product_option_composite_quote_item_bundle_update',
                [
                    'request'   => $this->request,
                    'quote'     => $quote,
                    'cart'      => $this->cart,
                    'cart_data' => $cartDataObject
                ]
            );

            $this->cartHelper->addItemCustomOptions($this->cart, $this->getArray($cartDataObject->getData()));

            $this->cart->save();
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (Exception $exception) {
            $this->logging->error($exception);

            $this->messageManager->addErrorMessage(__('We can\'t update the shopping cart.')->render());
        }

        return $resultRedirect;
    }

    /**
     * Returns the posted cart data, after the observers of the events changed it.
     *
     * Every option with values of the item that is available for its products and not in the request gets an empty
     * value: no value was selected.
     *
     * @param Quote $quote
     * @param string $itemId
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    protected function getCartData(Quote $quote, string $itemId): array
    {
        $cartData = $this->getArray($this->request->getParam('cart'));

        $itemData = $this->getArray($cartData[ $itemId ] ?? []);
        $itemData[ 'options' ] = $this->getArray($itemData[ 'options' ] ?? []);
        $cartData[ $itemId ] = $itemData;

        foreach ($quote->getAllItems() as $item) {
            if (! $item instanceof Item || $this->variables->stringValue($item->getId()) !== $itemId) {
                continue;
            }

            foreach ($this->helper->getItemBundleSelections($item) as $itemBundleSelection) {
                $bundleOption = $itemBundleSelection[ 'option' ];
                $selection = $itemBundleSelection[ 'product' ];

                $productOptions = $this->getOptions($item, $bundleOption, $selection);

                foreach ($productOptions as $option) {
                    $optionId = $this->variables->stringValue($option->getId());

                    // the page has fields for options with values only, other options keep their value
                    if ($option->getGroupByType() === Option::OPTION_GROUP_SELECT &&
                        ! array_key_exists($optionId, $cartData[ $itemId ][ 'options' ])) {
                        $cartData[ $itemId ][ 'options' ][ $optionId ] = null;
                    }
                }

                $cartData = $this->dispatchCartData(
                    'catalog_product_option_composite_quote_item_bundle_update_item_selection',
                    $cartData,
                    [
                        'request'         => $this->request,
                        'quote'           => $quote,
                        'cart'            => $this->cart,
                        'item'            => $item,
                        'bundle_option'   => $bundleOption,
                        'selection'       => $selection,
                        'product_options' => $productOptions
                    ]
                );
            }

            $cartData = $this->dispatchCartData(
                'catalog_product_option_composite_quote_item_bundle_update_item',
                $cartData,
                [
                    'request' => $this->request,
                    'quote'   => $quote,
                    'cart'    => $this->cart,
                    'item'    => $item
                ]
            );
        }

        return $cartData;
    }

    /**
     * Returns the options of the bundle that are available for the product of the bundle option of the item.
     *
     * @param Item $item
     * @param BundleOption $bundleOption
     * @param Product $product
     *
     * @return Option[]
     */
    protected function getOptions(Item $item, BundleOption $bundleOption, Product $product): array
    {
        $options = [];

        /** @var Option $productOption */
        foreach ($item->getProduct()->getOptions() ?? [] as $productOption) {
            if ($this->helper->isProductOptionAvailableForItemBundleOptionSelection(
                $productOption,
                $item,
                $bundleOption,
                $product
            )) {
                $options[] = $productOption;
            }
        }

        return $options;
    }

    /**
     * Dispatches the event with the cart data and returns the cart data that the observers changed.
     *
     * @param string $eventName
     * @param array $cartData
     * @param array $eventData
     * @phpstan-param array<int|string, mixed> $cartData
     * @phpstan-param array<string, mixed> $eventData
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    private function dispatchCartData(string $eventName, array $cartData, array $eventData): array
    {
        $cartDataObject = new DataObject($cartData);

        $eventData[ 'cart_data' ] = $cartDataObject;

        $this->eventManager->dispatch($eventName, $eventData);

        return $this->getArray($cartDataObject->getData());
    }

    /**
     * Returns the value if it is an array, else an empty array.
     *
     * @param mixed $value
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    private function getArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}
