<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Test\Unit\Helper;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Test\Unit\Models;
use Magento\Bundle\Model\Option as BundleOption;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Tests which custom options and option values are available for the selected children of composite products.
 */
class DataTest extends TestCase
{
    use Models;

    /**
     * Returns the helper with an event manager that sets "is_available" of the result to the value, if any.
     *
     * @param mixed $observerResult
     *
     * @return Data
     */
    private function createHelper(mixed $observerResult = null): Data
    {
        $eventManager = $this->createStub(ManagerInterface::class);

        if ($observerResult !== null) {
            $eventManager->method('dispatch')->willReturnCallback(
                function (string $eventName, array $data) use ($observerResult): void {
                    $result = $data[ 'result' ] ?? null;

                    if ($result instanceof DataObject) {
                        $result->setData('is_available', $observerResult);
                    }
                }
            );
        }

        return new Data($eventManager, new Variables());
    }

    /**
     * Returns a product with the id and type.
     *
     * @param int $productId
     * @param string $typeId
     *
     * @return Product
     */
    private function createProduct(int $productId, string $typeId = 'simple'): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn((string)$productId);
        $product->method('getTypeId')->willReturn($typeId);

        return $product;
    }

    /**
     * Returns a new configurable item with children of the products.
     *
     * @param int[] $childProductIds
     *
     * @return Item
     */
    private function createConfigurableItem(array $childProductIds): Item
    {
        $children = [];

        foreach ($childProductIds as $childProductId) {
            $child = $this->createStub(Item::class);
            $child->method('getProduct')->willReturn($this->createProduct($childProductId));

            $children[] = $child;
        }

        $item = $this->createStub(Item::class);
        $item->method('getId')->willReturn(null);
        $item->method('getProduct')->willReturn($this->createProduct(10, Configurable::TYPE_CODE));
        $item->method('getChildren')->willReturn($children);

        return $item;
    }

    /**
     * Returns a custom option with the children.
     *
     * @param mixed $allowHideProductIds
     *
     * @return Option
     * @throws ReflectionException
     */
    private function createOption(mixed $allowHideProductIds): Option
    {
        return $this->createModel(Option::class, 'option_id', [Data::ALLOW_HIDE_PRODUCT_IDS => $allowHideProductIds]);
    }

    /**
     * Returns a bundle option with the id.
     *
     * @param int $bundleOptionId
     *
     * @return BundleOption
     */
    private function createBundleOption(int $bundleOptionId): BundleOption
    {
        $bundleOption = $this->createStub(BundleOption::class);
        $bundleOption->method('getId')->willReturn((string)$bundleOptionId);

        return $bundleOption;
    }

    /**
     * The children are read from lists and from strings separated by comma, without empty values and duplicates.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllowHideProductIds(): void
    {
        $helper = $this->createHelper();

        $this->assertSame(['1', '2'], $helper->getAllowHideProductIds($this->createOption('1, 2,,1')));
        $this->assertSame(['3_5'], $helper->getAllowHideProductIds($this->createOption(['3_5', ''])));
        $this->assertSame([], $helper->getAllowHideProductIds($this->createOption([''])));
        $this->assertSame([], $helper->getAllowHideProductIds($this->createOption(null)));
    }

    /**
     * The selected children of a configurable item are the products of its child items, not the configurable product.
     *
     * @return void
     */
    public function testGetCompositeProductIdsOfConfigurableItem(): void
    {
        $this->assertSame(['11'], $this->createHelper()->getCompositeProductIds($this->createConfigurableItem([11])));
    }

    /**
     * Options are available if they are not restricted or restricted to the selected child.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testIsProductOptionAvailableForItem(): void
    {
        $helper = $this->createHelper();
        $item = $this->createConfigurableItem([11]);

        $this->assertTrue($helper->isProductOptionAvailableForItem($this->createOption(null), $item));
        $this->assertTrue($helper->isProductOptionAvailableForItem($this->createOption('11,12'), $item));
        $this->assertFalse($helper->isProductOptionAvailableForItem($this->createOption(['12']), $item));
        $this->assertTrue($helper->isProductOptionAvailableForItem($this->createOption(['']), $item));
    }

    /**
     * Options of bundle products are available for the products of the bundle options they are restricted to, and
     * observers can change the result to any value, which is returned as boolean.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testIsProductOptionAvailableForBundleOptionSelection(): void
    {
        $bundleOption = $this->createBundleOption(3);
        $product = $this->createProduct(5);

        $helper = $this->createHelper();

        $allowed = $this->createOption('3_5');
        $this->assertTrue($helper->isProductOptionAvailableForBundleOptionSelection($allowed, $bundleOption, $product));

        $other = $this->createOption('4_5');
        $this->assertFalse($helper->isProductOptionAvailableForBundleOptionSelection($other, $bundleOption, $product));

        $this->assertTrue(
            $this->createHelper(1)->isProductOptionAvailableForBundleOptionSelection(
                $this->createOption('4_5'),
                $bundleOption,
                $product
            )
        );
        $this->assertFalse(
            $this->createHelper(0)->isProductOptionAvailableForBundleOptionSelection(
                $this->createOption('3_5'),
                $bundleOption,
                $product
            )
        );
    }

    /**
     * An option belongs to a bundle option if it is restricted to one of its products.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testIsProductOptionValueAvailableForBundleOption(): void
    {
        $helper = $this->createHelper();
        $option = $this->createOption('13_5,3_6');

        foreach ([3 => true, 13 => true, 1 => false] as $bundleOptionId => $available) {
            $bundleOption = $this->createBundleOption($bundleOptionId);

            $isAvailable = $helper->isProductOptionValueAvailableForBundleOption($option, $bundleOption);

            $this->assertSame($available, $isAvailable);
        }
    }
}
