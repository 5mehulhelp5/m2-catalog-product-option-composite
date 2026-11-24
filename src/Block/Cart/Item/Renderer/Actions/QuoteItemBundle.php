<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Cart\Item\Renderer\Actions;

use Infrangible\Core\Helper\Stores;
use Magento\Bundle\Model\Product\Type;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Template;

/**
 * The link of a bundle item in the cart to the page with the custom options of its products.
 */
class QuoteItemBundle extends Generic
{
    /**
     * @param Template\Context $context
     * @param Stores $storeHelper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(Template\Context $context, protected Stores $storeHelper, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Returns the URL of the page with the custom options of the item.
     *
     * @return string
     */
    public function getCompositeUrl(): string
    {
        return $this->getUrl(
            'catalog_product_option_composite/quote_item/bundle',
            ['item_id' => $this->getItem()->getId()]
        );
    }

    /**
     * Returns whether the link is shown: for bundle items, if the configuration allows it.
     *
     * @return bool
     */
    public function showCompositeUrl(): bool
    {
        return $this->getItem()->getProduct()->getTypeId() === Type::TYPE_CODE &&
            $this->storeHelper->getStoreConfigFlag(
                'infrangible_catalogproductoptioncomposite/general/show_composite_url',
                true
            );
    }
}
