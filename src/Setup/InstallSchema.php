<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionComposite\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @throws \Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $tableName = $connection->getTableName('catalog_product_option_type_value');

        if (! $connection->tableColumnExists(
            $tableName,
            'allow_hide_product_ids'
        )) {
            $connection->addColumn(
                $tableName,
                'allow_product_ids',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 10000,
                    'nullable' => true,
                    'comment'  => 'Allow Product Ids'
                ]
            );

            $connection->addColumn(
                $tableName,
                'allow_hide_product_ids',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 10000,
                    'nullable' => true,
                    'comment'  => 'Allow & Hide Product Ids'
                ]
            );

            $connection->addColumn(
                $tableName,
                'prohibit_product_ids',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 10000,
                    'nullable' => true,
                    'comment'  => 'Prohibit Product Ids'
                ]
            );

            $connection->addColumn(
                $tableName,
                'prohibit_hide_product_ids',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 10000,
                    'nullable' => true,
                    'comment'  => 'Prohibit & Hide Product Ids'
                ]
            );
        }

        $setup->endSetup();
    }
}
