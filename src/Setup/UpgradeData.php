<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Setup;

use Infrangible\Core\Helper\Setup;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Validator\ValidateException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var Setup */
    protected $setupHelper;

    public function __construct(Setup $setupHelper)
    {
        $this->setupHelper = $setupHelper;
    }

    /**
     * @throws ValidateException
     * @throws LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->setupHelper->getEavSetup($setup);

        if (version_compare(
            $context->getVersion(),
            '2.1.0',
            '<'
        )) {
            $this->setupHelper->addProductAttribute(
                $eavSetup,
                'quote_item_bundle_headline',
                'Quote Item Bundle Headline',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_VARCHAR,
                'text',
                2000,
                null,
                true,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                null,
                null,
                Type::TYPE_CODE
            );

            $this->setupHelper->addProductAttribute(
                $eavSetup,
                'quote_item_bundle_subline',
                'Quote Item Bundle Subline',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_TEXT,
                'text',
                2010,
                null,
                true,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                null,
                null,
                Type::TYPE_CODE
            );

            $this->setupHelper->addProductAttribute(
                $eavSetup,
                'quote_item_bundle_description',
                'Quote Item Bundle Description',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_TEXT,
                'textarea',
                2020,
                null,
                true,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                null,
                null,
                Type::TYPE_CODE
            );

            $this->setupHelper->addProductAttribute(
                $eavSetup,
                'quote_item_bundle_back_button_text',
                'Quote Item Bundle Back Button Text',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_VARCHAR,
                'text',
                2030,
                null,
                true,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                null,
                null,
                Type::TYPE_CODE
            );

            $this->setupHelper->addProductAttribute(
                $eavSetup,
                'quote_item_bundle_update_button_text',
                'Quote Item Bundle Update Button Text',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_VARCHAR,
                'text',
                2040,
                null,
                true,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                null,
                null,
                Type::TYPE_CODE
            );
        }

        if (version_compare(
            $context->getVersion(),
            '4.0.0',
            '<'
        )) {
            $this->setupHelper->addProductAttribute(
                $eavSetup,
                'quote_item_bundle_redirect',
                'Quote Item Bundle Redirect',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_INT,
                'boolean',
                2050,
                '0',
                true,
                false,
                false,
                false,
                false,
                false,
                true,
                false,
                false,
                null,
                Boolean::class,
                Type::TYPE_CODE
            );

            $this->setupHelper->addProductAttribute(
                $eavSetup,
                'quote_item_bundle_redirect_threshold',
                'Quote Item Bundle Redirect Threshold',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_DECIMAL,
                'price',
                2060,
                null,
                true,
                false,
                false,
                false,
                false,
                false,
                true,
                false,
                false,
                Price::class,
                null,
                Type::TYPE_CODE
            );
        }
    }
}
