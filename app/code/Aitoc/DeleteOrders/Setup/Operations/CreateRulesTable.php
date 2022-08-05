<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Setup\Operations;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Aitoc\DeleteOrders\Model\ResourceModel\Rules as RulesModel;
use Aitoc\DeleteOrders\Api\Data\RulesInterface;

class CreateRulesTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createRulesTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function createRulesTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(RulesModel::TABLE_NAME);
        $rulesTable = $setup->getConnection()->newTable(
            $tableName
        )->addColumn(
            RulesInterface::ENTITY_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], //TODO: change to constants in 2.3.0
            'Entity Id'
        )->addColumn(
            RulesInterface::TITLE,
            Table::TYPE_TEXT,
            255,
            [], //TODO: change to constants in 2.3.0
            'Title'
        )->addColumn(
            RulesInterface::SCOPE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false], //TODO: change to constants in 2.3.0
            'Scope'
        )->addColumn(
            RulesInterface::ORDER_STATUSES,
            Table::TYPE_TEXT,
            255,
            [], //TODO: change to constants in 2.3.0
            'Order statuses'
        )->addColumn(
            RulesInterface::ACTION,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false], //TODO: change to constants in 2.3.0
            'Action'
        )->addColumn(
            RulesInterface::TIME,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Time'
        )->addColumn(
            RulesInterface::IS_ACTIVE,
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 1],
            'Is active'
        );

        $setup->getConnection()->createTable($rulesTable);
    }
}
