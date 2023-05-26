<?php

namespace Aventi\ImageUploader\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $imagesTableName = $setup->getTable('aventi_images');
        if (!$setup->getConnection()->isTableExists($imagesTableName)) {
            $imagesTable = $setup->getConnection()->newTable($imagesTableName)
                ->addColumn(
                    'image_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        Table::OPTION_IDENTITY => true,
                        Table::OPTION_PRIMARY => true,
                        Table::OPTION_UNSIGNED => true,
                        Table::OPTION_NULLABLE => false,
                    ],
                    'Image Id'
                )
                ->addColumn(
                    'path',
                    Table::TYPE_TEXT,
                    255,
                    [
                        Table::OPTION_NULLABLE => false
                    ],
                    'Image Path'
                )
                ->addColumn(
                    'details',
                    Table::TYPE_TEXT,
                    512,
                    [
                        Table::OPTION_NULLABLE => false
                    ],
                    'Image Details'
                );

            $setup->getConnection()->createTable($imagesTable);
        }

        $setup->endSetup();
    }
}
