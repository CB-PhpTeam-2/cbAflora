<?php

namespace Cb\OcsImport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{       
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {        
        $installer = $setup;
        $installer->startSetup();
        
        $ocs_import_history = 'ocs_import_history';

        $tableName = $setup->getTable($ocs_import_history);
        $conn = $installer->getConnection();

        if($conn->isTableExists($tableName) != true){

            $table = $conn->newTable($installer->getTable($ocs_import_history))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
			->addColumn(
                'service',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Service'
            )
            ->addColumn(
                'offset',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Offset'
            )
            ->addColumn(
                'last_offset',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Last Offset'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Update At'
            );      
                    
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
