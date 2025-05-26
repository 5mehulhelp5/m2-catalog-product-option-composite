<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Observer;

use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
abstract class ItemOptionAvailable
{
    /** @var Data */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    protected function isDataObjectAvailable(AbstractItem $item, DataObject $dataObject): bool
    {
        $allowHideProductIds = $dataObject->getData('allow_hide_product_ids');

        if ($allowHideProductIds) {
            foreach ($this->helper->getCompositeProductIds($item) as $itemProductId) {
                if (in_array(
                    $itemProductId,
                    $allowHideProductIds
                )) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
