<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTwilioSMSNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Webkul\MpTwilioSMSNotification\Helper\Data;

class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpTwilioSMSNotification\Helper\Data
     */
    private $helperData;
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @param Data                         $helperData
     * @param ManagerInterface             $messageManager
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        Data $helperData,
        ManagerInterface $messageManager,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helperData->getTwilioStatus() &&
            $this->helperData->isCustomerNotificationEnabled()
        ) {
            $twilioClient = $this->helperData->makeTwilloClient();
            $invoice = $observer->getInvoice();
            if (empty($invoice)) {
                return;
            }
            $order = $invoice->getOrder();
            $orderIncrementId = $order->getIncrementId();
            $visibleInvoiceItems = $this->getVisibleInvoiceItems($invoice);
            $invoiceItemsString =
                $this->getInvoiceItemsToString($visibleInvoiceItems);
            $customerName = $this->helperData->getCustomerName($order);
            $customerTelephone = $this->helperData->getCustomerTelephone($order);
            $customerEmail = $order->getCustomerEmail();
            $customerOrderHistoryUrl = $this->helperData->getSalesOrderHistoryUrl();
            if (!empty($customerTelephone) &&
                !empty($invoiceItemsString)
            ) {
                $body = __(
                    "Hi %1, An Invoice for item(s) %2 for your Order #%3 has been generated," .
                    " Please visit %4 or check your email %5 for more details",
                    $customerName,
                    $invoiceItemsString,
                    $orderIncrementId,
                    $customerOrderHistoryUrl,
                    $customerEmail
                );
                try {
                    $this->helperData->sendMessage(
                        $twilioClient,
                        $customerTelephone,
                        $body
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
    }

    /**
     * Get visible invoice items from list of invoice items.
     *
     * @param  \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Api\Data\InvoiceItemInterface[]
     */
    private function getVisibleInvoiceItems($invoice)
    {
        $invoiceItems = $invoice->getItems();
        $invoiceItemsArray = [];
        foreach ($invoiceItems as $invoiceItem) {
            if (empty($this->orderItemRepository
                    ->get($invoiceItem->getOrderItemId())
                    ->getParentItemId()) &&
               !empty((int)$invoiceItem->getQty())
            ) {
                $invoiceItemsArray[] = $invoiceItem;
            }
        }

        return $invoiceItemsArray;
    }

    /**
     * Get a comma seperated string of invoice items product name
     * including quantity from list of invoice items.
     *
     * @param  \Magento\Sales\Api\Data\InvoiceItemInterface[] $invoiceItems
     * @return string
     */
    private function getInvoiceItemsToString($invoiceItems)
    {
        $lastInvoiceItem = array_pop($invoiceItems);
        $lastInvoiceItem = empty($lastInvoiceItem)
            ? ''
            : $this->helperData->getOrderItemName(
                $this->orderItemRepository->get(
                    $lastInvoiceItem->getOrderItemId()
                )
            ) . ' (x' . (int)$lastInvoiceItem->getQty() . ')';
        $resultString =
            ltrim(
                array_reduce(
                    $invoiceItems,
                    function ($carry, $invoiceItem) {
                        return $carry . ', ' . $this->helperData->getOrderItemName(
                            $this->orderItemRepository->get(
                                $invoiceItem->getOrderItemId()
                            )
                        ) . ' (x' . (int)$invoiceItem->getQty() . ')';
                    }
                ),
                ', '
            );

        return empty($resultString) ? $lastInvoiceItem : ($resultString . ', '.__('and').' ' . $lastInvoiceItem);
    }
}
