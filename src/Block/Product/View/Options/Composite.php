<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Block\Product\View\Options;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Infrangible\Core\Helper\Registry;
use LogicException;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\View\Element\Template;

/**
 * Passes the children that the custom options and option values of a configurable product are restricted to to the
 * script that shows and hides them.
 */
class Composite extends Template
{
    /** @var Product|null */
    private ?Product $product = null;

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param Json $json
     * @param Variables $variables
     * @param Data $helper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected Registry $registryHelper,
        protected Json $json,
        protected Variables $variables,
        protected Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the product, the current product if none was set.
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        if ($this->product === null) {
            $product = $this->registryHelper->registry('current_product');

            if (! $product instanceof Product) {
                throw new LogicException('Product is not defined');
            }

            $this->product = $product;
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
     * Returns the custom options of the product.
     *
     * @return Option[]
     */
    public function getOptions(): array
    {
        return array_filter($this->getProduct()->getOptions() ?? [], fn($option) => $option instanceof Option);
    }

    /**
     * Returns the configuration of the script as JSON.
     *
     * @return string
     */
    public function getConfig(): string
    {
        return $this->json->encode($this->getConfigData()) ?? '{}';
    }

    /**
     * Returns whether the options are shown with a bundle option.
     *
     * @return bool
     */
    public function isBundleOption(): bool
    {
        return false;
    }

    /**
     * Returns the id of the bundle option that the options are shown with, empty without bundle option.
     *
     * @return string
     */
    protected function getBundleOptionKey(): string
    {
        return '';
    }

    /**
     * Returns the configuration of the script: the children of every restricted option and option value.
     *
     * Children of bundle products are product ids by bundle option id, or the product ids of the bundle option if the
     * options are shown with a bundle option.
     *
     * @return array
     * @phpstan-return array<string, mixed>
     */
    public function getConfigData(): array
    {
        $config = [];

        foreach ($this->getOptions() as $option) {
            $optionId = $this->variables->stringValue($option->getId());

            $allowHideProductIds = $this->getProductIds($option, $this->helper->getAllowHideProductIds($option));

            if ($allowHideProductIds !== null) {
                $config[ 'allowHideProductIds' ][ $optionId ][ 'option' ] = $allowHideProductIds;
            }

            /** @var Option\Value $optionValue */
            foreach ($option->getValues() ?? [] as $optionValue) {
                $allowHideProductIds =
                    $this->getProductIds($option, $this->helper->getAllowHideProductIds($optionValue));

                if ($allowHideProductIds !== null) {
                    $optionValueId = $this->variables->stringValue($optionValue->getId());

                    $config[ 'allowHideProductIds' ][ $optionId ][ 'values' ][ $optionValueId ] = $allowHideProductIds;
                }
            }
        }

        return $config;
    }

    /**
     * Returns the children of the script for the children of an option or option value, null if it has none.
     *
     * @param Option $option
     * @param string[] $allowHideProductIds
     *
     * @return array|null
     * @phpstan-return array<int|string, mixed>|null
     */
    private function getProductIds(Option $option, array $allowHideProductIds): ?array
    {
        if (count($allowHideProductIds) === 0) {
            return null;
        }

        if ($option->getProduct()->getTypeId() !== Type::TYPE_CODE) {
            return $allowHideProductIds;
        }

        $productIds = [];

        foreach ($allowHideProductIds as $allowHideProductId) {
            if (preg_match('/^(\d+)_(\d+)$/', $allowHideProductId, $matches) !== 1) {
                continue;
            }

            if ($this->isBundleOption()) {
                if ($matches[ 1 ] === $this->getBundleOptionKey()) {
                    $productIds[] = $matches[ 2 ];
                }
            } else {
                $productIds[ $matches[ 1 ] ][] = $matches[ 2 ];
            }
        }

        return $productIds;
    }
}
