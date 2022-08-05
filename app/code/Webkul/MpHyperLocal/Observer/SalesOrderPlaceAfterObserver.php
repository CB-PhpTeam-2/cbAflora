<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Webkul\Marketplace\Model\OrdersFactory;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var Webkul\Marketplace\Model\OrdersFactory
     */
    private $ordersFactory;

    /**
     * @var SessionManager
     */
    private $session;

    /**
     * @param OrdersFactory $objectManager
     * @param SessionManager $session
     */
    public function __construct(
        OrdersFactory $ordersFactory,
        SessionManager $session,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\MpHyperLocal\Helper\Data $mpHypHelper
    ) {
        $this->ordersFactory = $ordersFactory;
        $this->session = $session;
        $this->mpHypHelper = $mpHypHelper;
        $this->_resource = $resource;
    }

    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance Order */
        $order = $observer->getOrder();
        $lastOrderId = $observer->getOrder()->getId();
        $shippingmethod = $order->getShippingMethod();

        $savedAddress = $this->mpHypHelper->getSavedAddress();
        $service_type = 1;
        if(sizeof($savedAddress) > 0){
            $service_type = $savedAddress['service_type'];
        }
        $order->setServiceType($service_type);
        $order->save();

        $OrderId = $order->getId();
        $connection  = $this->_resource->getConnection();
        $query= "UPDATE `sales_order_grid` SET `service_type` = '$service_type' WHERE `entity_id` = '$OrderId'";
        $connection->query($query);


        if (strpos($shippingmethod, 'mplocalship') !== false) {
            $shippingAll = $this->session->getShippingInfo();
            foreach ($shippingAll['mplocalship'] as $shipdata) {
                $collection = $this->ordersFactory->create()->getCollection()
                                ->addFieldToFilter('order_id', ['eq' => $lastOrderId])
                                ->addFieldToFilter('seller_id', ['eq' => $shipdata['seller_id']])
                                ->setPageSize(1)->getFirstItem();
                if ($collection->getEntityId()) {
                    $collection->setCarrierName($shipdata['submethod'][0]['method']);
                    $collection->setShippingCharges($shipdata['submethod'][0]['cost']);
                    $this->saveShipping($collection);
                }
            }
            $this->session->unsetShippingInfo();
        }
    }

    /**
     * saveShipping
     * @param $object
     * @return void
     */
    private function saveShipping($object)
    {
        $object->save();
    }
}
