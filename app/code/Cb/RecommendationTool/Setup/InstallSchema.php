<?php

namespace Cb\RecommendationTool\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;

		$installer->startSetup();

		/*
		 * Create table 'recommendation_mapping_values'
		 * */
		
		$table = $installer->getConnection()->newTable(
			$installer->getTable('recommendation_mapping_values')
		)->addColumn(
			'id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			[
				'identity' => true,
				'unsigned' => true,
				'nullable' => false,
				'primary' => true,
			],
			'ID'
		)->addColumn(
			'frontend_label',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => false],
			'Frontend Label'
		)->addColumn(
			'backend_label',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => false],
			'Backend Label'
		)->addColumn(
			'attribute_code',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => false],
			'Attribute Code'
		)->setComment(
			'recommendation mapping manager'
		);
		$installer->getConnection()->createTable($table);


		$installer->endSetup();
	}
}