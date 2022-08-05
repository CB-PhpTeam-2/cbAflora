<?php 
namespace Cb\GreenlineApi\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), "1.0.2", "<")) {
        	
        	$greenlineapi_sellercron_allow = 'greenlineapi_sellercron_allow';

	        $tableName = $setup->getTable($greenlineapi_sellercron_allow);
	        $conn = $installer->getConnection();

	        if($conn->isTableExists($tableName) != true){

            	$table = $conn->newTable($installer->getTable($greenlineapi_sellercron_allow))
	            ->addColumn(
	                'id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
	                'ID'
	            )
	            ->addColumn(
	                'seller_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                [],
	                'Seller Id'
	            )
	            ->addColumn(
	                'allow',
	                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
	                null,
	                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
	                'Allow'
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
        }
        
        if (version_compare($context->getVersion(), "1.0.3", "<")) {
        	
        	$greenlineapi_inventory_stock = 'greenlineapi_inventory_stock';

	        $tableName = $setup->getTable($greenlineapi_inventory_stock);
	        $conn = $installer->getConnection();

	        if($conn->isTableExists($tableName) != true){

            	$table = $conn->newTable($installer->getTable($greenlineapi_inventory_stock))
	            ->addColumn(
	                'id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	                null,
	                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
	                'ID'
	            )
	            ->addColumn(
	                'seller_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                255,
	                [],
	                'Seller Id'
	            )
	            ->addColumn(
	                'product_id',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                '',
	                [],
	                'Product Id'
	            )
	            ->addColumn(
	                'qty',
	                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
	                null,
	                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
	                'Quantity'
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
        }

        $installer->endSetup();
    }
}

?>