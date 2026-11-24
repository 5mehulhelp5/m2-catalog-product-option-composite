<?php /** @noinspection PhpDeprecationInspection The plugin changes the request of the cart model. */

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Plugin\Checkout\Model;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Stores;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Removes the values of custom options of a bundle product from the request to add it to the cart, if the options
 * are restricted to products of bundle options that were not selected.
 */
class Cart
{
    /**
     * @param Stores $storeHelper
     * @param ProductRepositoryInterface $productRepository
     * @param Variables $variables
     * @param Data $helper
     */
    public function __construct(
        protected Stores $storeHelper,
        protected ProductRepositoryInterface $productRepository,
        protected Variables $variables,
        protected Data $helper
    ) {
    }

    /**
     * Removes the values of the options that are restricted to products of bundle options that were not selected.
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param mixed $productInfo
     * @param mixed $requestInfo
     *
     * @return array
     * @phpstan-return array{0: mixed, 1: mixed}
     * @noinspection PhpUnusedParameterInspection The signature of the plugin is given.
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        mixed $productInfo,
        mixed $requestInfo = null
    ): array {
        if (! is_array($requestInfo) || ! isset($requestInfo[ 'options' ]) || ! is_array($requestInfo[ 'options' ])) {
            return [$productInfo, $requestInfo];
        }

        $product = $this->getProduct($productInfo);

        if ($product === null || $product->getTypeId() !== Type::TYPE_CODE) {
            return [$productInfo, $requestInfo];
        }

        $selectedProductIds = $this->getSelectedProductIds($product, $requestInfo[ 'bundle_option' ] ?? []);
        $selectedOptions = $requestInfo[ 'options' ];

        /** @var Option $option */
        foreach ($product->getOptions() ?? [] as $option) {
            $allowHideProductIds = $this->helper->getAllowHideProductIds($option);

            if (count($allowHideProductIds) > 0 &&
                count(array_intersect($allowHideProductIds, $selectedProductIds)) === 0) {

                unset($selectedOptions[ $option->getId() ]);
            }
        }

        $requestInfo[ 'options' ] = $selectedOptions;

        return [$productInfo, $requestInfo];
    }

    /**
     * Returns the selected products of the bundle as "<bundle option id>_<product id>".
     *
     * @param Product $product
     * @param mixed $selectedBundleOptions bundle option id => selection id or list of selection ids
     *
     * @return string[]
     */
    private function getSelectedProductIds(Product $product, mixed $selectedBundleOptions): array
    {
        $typeInstance = $product->getTypeInstance();

        if (! is_array($selectedBundleOptions) || count($selectedBundleOptions) === 0 ||
            ! $typeInstance instanceof Type) {

            return [];
        }

        $selectedSelectionIds = [];

        foreach ($selectedBundleOptions as $bundleOptionId => $selectionIds) {
            foreach (is_array($selectionIds) ? $selectionIds : [$selectionIds] as $selectionId) {
                if (is_scalar($selectionId)) {
                    $selectedSelectionIds[ sprintf('%s_%s', $bundleOptionId, $selectionId) ] = true;
                }
            }
        }

        $selectedProductIds = [];

        $selections = $typeInstance->getSelectionsCollection(array_keys($selectedBundleOptions), $product);

        /** @var Product $selection */
        foreach ($selections as $selection) {
            $bundleOptionId = $this->variables->stringValue($selection->getData('option_id'));
            $selectionId = $this->variables->stringValue($selection->getData('selection_id'));

            if (isset($selectedSelectionIds[ sprintf('%s_%s', $bundleOptionId, $selectionId) ])) {
                $selectedProductIds[] =
                    sprintf('%s_%s', $bundleOptionId, $this->variables->stringValue($selection->getId()));
            }
        }

        return $selectedProductIds;
    }

    /**
     * Returns the product to add, null if it does not exist; the cart reports the error then.
     *
     * @param mixed $productInfo
     *
     * @return Product|null
     */
    private function getProduct(mixed $productInfo): ?Product
    {
        if ($productInfo instanceof Product) {
            return $productInfo;
        }

        if (! is_numeric($productInfo)) {
            return null;
        }

        try {
            $product = $this->productRepository->getById(
                $this->variables->intValue($productInfo),
                false,
                $this->variables->intValue($this->storeHelper->getStore()->getId())
            );
        } catch (NoSuchEntityException) {
            return null;
        }

        return $product instanceof Product ? $product : null;
    }
}
