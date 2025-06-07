<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Cart\Item\Renderer\Actions;

use Infrangible\Core\Helper\Stores;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class QuoteItemBundle extends Generic
{
    /** @var Stores */
    protected $storeHelper;

    public function __construct(Template\Context $context, Stores $storeHelper, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->storeHelper = $storeHelper;
    }

    public function getCompositeUrl(): string
    {
        return $this->getUrl(
            'infrangible_catalogproductoptioncomposite/quote_item/bundle',
            [
                'item_id' => $this->getItem()->getId()
            ]
        );
    }

    public function showCompositeUrl(): bool
    {
        if ($this->getItem()->getProduct()->getTypeId() !== 'bundle') {
            return false;
        }

        return $this->storeHelper->getStoreConfigFlag(
            'infrangible_catalogproductoptioncomposite/general/show_composite_url',
            true
        );
    }
}
