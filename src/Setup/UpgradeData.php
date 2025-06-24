<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Setup;

use Infrangible\Core\Helper\Setup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
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
        if (version_compare(
            $context->getVersion(),
            '2.1.0',
            '<'
        )) {
            $eavSetup = $this->setupHelper->getEavSetup($setup);

            $this->setupHelper->addEavEntityAttribute(
                $eavSetup,
                Product::ENTITY,
                'quote_item_bundle_headline',
                'Quote Item Bundle Headline',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_VARCHAR,
                'text',
                2000,
                null,
                true
            );

            $this->setupHelper->addEavEntityAttribute(
                $eavSetup,
                Product::ENTITY,
                'quote_item_bundle_subline',
                'Quote Item Bundle Subline',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_TEXT,
                'text',
                2010,
                null,
                true
            );

            $this->setupHelper->addEavEntityAttribute(
                $eavSetup,
                Product::ENTITY,
                'quote_item_bundle_description',
                'Quote Item Bundle Description',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_TEXT,
                'textarea',
                2020,
                null,
                true
            );

            $this->setupHelper->addEavEntityAttribute(
                $eavSetup,
                Product::ENTITY,
                'quote_item_bundle_back_button_text',
                'Quote Item Bundle Back Button Text',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_VARCHAR,
                'text',
                2030,
                null,
                true
            );

            $this->setupHelper->addEavEntityAttribute(
                $eavSetup,
                Product::ENTITY,
                'quote_item_bundle_update_button_text',
                'Quote Item Bundle Update Button Text',
                ScopedAttributeInterface::SCOPE_STORE,
                Setup::ATTRIBUTE_TYPE_VARCHAR,
                'text',
                2040,
                null,
                true
            );
        }
    }
}
