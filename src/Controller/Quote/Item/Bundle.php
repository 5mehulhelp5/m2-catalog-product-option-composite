<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Controller\Quote\Item;

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

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Registry $registryHelper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->registryHelper = $registryHelper;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
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
                        $this->registryHelper->register(
                            'current_product',
                            $item->getProduct()
                        );

                        $resultPage = $this->resultPageFactory->create();

                        $resultPage->getConfig()->getTitle()->set($item->getProduct()->getName());

                        return $resultPage;
                    }
                }
            }
        }

        $this->_forward('noRoute');

        return null;
    }
}
