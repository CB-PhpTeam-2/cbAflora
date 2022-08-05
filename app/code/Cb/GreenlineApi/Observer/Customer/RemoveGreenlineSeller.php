<?php
namespace Cb\GreenlineApi\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;

class RemoveGreenlineSeller implements ObserverInterface
{
    protected $_resource;
    protected $_moduleHelper;
    protected $_objectManager;
	protected $_mpProductCollectionFactory;
	protected $productRepository;
	protected $registry;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
		MpProductCollection $mpProductCollectionFactory,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Framework\Registry $registry
    )
    {
        $this->_resource = $resource;
		$this->_objectManager = $objectManager;
        $this->_moduleHelper = $moduleHelper;
		$this->_mpProductCollectionFactory = $mpProductCollectionFactory;
		$this->productRepository = $productRepository;
		$this->registry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->_moduleHelper->chkIsModuleEnable()){
			$customer = $observer->getEvent()->getCustomer();
			$customerId = $customer->getId();
			
			$connection  = $this->_resource->getConnection();
			$tablePrefix = $this->_moduleHelper->getTablePrefix();
			
			$table1 = $connection->getTableName($tablePrefix.'greenlineapi_seller_history');
			$table2 = $connection->getTableName($tablePrefix.'greenlineapi_sellercron_allow');
			$table3 = $connection->getTableName($tablePrefix.'greenlineapi_import_history');
			$table4 = $connection->getTableName($tablePrefix.'greenlineapi_import_product_history');
			$table5 = $connection->getTableName($tablePrefix.'greenlineapi_inventory_stock');
			$table6 = $connection->getTableName($tablePrefix.'greenlineapi_export_order_history');
			$table7 = $connection->getTableName($tablePrefix.'greenlineapi_seller_credentiial');
		
            
			$collection = $this->_mpProductCollectionFactory->create()
            ->addFieldToFilter('seller_id', $customerId);
			
			$this->registry->register('isSecureArea', true);
			foreach ($collection as $_product){
				$productId = $_product->getMageproductId();
				
				try {
					$product = $this->productRepository->getById($productId);
					$this->productRepository->delete($product);
				} catch (Exception $e) {
					//echo 'Unable to delete product '.$product->getName()."<br>";
					//echo $e->getMessage() . "<br>";
					continue;
				}   
			}

			$query = "DELETE FROM `".$table1."` WHERE `seller_id` = $customerId";
			$connection->query($query);
			
			$query = "DELETE FROM `".$table2."` WHERE `seller_id` = $customerId";
			$connection->query($query);
			
			$query = "DELETE FROM `".$table3."` WHERE `seller_id` = $customerId";
			$connection->query($query);
			
			$query = "DELETE FROM `".$table4."` WHERE `seller_id` = $customerId";
			$connection->query($query);
			
			$query = "DELETE FROM `".$table5."` WHERE `seller_id` = $customerId";
			$connection->query($query);
			
			$query = "DELETE FROM `".$table6."` WHERE `seller_id` = $customerId";
			$connection->query($query);

			$query = "DELETE FROM `".$table7."` WHERE `seller_id` = $customerId";
			$connection->query($query);
        }
        return $this;
    }
}
