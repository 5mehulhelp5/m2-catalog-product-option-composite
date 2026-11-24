<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Traits;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Render;

/**
 * The price box of custom options of a product: the price of the selected options and the configuration of the
 * price box script.
 */
trait OptionsPriceBox
{
    /**
     * Returns the product of the options.
     *
     * @return Product
     */
    abstract public function getProduct(): Product;

    /**
     * Returns the price of the selected options.
     *
     * @return float
     */
    abstract public function getOptionsPrice(): float;

    /**
     * Returns the data as JSON.
     *
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     *
     * @return string
     */
    abstract protected function encodeJson(array $data): string;

    /**
     * Renders the price of the selected options.
     *
     * @param array $arguments
     * @phpstan-param array<string, mixed> $arguments
     *
     * @return string
     */
    public function renderAmount(array $arguments): string
    {
        $customOptionPrice = $this->getProduct()->getPriceInfo()->getPrice(CustomOptionPrice::PRICE_CODE);

        try {
            $renderer = $this->getLayout()->getBlock('product.price.render.default');
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception);

            return '';
        }

        if (! $customOptionPrice instanceof CustomOptionPrice || ! $renderer instanceof Render) {
            return '';
        }

        $amount = $customOptionPrice->getCustomAmount(
            $this->getOptionsPrice(),
            null,
            [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true]
        );

        return $amount instanceof AmountInterface ?
            $renderer->renderAmount($amount, $customOptionPrice, $this->getProduct(), $arguments) : '';
    }

    /**
     * Returns the price format of the store as JSON.
     *
     * @return string
     */
    public function getPriceFormatJson(): string
    {
        return $this->encodeJson($this->localeFormat->getPriceFormat());
    }

    /**
     * Returns the start prices of the price box as JSON.
     *
     * @return string
     */
    public function getPricesJson(): string
    {
        return $this->encodeJson(
            [
                'baseOldPrice' => ['amount' => 0],
                'oldPrice'     => ['amount' => 0],
                'basePrice'    => ['amount' => 0],
                'finalPrice'   => ['amount' => 0]
            ]
        );
    }
}
