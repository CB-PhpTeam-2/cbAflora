<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), "3.0.1", "<")) {
            /**
             * Update tables 'marketplace_sub_accounts'
             */
            /*$setup->getConnection()->changeColumn(
                $setup->getTable('marketplace_sub_accounts'),
                'permission_type',
                'permission_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64m',
                    'nullable' => false,
                    'comment' => 'Account Permission Types'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('marketplace_userdata'),
                'sub_account_permission',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64m',
                    'nullable' => false,
                    'comment' => 'Permissions to Sub Seller Account'
                ]
            );*/
            $setup->getConnection()->addColumn(
                $setup->getTable('marketplace_userdata'),
                'sub_account_permission',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Permissions to Sub Seller Account'
                ]
            );
        }
        $setup->endSetup();
    }
}
