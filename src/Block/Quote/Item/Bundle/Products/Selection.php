<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options;
use LogicException;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Template;

/**
 * A product of a bundle item in the cart: image, name, price and custom options.
 */
class Selection extends Template
{
    /** @var Option|null */
    private ?Option $bundleOption = null;

    /** @var Product|null */
    private ?Product $product = null;

    /**
     * @param Template\Context $context
     * @param ImageFactory $imageFactory
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(Template\Context $context, protected ImageFactory $imageFactory, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Returns the bundle option of the product.
     *
     * @return Option
     */
    public function getBundleOption(): Option
    {
        if ($this->bundleOption === null) {
            throw new LogicException('Bundle option is not defined');
        }

        return $this->bundleOption;
    }

    /**
     * Sets the bundle option of the product.
     *
     * @param Option $bundleOption
     *
     * @return void
     */
    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }

    /**
     * Returns the product.
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        if ($this->product === null) {
            throw new LogicException('Product is not defined');
        }

        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param Product $product
     *
     * @return void
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * Returns the image of the product.
     *
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @phpstan-param array<string, string> $attributes
     *
     * @return Image
     */
    public function getImage(Product $product, string $imageId, array $attributes = []): Image
    {
        return $this->imageFactory->create($product, $imageId, $attributes);
    }

    /**
     * Renders the price of the product like in product lists.
     *
     * @param Product $product
     *
     * @return string
     */
    public function getProductPrice(Product $product): string
    {
        try {
            $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception);

            return '';
        }

        if (! $priceRender instanceof Render) {
            return '';
        }

        $priceRender->setData('is_product_list', true);

        return $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            [
                'include_container'     => true,
                'display_minimal_price' => true,
                'zone'                  => Render::ZONE_ITEM_LIST,
                'list_category_page'    => true
            ]
        );
    }

    /**
     * Renders the custom options of the bundle for the product of the bundle option.
     *
     * @param Option $bundleOption
     * @param Product $product
     *
     * @return string
     */
    public function getOptionsHtml(Option $bundleOption, Product $product): string
    {
        $optionsBlock = $this->getChildBlock('quote.item.bundle.products.selection.options');

        if (! $optionsBlock instanceof Options) {
            return '';
        }

        $optionsBlock->setBundleOption($bundleOption);
        $optionsBlock->setProduct($product);

        return $optionsBlock->toHtml();
    }
}
