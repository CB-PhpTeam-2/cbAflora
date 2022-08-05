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
        SessionManager $session
    ) {
        $this->ordersFactory = $ordersFactory;
        $this->session = $session;
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
