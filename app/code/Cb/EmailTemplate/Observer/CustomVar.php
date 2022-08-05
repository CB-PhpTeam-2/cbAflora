<?php

namespace Cb\EmailTemplate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomVar implements \Magento\Framework\Event\ObserverInterface
{
    protected $mpHelper;

    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->mpHelper = $mpHelper;
    }

    public function execute(Observer $observer)
    {
        $order = $this->getOrder($observer);
        if (!$order) {
            return;
        }

        $productId = 0;
        foreach ($order->getAllItems() as $_item) {
            $productId = $_item->getProductId();
            break;
        }

        $pickup_location = '';
        if($productId){
            $sellerProduct = $this->mpHelper->getSellerProductDataByProductId($productId);
            $sellerId = 0;
            if($sellerProduct->getSize() > 0){
                $sellerId= $sellerProduct->getFirstItem()->getData('seller_id');
            }
            if($sellerId){
                $sellerObj= $this->mpHelper->getSellerDataBySellerId($sellerId);
                if($sellerObj->getSize() > 0){
                    $pickup_location = $sellerObj->getFirstItem()->getData('origin_address');
                }
            }
        }
        
        $vars = $observer->getData('additional_vars');
        $vars->setData('pickup_location', $pickup_location);
    }

    public function getOrder($observer)
    {
        $vars = $observer->getVars();
        if (isset($vars['order'])) {
            $order = $vars['order'];
            if ($order instanceof \Magento\Sales\Api\Data\OrderInterface) {
                return $order;
            }
        }

        if (isset($vars['invoice'])) {
            $invoice = $vars['invoice'];
            if ($invoice instanceof \Magento\Sales\Api\Data\InvoiceInterface) {
                return $invoice->getOrder();
            }
        }

        if (isset($vars['shipment'])) {
            $shipment = $vars['shipment'];
            if ($shipment instanceof \Magento\Sales\Api\Data\ShipmentInterface) {
                return $shipment->getOrder();
            }
        }

        if (isset($vars['creditmemo'])) {
            $creditMemo = $vars['creditmemo'];
            if ($creditMemo instanceof \Magento\Sales\Api\Data\CreditmemoInterface) {
                return $creditMemo->getOrder();
            }
        }

        return null;
    }
}