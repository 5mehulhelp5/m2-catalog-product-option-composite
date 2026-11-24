<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Test\Unit\Plugin\Checkout\Model;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Plugin\Checkout\Model\Cart;
use Infrangible\CatalogProductOptionComposite\Test\Unit\Models;
use Infrangible\Core\Helper\Stores;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Event\ManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Tests which custom option values of a bundle product are kept when it is added to the cart.
 */
class CartTest extends TestCase
{
    use Models;

    /**
     * Returns a custom option with the id and children.
     *
     * @param int $optionId
     * @param string|null $allowHideProductIds
     *
     * @return Option
     * @throws ReflectionException
     */
    private function createOption(int $optionId, ?string $allowHideProductIds): Option
    {
        return $this->createModel(
            Option::class,
            'option_id',
            ['option_id' => (string)$optionId, Data::ALLOW_HIDE_PRODUCT_IDS => $allowHideProductIds]
        );
    }

    /**
     * Returns a selection of a bundle option.
     *
     * @param int $bundleOptionId
     * @param int $selectionId
     * @param int $productId
     *
     * @return Product
     * @throws ReflectionException
     */
    private function createSelection(int $bundleOptionId, int $selectionId, int $productId): Product
    {
        return $this->createModel(
            Product::class,
            'entity_id',
            ['entity_id' => (string)$productId, 'option_id' => $bundleOptionId, 'selection_id' => $selectionId]
        );
    }

    /**
     * Returns the bundle product with options restricted to product 5 and 6 of bundle option 3 and an unrestricted
     * option; bundle option 3 has the selections 7 (product 5) and 8 (product 6).
     *
     * @return Product
     * @throws ReflectionException
     */
    private function createBundleProduct(): Product
    {
        $typeInstance = $this->createStub(Type::class);
        $typeInstance->method('getSelectionsCollection')->willReturn(
            [$this->createSelection(3, 7, 5), $this->createSelection(3, 8, 6)]
        );

        $product = $this->createStub(Product::class);
        $product->method('getTypeId')->willReturn(Type::TYPE_CODE);
        $product->method('getTypeInstance')->willReturn($typeInstance);
        $product->method('getOptions')->willReturn(
            [$this->createOption(1, '3_5'), $this->createOption(2, '3_6'), $this->createOption(4, null)]
        );

        return $product;
    }

    /**
     * Returns the plugin.
     *
     * @return Cart
     */
    private function createPlugin(): Cart
    {
        return new Cart(
            $this->createStub(Stores::class),
            $this->createStub(ProductRepositoryInterface::class),
            new Variables(),
            new Data($this->createStub(ManagerInterface::class), new Variables())
        );
    }

    /**
     * Adds the product with values for the options 1, 2 and 4, returns the option values that the plugin keeps.
     *
     * @param Cart $plugin
     * @param Product $product
     * @param array $request
     * @phpstan-param array<string, mixed> $request
     *
     * @return mixed
     * @noinspection PhpDeprecationInspection The plugin is for the deprecated cart model.
     */
    private function add(Cart $plugin, Product $product, array $request): mixed
    {
        $request[ 'options' ] = [1 => 'a', 2 => 'b', 4 => 'c'];

        $result = $plugin->beforeAddProduct($this->createStub(\Magento\Checkout\Model\Cart::class), $product, $request);

        $this->assertIsArray($result[ 1 ]);

        return $result[ 1 ][ 'options' ] ?? null;
    }

    /**
     * Values of options restricted to products that were not selected are removed, also with several selections of
     * a bundle option and without selections.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testRemovesValuesOfOptionsOfUnselectedProducts(): void
    {
        $plugin = $this->createPlugin();
        $product = $this->createBundleProduct();

        $this->assertSame([1 => 'a', 4 => 'c'], $this->add($plugin, $product, ['bundle_option' => [3 => '7']]));
        $this->assertSame([2 => 'b', 4 => 'c'], $this->add($plugin, $product, ['bundle_option' => [3 => ['8']]]));
        $this->assertSame([4 => 'c'], $this->add($plugin, $product, []));
    }

    /**
     * Requests without options and products that are no bundles are not changed.
     *
     * @return void
     * @throws ReflectionException
     * @noinspection PhpDeprecationInspection The plugin is for the deprecated cart model.
     */
    public function testKeepsOtherRequests(): void
    {
        $plugin = $this->createPlugin();
        $cart = $this->createStub(\Magento\Checkout\Model\Cart::class);

        $product = $this->createBundleProduct();

        $this->assertSame([5, null], $plugin->beforeAddProduct($cart, 5));
        $this->assertSame([$product, ['qty' => 1]], $plugin->beforeAddProduct($cart, $product, ['qty' => 1]));
    }
}
