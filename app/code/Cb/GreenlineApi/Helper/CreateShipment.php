<?php
namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

class CreateShipment extends AbstractHelper {

    protected $_objectManager;
    protected $_shipmentSender;
    protected $_shipmentFactory;
    protected $_orderRepository;
    protected $orderHelper;
    protected $helper;
    protected $saleslistFactory;
    protected $mpOrdersModel;

    public function __construct(
    \Magento\Framework\View\Element\Context $context,
    \Magento\Framework\ObjectManagerInterface $objectManager,
    EventManager $eventManager,
    ShipmentSender $shipmentSender,
    ShipmentFactory $shipmentFactory,
    OrderRepositoryInterface $orderRepository,
    \Cb\GreenlineApi\Helper\OrderInfo $orderinfoHelper,
    HelperData $helper,
    SaleslistFactory $saleslistFactory,
    MpOrdersModel $mpOrdersModel
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_shipmentSender = $shipmentSender;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_orderRepository = $orderRepository;
        $this->orderinfoHelper = $orderinfoHelper;
        $this->helper = $helper;
        $this->saleslistFactory = $saleslistFactory;
        $this->mpOrdersModel = $mpOrdersModel;
    }

    public function generateShipment($orderId, $sellerId) {

        if ($order = $this->_initOrder($orderId, $sellerId)) {
            $this->doShipmentExecution($order, $sellerId);
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

    protected function doShipmentExecution($order, $sellerId)
    {
        try {
            $orderId = $order->getId();
            $marketplaceOrder = $this->orderinfoHelper->getOrderinfo($orderId, $sellerId);
            $trackingid = '';
            $carrier = '';
            $trackingData = [];
            //$paramData = $this->getRequest()->getParams();
            $paramData = [];
            if (!empty($paramData['tracking_id'])) {
                $trackingid = $paramData['tracking_id'];
                $trackingData[1]['number'] = $trackingid;
                $trackingData[1]['carrier_code'] = 'custom';
            }
            if (!empty($paramData['carrier'])) {
                $carrier = $paramData['carrier'];
                $trackingData[1]['title'] = $carrier;
            }
            $shippingLabel = '';
            if (!empty($paramData['api_shipment'])) {
                $packageDetails = [];
                if (!empty($paramData['package'])) {
                    $packageDetails = json_decode($paramData['package']);
                }
                $this->_eventManager->dispatch(
                    'generate_api_shipment',
                    [
                        'api_shipment' => $paramData['api_shipment'],
                        'order_id' => $orderId,
                        'package_details' => $packageDetails
                    ]
                );
                //$shipmentData = $this->_customerSession->getData('shipment_data');
                $shipmentData = [];
                $trackingid = '';
                if (!empty($shipmentData['tracking_number'])) {
                    $trackingid = $shipmentData['tracking_number'];
                }
                $shippingLabel = '';
                if (!empty($shipmentData['shipping_label'])) {
                    $shippingLabel = $shipmentData['shipping_label'];
                }
                $trackingData[1]['number'] = $trackingid;
                if (array_key_exists('carrier_code', $shipmentData)) {
                    $trackingData[1]['carrier_code'] = $shipmentData['carrier_code'];
                } else {
                    $trackingData[1]['carrier_code'] = 'custom';
                }
            }

            if (empty($paramData['api_shipment']) || $trackingid != '') {
                if ($order->canUnhold()) {
                    $this->helper->logDataInLogger("Order Id #".$orderId." Can not create shipment as order is in HOLD state."
                    );
                } else {
                    $items = [];

                    $collection = $this->saleslistFactory->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        $orderId
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    foreach ($collection as $saleproduct) {
                        array_push($items, $saleproduct['order_item_id']);
                    }

                    $itemsarray = $this->_getShippingItemQtys($order, $items);

                    if (count($itemsarray) > 0) {
                        $shipment = false;
                        $shipmentId = 0;
                        if (!empty($paramData['shipment_id'])) {
                            $shipmentId = $paramData['shipment_id'];
                        }
                        if ($shipmentId) {
                            $shipment = $this->_shipment->load($shipmentId);
                        } elseif ($orderId) {
                            if ($order->getForcedDoShipmentWithInvoice()) {
                                $this->helper->logDataInLogger("Order Id #".$orderId." Cannot do shipment for the order separately from invoice."
                                );
                            }
                            if (!$order->canShip()) {
                                $this->helper->logDataInLogger("Order Id #".$orderId." Cannot do shipment for the order."
                                );
                            }

                            $shipment = $this->_prepareShipment(
                                $order,
                                $itemsarray['data'],
                                $trackingData
                            );
                            if ($shippingLabel!='') {
                                $shipment->setShippingLabel($shippingLabel);
                            }
                        }
                        if ($shipment) {
                            $comment = '';
                            $shipment->getOrder()->setCustomerNoteNotify(
                                !empty($data['send_email'])
                            );
                            $isNeedCreateLabel=!empty($shippingLabel) && $shippingLabel;
                            $shipment->getOrder()->setIsInProcess(true);

                            $transactionSave = $this->_objectManager->create(
                                'Magento\Framework\DB\Transaction'
                            )->addObject(
                                $shipment
                            )->addObject(
                                $shipment->getOrder()
                            );
                            $transactionSave->save();

                            $shipmentId = $shipment->getId();

                            $sellerCollection = $this->mpOrdersModel->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                ['eq' => $orderId]
                            )
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $sellerId]
                            );
                            foreach ($sellerCollection as $row) {
                                if ($shipment->getId() != '') {
                                    $row->setShipmentId($shipment->getId());
                                    //$row->setTrackingNumber($trackingid);
                                    //$row->setCarrierName($carrier);
                                    if ($row->getInvoiceId()) {
                                        $row->setOrderStatus('complete');
                                    } else {
                                        $row->setOrderStatus('processing');
                                    }
                                    $row->save();
                                }
                            }

                            $this->_shipmentSender->send($shipment);

                            if($isNeedCreateLabel){
                                $this->helper->logDataInLogger("Order Id #".$orderId." The shipment has been created."
                                );
                            }else{
                                $this->helper->logDataInLogger("Order Id #".$orderId." The shipping label has been created."
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger("Order Id #".$orderId." ".$e->getMessage()
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Order Id #".$orderId." We can\'t save the shipment right now. ".$e->getMessage()
            );
        }
    }

    protected function _prepareShipment($order, $items, $trackingData)
    {
        $shipment = $this->_shipmentFactory->create(
            $order,
            $items,
            $trackingData
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }

    protected function _getShippingItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = intval($item->getQtyOrdered() - $item->getQtyShipped());

                $_item = $item;

                // for bundle product
                $bundleitems = array_merge([$_item], $_item->getChildrenItems());

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = intval(
                                $_bundleitem->getQtyOrdered() - $item->getQtyShipped()
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return ['data' => $data,'subtotal' => $subtotal,'baseSubtotal' => $baseSubtotal];
    }

}
