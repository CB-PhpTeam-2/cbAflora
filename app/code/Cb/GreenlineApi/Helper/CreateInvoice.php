<?php
namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Api\OrderRepositoryInterface;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

class CreateInvoice extends AbstractHelper {

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
    InvoiceSender $invoiceSender,
    OrderRepositoryInterface $orderRepository,
    \Cb\GreenlineApi\Helper\OrderInfo $orderinfoHelper,
    HelperData $helper,
    SaleslistFactory $saleslistFactory,
    MpOrdersModel $mpOrdersModel
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_invoiceSender = $invoiceSender;
        $this->_orderRepository = $orderRepository;
        $this->orderinfoHelper = $orderinfoHelper;
        $this->helper = $helper;
        $this->saleslistFactory = $saleslistFactory;
        $this->mpOrdersModel = $mpOrdersModel;
    }

    public function generateInvoice($orderId, $sellerId) {

        if ($order = $this->_initOrder($orderId, $sellerId)) {
            $this->doInvoiceExecution($order, $sellerId);
            $this->doAdminShippingInvoiceExecution($order);
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

    protected function doInvoiceExecution($order, $sellerId)
    {
        try {
            $helper = $this->helper;
            $orderId = $order->getId();
            if ($order->canUnhold()) {
                $this->helper->logDataInLogger("Order Id #".$orderId.
                    " Can not create invoice as order is in HOLD state."
                );
            } else {
                $data = [];
                $data['send_email'] = 1;
                $marketplaceOrder = $this->orderinfoHelper->getOrderinfo($orderId, $sellerId);
                $invoiceId = $marketplaceOrder->getInvoiceId();
                if (!$invoiceId) {
                    $items = [];
                    $itemsarray = [];
                    $shippingAmount = 0;
                    $couponAmount = 0;
                    $codcharges = 0;
                    $paymentCode = '';
                    $paymentMethod = '';
                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    $trackingsdata = $this->mpOrdersModel->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        $orderId
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    foreach ($trackingsdata as $tracking) {
                        $shippingAmount = $tracking->getShippingCharges();
                        $couponAmount = $tracking->getCouponAmount();
                        if ($paymentCode == 'mpcashondelivery') {
                            $codcharges = $tracking->getCodCharges();
                        }
                    }
                    $codCharges = 0;
                    $tax = 0;
                    $currencyRate = 1;
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
                        $currencyRate = $saleproduct->getCurrencyRate();
                        if ($paymentCode == 'mpcashondelivery') {
                            $codCharges = $codCharges + $saleproduct->getCodCharges();
                        }
                        $tax = $tax + $saleproduct->getTotalTax();
                        array_push($items, $saleproduct['order_item_id']);
                    }

                    $itemsarray = $this->_getItemQtys($order, $items);

                    if (count($itemsarray) > 0 && $order->canInvoice()) {
                        $invoice = $this->_objectManager->create(
                            \Magento\Sales\Model\Service\InvoiceService::class
                        )->prepareInvoice($order, $itemsarray['data']);

                        if (!$invoice) {
                            $this->helper->logDataInLogger("Order Id #".$orderId." We can\'t save the invoice right now."
                            );
                        }
                        if (!$invoice->getTotalQty()) {
                            $this->helper->logDataInLogger("Order Id #".$orderId." You can\'t create an invoice without products."
                            );
                        }

                        if (!empty($data['capture_case'])) {
                            $invoice->setRequestedCaptureCase(
                                $data['capture_case']
                            );
                        }

                        if (!empty($data['comment_text'])) {
                            $invoice->addComment(
                                $data['comment_text'],
                                isset($data['comment_customer_notify']),
                                isset($data['is_visible_on_front'])
                            );

                            $invoice->setCustomerNote($data['comment_text']);
                            $invoice->setCustomerNoteNotify(
                                isset($data['comment_customer_notify'])
                            );
                        }

                        $currentCouponAmount = $currencyRate * $couponAmount;
                        $currentShippingAmount = $currencyRate * $shippingAmount;
                        $currentTaxAmount = $currencyRate * $tax;
                        $currentCodcharges = $currencyRate * $codcharges;
                        $invoice->setBaseDiscountAmount($couponAmount);
                        $invoice->setDiscountAmount($currentCouponAmount);
                        $invoice->setShippingAmount($currentShippingAmount);
                        $invoice->setBaseShippingInclTax($shippingAmount);
                        $invoice->setBaseShippingAmount($shippingAmount);
                        $invoice->setSubtotal($itemsarray['subtotal']);
                        $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
                        if ($paymentCode == 'mpcashondelivery') {
                            $invoice->setMpcashondelivery($currentCodcharges);
                            $invoice->setBaseMpcashondelivery($codCharges);
                        }
                        $invoice->setGrandTotal(
                            $itemsarray['subtotal'] +
                            $currentShippingAmount +
                            $currentCodcharges +
                            $currentTaxAmount -
                            $currentCouponAmount
                        );
                        $invoice->setBaseGrandTotal(
                            $itemsarray['baseSubtotal'] +
                            $shippingAmount +
                            $codcharges +
                            $tax -
                            $couponAmount
                        );

                        $invoice->register();

                        $invoice->getOrder()->setCustomerNoteNotify(
                            !empty($data['send_email'])
                        );
                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = $this->_objectManager->create(
                            \Magento\Framework\DB\Transaction::class
                        )->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        $transactionSave->save();

                        $invoiceId = $invoice->getId();

                        $this->_invoiceSender->send($invoice);

                        $this->helper->logDataInLogger("Order Id #".$orderId." Invoice has been created for this order."
                        );
                    }
                    /*update mpcod table records*/
                    if ($invoiceId != '') {
                        if ($paymentCode == 'mpcashondelivery') {
                            $saleslistColl = $this->saleslistFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                $orderId
                            )
                            ->addFieldToFilter(
                                'seller_id',
                                $sellerId
                            );
                            foreach ($saleslistColl as $saleslist) {
                                $saleslist->setCollectCodStatus(1);
                                $saleslist->save();
                            }
                        }

                        $trackingcol1 = $this->mpOrdersModel->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($trackingcol1 as $row) {
                            $row->setInvoiceId($invoiceId);
                            if ($row->getShipmentId()) {
                                $row->setOrderStatus('complete');
                            } else {
                                $row->setOrderStatus('processing');
                            }
                            $row->save();
                        }
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger("Order Id #".$orderId." Line 279 ".$e->getMessage()
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Order Id #".$orderId." Line 282 We can\'t save the invoice right now. ".$e->getMessage()
            );
        }
    }

    protected function doAdminShippingInvoiceExecution($order)
    {
        try {
            $paymentCode = '';
            $paymentMethod = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            if (!$order->canUnhold() && ($order->getGrandTotal() > $order->getTotalPaid())) {
                $isAllItemInvoiced = $this->isAllItemInvoiced($order);

                if ($isAllItemInvoiced && $order->getShippingAmount()) {
                    $invoice = $this->_objectManager->create(
                        \Magento\Sales\Model\Service\InvoiceService::class
                    )->prepareInvoice(
                        $order,
                        []
                    );
                    if (!$invoice) {
                        $this->helper->logDataInLogger("Order Id #".$orderId." Line306 We can\'t save the invoice right now. "
                        );
                    }

                    $baseSubtotal = $order->getBaseShippingAmount();
                    $subtotal = $order->getShippingAmount();

                    if (!empty($data['capture_case'])) {
                        $invoice->setRequestedCaptureCase(
                            $data['capture_case']
                        );
                    }

                    if (!empty($data['comment_text'])) {
                        $invoice->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );

                        $invoice->setCustomerNote($data['comment_text']);
                        $invoice->setCustomerNoteNotify(
                            isset($data['comment_customer_notify'])
                        );
                    }
                    $invoice->setShippingAmount($subtotal);
                    $invoice->setBaseShippingInclTax($baseSubtotal);
                    $invoice->setBaseShippingAmount($baseSubtotal);
                    $invoice->setSubtotal($subtotal);
                    $invoice->setBaseSubtotal($baseSubtotal);
                    $invoice->setGrandTotal($subtotal);
                    $invoice->setBaseGrandTotal($baseSubtotal);
                    $invoice->register();

                    $invoice->getOrder()->setCustomerNoteNotify(
                        !empty($data['send_email'])
                    );
                    $invoice->getOrder()->setIsInProcess(true);

                    $transactionSave = $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();

                    $this->_eventManager->dispatch(
                        'mp_order_shipping_invoice_save_after',
                        ['invoice' => $invoice, 'order' => $order]
                    );

                    $this->_invoiceSender->send($invoice);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger("Order Id #".$orderId." Line362 ".$e->getMessage()
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Order Id #".$orderId." Line366 ".$e->getMessage()
            );
        }
    }

    protected function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = intval($item->getQtyOrdered() - ($item->getQtyInvoiced() + $item->getQtyCanceled()));

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
                                $_bundleitem->getQtyOrdered() - ($_bundleitem->getQtyInvoiced() + $_bundleitem->getQtyCanceled())
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

    protected function isAllItemInvoiced($order)
    {
        $flag = 1;
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            } elseif ($item->getProductType() == 'bundle') {
                // for bundle product
                $bundleitems = array_merge([$item], $item->getChildrenItems());
                foreach ($bundleitems as $bundleitem) {
                    if ($bundleitem->getParentItem()) {
                        if (intval($bundleitem->getQtyOrdered() - $item->getQtyInvoiced())) {
                            $flag = 0;
                        }
                    }
                }
            } else {
                if (intval($item->getQtyOrdered() - $item->getQtyInvoiced())) {
                    $flag = 0;
                }
            }
        }

        return $flag;
    }

}
