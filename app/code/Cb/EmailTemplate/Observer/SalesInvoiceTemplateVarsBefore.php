<?php
namespace Cb\EmailTemplate\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesInvoiceTemplateVarsBefore implements ObserverInterface
{
    protected $customerRepository;
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper
    ){
        $this->mpHelper = $mpHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transportObject = $observer->getEvent()->getData('transportObject');

        if ($transportObject->getData('order') != null) 
        {
            $order = $transportObject->getData('order');

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

            $transportObject->setData('pickup_service', $pickup_service);
            $transportObject->setData('pickup_shop', $shop_title);
            $transportObject->setData('pickup_location', $pickup_location);
        }
    }
}