<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Webkul\Marketplace\Model\SellerFactory as MpSellerFactory;

class CreateDefaultSeller implements DataPatchInterface
{
    private $_date;
	private $storeManager;
    private $customerFactory;
	private $mpSellerFactory;

    public function __construct(
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		MpSellerFactory $mpSellerFactory
    ){
		$this->_date = $date;
		$this->storeManager = $storeManager;
		$this->customerFactory = $customerFactory;
		$this->mpSellerFactory = $mpSellerFactory;
	}

    /**
     * {@inheritdoc}
     */
    public function apply()
    {	
		 $data =[
				 'customer' =>[
					 'firstname' => 'Aflora',
					 'email' => 'support@aflora.ca',
					 'lastname' => 'Default',
					 'password' => 'admin@123',
					 'prefix' => '',
					 'suffix' => ''
					 ]
				];
         $store = $this->storeManager->getStore();
		 $storeId = $store->getStoreId();
		 $websiteId = $this->storeManager->getStore()->getWebsiteId();
		 $customer = $this->customerFactory->create();
		 $customer->setWebsiteId($websiteId);
		 $customer->loadByEmail($data['customer']['email']);// load customer by email address
		 if(!$customer->getId()){
			 //For guest customer create new cusotmer
			 $customer->setWebsiteId($websiteId)
			 ->setStore($store)
			 ->setFirstname($data['customer']['firstname'])
			 ->setLastname($data['customer']['lastname'])
			 ->setPrefix($data['customer']['prefix'])
			 ->setEmail($data['customer']['email'])
			 ->setPassword($data['customer']['password']);
			 $customer->save();
		 }
		 
		 if($customer->getId()){
			$customerid = $customer->getId();
			$model = $this->mpSellerFactory->create();
			$model->setData('is_seller', 1);
			$model->setData('shop_url', 'aflora-shop');
			$model->setData('seller_id', $customerid);
			$model->setData('store_id', 0);
			$model->setCreatedAt($this->_date->gmtDate());
			$model->setUpdatedAt($this->_date->gmtDate());
			$model->save();
		 }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
