<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use FeWeDev\Base\Arrays;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CustomOptions
{
    public const FIELD_ALLOW_HIDE_PRODUCT_IDS_NAME = 'allow_hide_product_ids';

    /** @var Arrays */
    protected $arrays;

    /** @var LocatorInterface */
    protected $locator;

    public function __construct(Arrays $arrays, LocatorInterface $locator)
    {
        $this->arrays = $arrays;
        $this->locator = $locator;
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterModifyMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject,
        array $meta
    ): array {
        /** @var Product $product */
        $product = $this->locator->getProduct();

        if (! $product->getId() || ! $product->isComposite()) {
            return $meta;
        }

        $productOptions = [];

        $typeInstance = $product->getTypeInstance();

        if ($typeInstance instanceof Configurable) {
            $childProducts = $typeInstance->getUsedProducts($product);

            /** @var Product $childProduct */
            foreach ($childProducts as $childProduct) {
                $productOptions[] = [
                    'value' => $childProduct->getId(),
                    'label' => sprintf(
                        '%s [%s]',
                        $childProduct->getName(),
                        $childProduct->getSku()
                    )
                ];
            }
        } elseif ($typeInstance instanceof Type) {
            $options = $typeInstance->getOptionsCollection($product);

            /** @var Option $option */
            foreach ($options as $option) {
                $optionSelections = $typeInstance->getSelectionsCollection(
                    [$option->getId()],
                    $product
                );

                $optionProductOptions = [];

                /** @var Product $optionSelection */
                foreach ($optionSelections as $optionSelection) {
                    $optionProductOptions[] = [
                        'value' => $optionSelection->getId(),
                        'label' => sprintf(
                            '%s [%s]',
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

        if (count($productOptions) === 0) {
            return $meta;
        }

        return $this->arrays->addDeepValue(
            $meta,
            [
                \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions::GROUP_CUSTOM_OPTIONS_NAME,
                'children',
                \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions::GRID_OPTIONS_NAME,
                'children',
                'record',
                'children',
                \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions::CONTAINER_OPTION,
                'children',
                \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions::GRID_TYPE_SELECT_NAME,
                'children',
                'record',
                'children',
                static::FIELD_ALLOW_HIDE_PRODUCT_IDS_NAME
            ],
            $this->getProductIdsFieldConfig(
                static::FIELD_ALLOW_HIDE_PRODUCT_IDS_NAME,
                __('Allow & Hide')->render(),
                $productOptions,
                200
            )
        );
    }

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
