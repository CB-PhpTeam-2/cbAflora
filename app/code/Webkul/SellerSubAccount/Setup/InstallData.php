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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @param ControllersRepository $controllersRepository
     * @param GroupFactory          $groupFactory
     * @param CollectionFactory     $groupCollectionFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository,
        GroupFactory $groupFactory,
        CollectionFactory $groupCollectionFactory
    ) {
        $this->controllersRepository = $controllersRepository;
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * insert sellersubaccount controller's data
         */
        $data = [];

        if (!count($this->controllersRepository->getByPath('sellersubaccount/account/'))) {
            $data[] = [
                'module_name' => 'Webkul_SellerSubAccount',
                'controller_path' => 'sellersubaccount/account/',
                'label' => 'Manage Accounts',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $setup->getConnection()
            ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);

        $groupCollection = $this->groupCollectionFactory->create()
            ->addFieldToFilter('customer_group_code', 'Sub Account');
        if (!$groupCollection->getSize()) {
            // Create the new group
            /** @var \Magento\Customer\Model\Group $group */
            $group = $this->groupFactory->create();
            $group->setCode('Sub Account')
                  ->setTaxClassId(3)
                  ->save();
        }

        $setup->endSetup();
    }
}
