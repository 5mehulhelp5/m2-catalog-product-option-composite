<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogProductOptionComposite\Setup\Patch\Data;

use Infrangible\Core\Helper\Setup;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validator\ValidateException;

/**
 * Adds the attributes of bundle products for the page that edits the custom options of a bundle in the cart.
 *
 * The attributes are added to the default group of every attribute set; they apply to bundle products only.
 * Installations of version 5 already have the attributes, but in no attribute set, and five of them as not visible,
 * which hid them in the admin; the patch makes them visible.
 */
class QuoteItemBundleAttributes implements DataPatchInterface
{
    /**
     * The attributes: code => label, type, input, sort order, default, backend model, source model.
     */
    private const ATTRIBUTES = [
        'quote_item_bundle_headline'           => [
            'Quote Item Bundle Headline', Setup::ATTRIBUTE_TYPE_VARCHAR, 'text', 2000, null, null, null
        ],
        'quote_item_bundle_subline'            => [
            'Quote Item Bundle Subline', Setup::ATTRIBUTE_TYPE_TEXT, 'text', 2010, null, null, null
        ],
        'quote_item_bundle_description'        => [
            'Quote Item Bundle Description', Setup::ATTRIBUTE_TYPE_TEXT, 'textarea', 2020, null, null, null
        ],
        'quote_item_bundle_back_button_text'   => [
            'Quote Item Bundle Back Button Text', Setup::ATTRIBUTE_TYPE_VARCHAR, 'text', 2030, null, null, null
        ],
        'quote_item_bundle_update_button_text' => [
            'Quote Item Bundle Update Button Text', Setup::ATTRIBUTE_TYPE_VARCHAR, 'text', 2040, null, null,
            null
        ],
        'quote_item_bundle_redirect'           => [
            'Quote Item Bundle Redirect', Setup::ATTRIBUTE_TYPE_INT, 'boolean', 2050, '0', null, Boolean::class
        ],
        'quote_item_bundle_redirect_threshold' => [
            'Quote Item Bundle Redirect Threshold', Setup::ATTRIBUTE_TYPE_DECIMAL, 'price', 2060, null,
            Price::class, null
        ]
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Setup $setupHelper
     */
    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected Setup $setupHelper
    ) {
    }

    /**
     * Adds the attributes that do not exist, makes the existing ones visible and adds them to the attribute sets.
     *
     * @return $this
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply(): static
    {
        $eavSetup = $this->setupHelper->getEavSetup($this->moduleDataSetup);

        foreach (self::ATTRIBUTES as $code => [$label, $type, $input, $sortOrder, $default, $backend, $source]) {
            if ($eavSetup->getAttributeId(Product::ENTITY, $code)) {
                $eavSetup->updateAttribute(Product::ENTITY, $code, 'is_visible', 1);
                $this->addToAttributeSets($eavSetup, $code, $sortOrder);

                continue;
            }

            $this->setupHelper->addProductAttribute(
                $eavSetup,
                $code,
                $label,
                ScopedAttributeInterface::SCOPE_STORE,
                $type,
                $input,
                $sortOrder,
                $default,
                true,
                false,
                false,
                false,
                false,
                false,
                true,
                false,
                false,
                $backend,
                $source,
                Type::TYPE_CODE
            );

            $this->addToAttributeSets($eavSetup, $code, $sortOrder);
        }

        return $this;
    }

    /**
     * Adds the attribute to the default group of every product attribute set.
     *
     * @param EavSetup $eavSetup
     * @param string $code
     * @param int $sortOrder
     *
     * @return void
     * @throws LocalizedException
     */
    private function addToAttributeSets(EavSetup $eavSetup, string $code, int $sortOrder): void
    {
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);

        foreach ($eavSetup->getAllAttributeSetIds($entityTypeId) as $attributeSetId) {
            $eavSetup->addAttributeToSet(
                $entityTypeId,
                $attributeSetId,
                $eavSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId),
                $code,
                $sortOrder
            );
        }
    }

    /**
     * Returns the patches that have to run first: none.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Returns other names of the patch: none.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
