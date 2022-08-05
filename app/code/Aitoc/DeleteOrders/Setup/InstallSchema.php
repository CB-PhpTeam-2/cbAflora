<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Aitoc\DeleteOrders\Setup\Operations\CreateOrderArchiveTable;
use Aitoc\DeleteOrders\Setup\Operations\CreateRulesTable;

/**
 * Class InstallSchema
 *
 * @package Aitoc\DeleteOrders\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateOrderArchiveTable
     */
    private $createOrderArchiveTableOperation;

    /**
     * @var CreateRulesTable
     */
    private $createRulesTableOperation;

    public function __construct(CreateOrderArchiveTable $createOrderArchiveTableOperation, CreateRulesTable $createRulesTableOperation)
    {
        $this->createOrderArchiveTableOperation = $createOrderArchiveTableOperation;
        $this->createRulesTableOperation = $createRulesTableOperation;
    }

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createOrderArchiveTableOperation->execute($setup);
        $this->createRulesTableOperation->execute($setup);

        $setup->endSetup();
    }
}
