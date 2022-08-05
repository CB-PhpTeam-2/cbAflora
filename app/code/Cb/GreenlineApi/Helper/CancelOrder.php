<?php

namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

class CancelOrder extends AbstractHelper {

    protected $_objectManager;
    protected $_invoiceSender;
    protected $_orderRepository;
    protected $orderinfoHelper;
    protected $helper;
    protected $saleslistFactory;
    protected $mpOrdersModel;

    public function __construct(
    \Magento\Framework\View\Element\Context $context,
    \Magento\Framework\ObjectManagerInterface $objectManager,
    EventManager $eventManager,
    OrderRepositoryInterface $orderRepository,
    \Webkul\Marketplace\Helper\Orders $orderHelper,
    \Cb\GreenlineApi\Helper\OrderInfo $orderinfoHelper,
    HelperData $helper,
    SaleslistFactory $saleslistFactory,
    MpOrdersModel $mpOrdersModel
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_orderRepository = $orderRepository;
        $this->orderHelper = $orderHelper;
        $this->orderinfoHelper = $orderinfoHelper;
        $this->helper = $helper;
        $this->saleslistFactory = $saleslistFactory;
        $this->mpOrdersModel = $mpOrdersModel;
    }

    public function cancelOrder($orderId, $sellerId) {

        if ($order = $this->_initOrder($orderId, $sellerId)) {
            $this->doCancelExecution($order, $sellerId);
        }

        return $this;
    }

    protected function _initOrder($id, $sellerId)
    {
        try {
            $order = $this->_orderRepository->get($id);
            $tracking = $this->orderinfoHelper->getOrderinfo($id, $sellerId);
            if ($tracking) {
                if ($tracking->getOrderId() == $id) {
                    if (!$id) {
                        $this->helper->logDataInLogger("Order Id #".$id.
                            " This order no longer exists.");

                        return false;
                    }
                } else {
                    $this->helper->logDataInLogger("Order Id #".$id.
                            " You are not authorize to manage this order.");

                    return false;
                }
            } else {
                $this->helper->logDataInLogger("Order Id #".$id.
                            " You are not authorize to manage this order.");

                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->helper->logDataInLogger("Order Id #".$id.
                " This order no longer exists. : ".$e->getMessage()
            );

            return false;
        } catch (InputException $e) {
            $this->helper->logDataInLogger("Order Id #".$id.
                " This order no longer exists. : ".$e->getMessage()
            );

            return false;
        }

        return $order;
    }   

    protected function doCancelExecution($order, $sellerId)
    {
        $orderId = $order->getId();
        $flag = $this->orderHelper->cancelorder($order, $sellerId);
        if ($flag) {
            $paidCanceledStatus = \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_CANCELED;
            $paymentCode = '';
            $paymentMethod = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            
            $collection = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
            foreach ($collection as $saleproduct) {
                $saleproduct->setCpprostatus(
                    $paidCanceledStatus
                );
                $saleproduct->setPaidStatus(
                    $paidCanceledStatus
                );
                if ($paymentCode == 'mpcashondelivery') {
                    $saleproduct->setCollectCodStatus(
                        $paidCanceledStatus
                    );
                    $saleproduct->setAdminPayStatus(
                        $paidCanceledStatus
                    );
                }
                $saleproduct->save();
            }
            $trackingcoll = $this->mpOrdersModel->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($trackingcoll as $tracking) {
                $tracking->setTrackingNumber('canceled');
                $tracking->setCarrierName('canceled');
                $tracking->setIsCanceled(1);
                $tracking->setOrderStatus('canceled');
                $tracking->save();
            }

            $this->helper->logDataInLogger("Order Id #".$orderId.
                " The order has been cancelled. "
            );

            $this->_eventManager->dispatch(
                'mp_order_cancel_after',
                ['seller_id' => $sellerId, 'order' => $order]
            );
        } else {
            $this->helper->logDataInLogger("Order Id #".$orderId.
                " You are not permitted to cancel this order. "
            );
        }
    }

}
