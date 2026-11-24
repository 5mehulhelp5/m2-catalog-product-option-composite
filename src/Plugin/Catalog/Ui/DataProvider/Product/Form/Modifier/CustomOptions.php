<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionComposite\Helper\Data;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as Modifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

/**
 * Adds the field "Allow & Hide" to the custom options and option values of composite products in the product form:
 * the children of configurable products or the products of bundle options that the option or value is restricted to.
 */
class CustomOptions
{
    /** Name of the field. */
    public const FIELD_ALLOW_HIDE_PRODUCT_IDS_NAME = Data::ALLOW_HIDE_PRODUCT_IDS;

    /**
     * @param Arrays $arrays
     * @param LocatorInterface $locator
     * @param Variables $variables
     */
    public function __construct(
        protected Arrays $arrays,
        protected LocatorInterface $locator,
        protected Variables $variables
    ) {
    }

    /**
     * Adds the field to the options and option values of composite products with children.
     *
     * @param Modifier $subject
     * @param array $meta
     * @phpstan-param array<int|string, mixed> $meta
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     * @noinspection PhpUnusedParameterInspection The signature of the plugin is given.
     */
    public function afterModifyMeta(Modifier $subject, array $meta): array
    {
        $product = $this->locator->getProduct();

        if (! $product instanceof Product || ! $product->getId() || ! $product->isComposite()) {
            return $meta;
        }

        $productOptions = $this->getProductOptions($product);

        if (count($productOptions) === 0) {
            return $meta;
        }

        $fieldConfig = $this->getProductIdsFieldConfig(
            static::FIELD_ALLOW_HIDE_PRODUCT_IDS_NAME,
            __('Allow & Hide')->render(),
            $productOptions,
            200
        );

        $optionPath = [
            Modifier::GROUP_CUSTOM_OPTIONS_NAME,
            'children',
            Modifier::GRID_OPTIONS_NAME,
            'children',
            'record',
            'children',
            Modifier::CONTAINER_OPTION,
            'children'
        ];

        $meta = $this->arrays->addDeepValue(
            $meta,
            array_merge(
                $optionPath,
                [Modifier::CONTAINER_COMMON_NAME, 'children', static::FIELD_ALLOW_HIDE_PRODUCT_IDS_NAME]
            ),
            $fieldConfig
        );

        return $this->arrays->addDeepValue(
            $meta,
            array_merge(
                $optionPath,
                [
                    Modifier::GRID_TYPE_SELECT_NAME,
                    'children',
                    'record',
                    'children',
                    static::FIELD_ALLOW_HIDE_PRODUCT_IDS_NAME
                ]
            ),
            $fieldConfig
        );
    }

    /**
     * Returns the choices of the field.
     *
     * These are the children of a configurable product, or the products of the options of a bundle product, grouped
     * by option.
     *
     * @param Product $product
     *
     * @return array
     * @phpstan-return list<array<string, mixed>>
     */
    protected function getProductOptions(Product $product): array
    {
        $productOptions = [];

        $typeInstance = $product->getTypeInstance();

        if ($typeInstance instanceof Configurable) {
            /** @var Product $childProduct */
            foreach ($typeInstance->getUsedProducts($product) as $childProduct) {
                $productOptions[] = [
                    'value' => $this->variables->stringValue($childProduct->getId()),
                    'label' => sprintf('%s [%s]', $childProduct->getName(), $childProduct->getSku())
                ];
            }
        } elseif ($typeInstance instanceof Type) {
            /** @var Option $option */
            foreach ($typeInstance->getOptionsCollection($product) as $option) {
                $optionProductOptions = [];

                /** @var Product $optionSelection */
                foreach ($typeInstance->getSelectionsCollection([$option->getId()], $product) as $optionSelection) {
                    $optionProductOptions[] = [
                        'value' => sprintf(
                            '%s_%s',
                            $this->variables->stringValue($option->getId()),
                            $this->variables->stringValue($optionSelection->getId())
                        ),
                        'label' => sprintf(
                            '%s - %s [%s]',
                            $option->getTitle(),
                            $optionSelection->getName(),
                            $optionSelection->getSku()
                        )
                    ];
                }

                $productOptions[] = [
                    'value'    => $option->getTitle(),
                    'label'    => $option->getTitle(),
                    'optgroup' => $optionProductOptions
                ];
            }
        }

        return $productOptions;
    }

    /**
     * Returns the configuration of the field, a multiple select with search.
     *
     * @param string $scopeName
     * @param string $label
     * @param array $productOptions
     * @param int $sortOrder
     * @phpstan-param list<array<string, mixed>> $productOptions
     *
     * @return array
     * @phpstan-return array<string, mixed>
     */
    protected function getProductIdsFieldConfig(
        string $scopeName,
        string $label,
        array $productOptions,
        int $sortOrder
    ): array {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType'         => Text::NAME,
                        'formElement'      => Select::NAME,
                        'componentType'    => Field::NAME,
                        'component'        => 'Magento_Ui/js/form/element/ui-select',
                        'elementTmpl'      => 'ui/grid/filters/elements/ui-select',
                        'disableLabel'     => true,
                        'filterOptions'    => true,
                        'multiple'         => true,
                        'showCheckbox'     => true,
                        'levelsVisibility' => 2,
                        'dataScope'        => $scopeName,
                        'label'            => $label,
                        'options'          => $productOptions,
                        'sortOrder'        => $sortOrder
                    ]
                ]
            ]
        ];
    }
}
