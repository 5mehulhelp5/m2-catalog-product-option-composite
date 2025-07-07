<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Controller\Quote\Item;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Product;
use Infrangible\Core\Helper\Registry;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Bundle extends Action implements ActionInterface
{
    /** @var Session */
    protected $checkoutSession;

    /** @var Registry */
    protected $registryHelper;

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Variables */
    protected $variables;

    /** @var Product */
    protected $productHelper;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Registry $registryHelper,
        PageFactory $resultPageFactory,
        Variables $variables,
        Product $productHelper
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->registryHelper = $registryHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->variables = $variables;
        $this->productHelper = $productHelper;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();

        if ($quote) {
            $itemId = $this->getRequest()->getParam('item_id');

            if (! $itemId) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getProduct()->getTypeId() === 'bundle') {
                        $itemId = $item->getId();
                    }
                }
            }

            if ($itemId) {
                /** @var Item $item */
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getId() == $itemId) {
                        $this->registryHelper->register(
                            'current_item',
                            $item
                        );

                        $product =
                            $this->productHelper->loadProduct($this->variables->intValue($item->getData('product_id')));

                        $this->registryHelper->register(
                            'current_product',
                            $product
                        );

                        $item->setProduct($product);

                        $resultPage = $this->resultPageFactory->create();

                        $headline = $product->getData('quote_item_bundle_headline');

                        $resultPage->getConfig()->getTitle()->set(
                            $this->variables->isEmpty($headline) ? $product->getName() : $headline
                        );

                        return $resultPage;
                    }
                }
            }
        }

        $this->_forward('noRoute');

        return null;
    }
}
