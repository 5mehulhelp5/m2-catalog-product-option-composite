<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Plugin\Checkout\Model;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Cart
{
    /** @var Stores */
    protected $storeHelper;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var Variables */
    protected $variables;

    public function __construct(
        Stores $storeHelper,
        ProductRepositoryInterface $productRepository,
        Variables $variables
    ) {
        $this->storeHelper = $storeHelper;
        $this->productRepository = $productRepository;
        $this->variables = $variables;
    }

    /**
     * @throws LocalizedException
     * @noinspection PhpUnusedParameterInspection
     */
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null): array
    {
        if (is_array($requestInfo)) {
            if (array_key_exists(
                'options',
                $requestInfo
            )) {
                $product = $this->getProduct($productInfo);

                if ($product->isComposite()) {
                    $selectedOptions = $requestInfo[ 'options' ];

                    /** @var Option $option */
                    foreach ($product->getOptions() as $option) {
                        $allowHideProductIds = $option->getData('allow_hide_product_ids');

                        if (! $this->variables->isEmpty($allowHideProductIds)) {
                            if ($option->getProduct()->getTypeId() === Type::TYPE_CODE) {
                                $selectedBundleOptions = $requestInfo[ 'bundle_option' ];

                                $keep = false;

                                /** @var Type $typeInstance */
                                $typeInstance = $product->getTypeInstance();

                                foreach ($allowHideProductIds as $allowHideProductId) {
                                    if (preg_match(
                                        '/[0-9]+_[0-9]+/',
                                        $allowHideProductId
                                    )) {
                                        [$bundleOptionId, $bundleProductId] = explode(
                                            '_',
                                            $allowHideProductId
                                        );

                                        if (array_key_exists(
                                            $bundleOptionId,
                                            $selectedBundleOptions
                                        )) {
                                            $bundleOptionSelections = $typeInstance->getSelectionsCollection(
                                                [$bundleOptionId],
                                                $product
                                            );

                                            /** @var Product $bundleOptionSelection */
                                            foreach ($bundleOptionSelections as $bundleOptionSelection) {
                                                $bundleOptionSelectionId =
                                                    $bundleOptionSelection->getData('selection_id');

                                                if ($selectedBundleOptions[ $bundleOptionId ] ===
                                                    $bundleOptionSelectionId) {

                                                    $selectionProductId = $bundleOptionSelection->getId();

                                                    if ($selectionProductId == $bundleProductId) {
                                                        $keep = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                if (! $keep) {
                                    unset($selectedOptions[ $option->getId() ]);
                                }
                            }
                        }
                    }

                    $requestInfo[ 'options' ] = $selectedOptions;
                }
            }
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * @param Product|int|string $productInfo
     *
     * @throws LocalizedException
     */
    protected function getProduct($productInfo): Product
    {
        if ($productInfo instanceof Product) {
            $product = $productInfo;
            if (! $product->getId()) {
                throw new LocalizedException(
                    __("The product wasn't found. Verify the product and try again.")
                );
            }
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->storeHelper->getStore()->getId();
            try {
                $product = $this->productRepository->getById(
                    $productInfo,
                    false,
                    $storeId
                );
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(
                    __("The product wasn't found. Verify the product and try again."),
                    $e
                );
            }
        } else {
            throw new LocalizedException(
                __("The product wasn't found. Verify the product and try again.")
            );
        }

        $currentWebsiteId = $this->storeHelper->getStore()->getWebsiteId();

        if (! is_array($product->getWebsiteIds()) || ! in_array(
                $currentWebsiteId,
                $product->getWebsiteIds()
            )) {
            throw new LocalizedException(
                __("The product wasn't found. Verify the product and try again.")
            );
        }

        return $product;
    }
}
