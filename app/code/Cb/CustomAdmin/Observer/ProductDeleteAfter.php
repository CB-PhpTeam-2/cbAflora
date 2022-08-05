<?php

namespace Cb\CustomAdmin\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductDeleteAfter implements ObserverInterface
{    
   /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Cb\GreenlineApi\Model\ProductHistoryFactory $productHistoryFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_productHistoryFactory = $productHistoryFactory;
        $this->_mpHelper = $mpHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$_product = $observer->getEvent()->getProduct();
        //$productId = $_product->getId();
        $productId = $observer->getProduct()->getId();

        $sellerId = $this->_mpHelper->getSellerIdByProductId($productId);

        $historyFactory = $this->_productHistoryFactory->create()->getCollection()->addFieldToFilter('product_id',$productId)->addFieldToFilter('seller_id',$sellerId);

        if($historyFactory->getSize() > 0){
            $id = $historyFactory->getFirstItem()->getId();
            $collection = $this->_productHistoryFactory->create()->load($id);
            $collection->delete();

        }

        return $this;
    }

}


?>