<?php
/**
 * Copyright 2022 Aventi Solutions. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aventi\ImageUploader\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '0.0.2', '<=')) {
            $imagesTable = $installer->getTable('aventi_images');
            $connection->addColumn(
                $imagesTable,
                'sku',
                [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => false,
                        'length' => '255',
                        'comment' => 'Image SKU',
                        'after' => 'path'
                    ]
            );
            $connection->addColumn(
                $imagesTable,
                'pos_img',
                [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'length' => '255',
                        'comment' => 'Image order',
                        'after' => 'sku'
                    ]
            );
        }

        $installer->endSetup();
    }
}
