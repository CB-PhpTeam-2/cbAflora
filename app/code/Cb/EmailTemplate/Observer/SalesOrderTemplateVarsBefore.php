<?php
namespace Cb\EmailTemplate\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderTemplateVarsBefore implements ObserverInterface
{
    protected $customerRepository;
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ){
        $this->customerRepository = $customerRepository;
        $this->mpHelper = $mpHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getEvent()->getTransport();
        if ($transport->getOrder() != null) 
        {
            /*$customer = $this->customerRepository->getById($transport->getOrder()->getCustomerId());
            if ($customer->getCustomAttribute('username')) 
            {
                $transport['username'] = $customer->getCustomAttribute('username')->getValue();
            }*/
            $order = $transport->getOrder();

            $shop_title = '';
            $pickup_location = '';
            $pickup_service = '';
            if($order->getServiceType() == 2){
                $pickup_service = $order->getServiceType();
                $productId = 0;
                foreach ($order->getAllItems() as $_item) {
                    $productId = $_item->getProductId();
                    break;
                }

                if($productId){
                    $sellerProduct = $this->mpHelper->getSellerProductDataByProductId($productId);
                    $sellerId = 0;
                    if($sellerProduct->getSize() > 0){
                        $sellerId= $sellerProduct->getFirstItem()->getData('seller_id');
                    }
                    if($sellerId){
                        $sellerObj= $this->mpHelper->getSellerDataBySellerId($sellerId);
                        if($sellerObj->getSize() > 0){
                            $shop_title = $sellerObj->getFirstItem()->getData('shop_title');
                            $pickup_location = $sellerObj->getFirstItem()->getData('origin_address');
                        }
                    }
                }
            }
            
            $transport['pickup_service'] = $pickup_service;
            $transport['pickup_shop'] = $shop_title;
            $transport['pickup_location'] = $pickup_location;
        }
    }
}