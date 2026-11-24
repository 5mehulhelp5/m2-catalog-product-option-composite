<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Test\Unit\Observer;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\CatalogProductOptionComposite\Observer\ModelSaveBefore;
use Infrangible\CatalogProductOptionComposite\Test\Unit\Models;
use Infrangible\Core\Helper\Product as ProductHelper;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Tests the validation of the children of saved custom options.
 */
class ModelSaveBeforeTest extends TestCase
{
    use Models;

    /**
     * Returns a custom option of a product of the type with the children.
     *
     * @param string $typeId
     * @param mixed $allowHideProductIds
     *
     * @return Option
     * @throws ReflectionException
     */
    private function createOption(string $typeId, mixed $allowHideProductIds): Option
    {
        $product = $this->createStub(Product::class);
        $product->method('getTypeId')->willReturn($typeId);

        $option = $this->createModel(
            Option::class,
            'option_id',
            ['title' => 'Engraving', Data::ALLOW_HIDE_PRODUCT_IDS => $allowHideProductIds]
        );
        $option->setProduct($product);

        return $option;
    }

    /**
     * Saves the option with the observer.
     *
     * @param Option $option
     *
     * @return void
     * @throws LocalizedException
     */
    private function save(Option $option): void
    {
        $observer = new ModelSaveBefore(
            new Data($this->createStub(ManagerInterface::class), new Variables()),
            $this->createStub(ProductHelper::class),
            new Variables()
        );

        $observer->execute(new Observer(['object' => $option]));
    }

    /**
     * Valid children are stored separated by comma, no children as null.
     *
     * @return void
     * @throws LocalizedException
     * @throws ReflectionException
     */
    public function testStoresValidChildren(): void
    {
        $option = $this->createOption(Type::TYPE_CODE, ['3_5', '3_6']);
        $this->save($option);
        $this->assertSame('3_5,3_6', $option->getData(Data::ALLOW_HIDE_PRODUCT_IDS));

        $option = $this->createOption('configurable', '11,12');
        $this->save($option);
        $this->assertSame('11,12', $option->getData(Data::ALLOW_HIDE_PRODUCT_IDS));

        $option = $this->createOption('configurable', ['']);
        $this->save($option);
        $this->assertNull($option->getData(Data::ALLOW_HIDE_PRODUCT_IDS));
    }

    /**
     * Children of bundle products need the bundle option, product ids of other products must be numbers.
     *
     * @return void
     * @throws LocalizedException
     * @throws ReflectionException
     */
    public function testRejectsInvalidChildren(): void
    {
        $this->expectException(LocalizedException::class);

        $this->save($this->createOption(Type::TYPE_CODE, ['5']));
    }

    /**
     * Product ids with other characters are rejected for products that are no bundles.
     *
     * @return void
     * @throws LocalizedException
     * @throws ReflectionException
     */
    public function testRejectsInvalidProductIds(): void
    {
        $this->expectException(LocalizedException::class);

        $this->save($this->createOption('configurable', ['3_5']));
    }
}
