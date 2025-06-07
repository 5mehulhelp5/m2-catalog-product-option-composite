<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products;

use Infrangible\CatalogProductOptionComposite\Block\Quote\Item\Bundle\Products\Selection\Options;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Selection extends Template
{
    /** @var ImageBuilder */
    protected $imageBuilder;

    /** @var Option */
    private $bundleOption;

    /** @var Product */
    private $product;

    public function __construct(Context $context, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->imageBuilder = $context->getImageBuilder();
    }

    public function getBundleOption(): Option
    {
        return $this->bundleOption;
    }

    public function setBundleOption(Option $bundleOption): void
    {
        $this->bundleOption = $bundleOption;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function getImage(Product $product, string $imageId, array $attributes = []): Image
    {
        return $this->imageBuilder->create(
            $product,
            $imageId,
            $attributes
        );
    }

    /**
     * @throws LocalizedException
     */
    public function getProductPrice(Product $product): string
    {
        $priceRender = $this->getPriceRender();

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
     * @throws LocalizedException
     */
    protected function getPriceRender(): Render
    {
        /** @var Render $renderer */
        $renderer = $this->getLayout()->getBlock('product.price.render.default');

        $renderer->setData(
            'is_product_list',
            true
        );

        return $renderer;
    }

    public function getOptionsHtml(Option $bundleOption, Product $product): string
    {
        /** @var Options $optionsBlock */
        $optionsBlock = $this->getChildBlock('quote.item.bundle.products.selection.options');

        if ($optionsBlock) {
            $optionsBlock->setBundleOption($bundleOption);
            $optionsBlock->setProduct($product);

            return $optionsBlock->toHtml();
        } else {
            return '';
        }
    }
}
