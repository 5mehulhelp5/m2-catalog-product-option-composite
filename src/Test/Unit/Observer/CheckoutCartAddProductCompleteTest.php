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
use Infrangible\CatalogProductOptionComposite\Observer\CheckoutCartAddProductComplete;
use Infrangible\CatalogProductOptionComposite\Test\Unit\Models;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Tests the redirect to the page of the custom options of an added bundle item.
 */
class CheckoutCartAddProductCompleteTest extends TestCase
{
    use Models;

    /**
     * Adds a bundle with the redirect settings and options price, returns whether it redirects.
     *
     * @param bool $redirect
     * @param mixed $threshold
     * @param float $optionsPrice
     * @param bool $added
     *
     * @return bool
     * @throws ReflectionException
     */
    private function addBundle(bool $redirect, mixed $threshold, float $optionsPrice, bool $added = true): bool
    {
        $helper = new Data($this->createStub(ManagerInterface::class), new Variables());

        if ($added) {
            $helper->setAddedBundleItem(
                $this->createModel(Item::class, 'item_id', ['item_id' => 9, 'options_price' => $optionsPrice])
            );
        }

        $product = $this->createModel(
            Product::class,
            'entity_id',
            [
                'quote_item_bundle_redirect'           => $redirect ? '1' : '0',
                'quote_item_bundle_redirect_threshold' => $threshold
            ]
        );

        $redirected = false;

        $request = $this->createStub(Http::class);
        $request->method('setParam')->willReturnCallback(
            function (string $name) use (&$redirected, $request): Http {
                $redirected = $name === 'return_url';

                return $request;
            }
        );

        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturn('https://shop.test/catalog_product_option_composite/quote_item/bundle');

        (new CheckoutCartAddProductComplete($helper, $request, $url))->execute(new Observer(['product' => $product]));

        return $redirected;
    }

    /**
     * Bundles redirect if they ask for it and the options price is below the threshold, or without threshold.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testRedirects(): void
    {
        $this->assertTrue($this->addBundle(true, null, 50.));
        $this->assertTrue($this->addBundle(true, '20.0000', 10.));
        $this->assertFalse($this->addBundle(true, '20.0000', 20.));
        $this->assertFalse($this->addBundle(false, null, 0.));
        $this->assertFalse($this->addBundle(true, null, 0., false));
    }
}
