<?php

namespace Webkul\MpTwilioSMSNotification\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;

class InstallData implements InstallDataInterface
{

    private $customerSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'mobile_number', [
            'type' => 'varchar', // type of attribute
            'label' => 'Mobile Number',
            'input' => 'text', // input type
            'source' => '',
            'required' => false, // if you want to required need to set true
            'visible' => true,
            'position' => 75, // position of attribute
            'system' => false,
            'backend' => ''
        ]);
        
        /* Specify which place you want to display customer attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'mobile_number')
        ->addData(['used_in_forms' => [
                'adminhtml_customer',
                'adminhtml_checkout',
                'customer_account_create',
                'customer_account_edit'
            ]
        ]);
        $attribute->save();
    }
}