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

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\MpTwilioSMSNotification\Helper\Data;

class MpProductSoldObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpTwilioSMSNotification\Helper\Data
     */
    private $helperData;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    private $helperMarketplace;
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var Magento\Catalog\Model\Product
     */
    private $productModel;
    /**
     * @var Magento\Customer\Model\Customer
     */
    private $customerModel;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @param Data                         $helperData
     * @param Customer                     $customerModel
     * @param MpHelper                     $helperMarketplace
     * @param Product                      $productModel
     * @param ManagerInterface             $messageManager
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        Data $helperData,
        Customer $customerModel,
        MpHelper $helperMarketplace,
        Product $productModel,
        ManagerInterface $messageManager,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->customerModel = $customerModel;
        $this->productModel = $productModel;
        $this->messageManager = $messageManager;
        $this->helperMarketplace = $helperMarketplace;
        $this->helperData = $helperData;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helperData->getTwilioStatus()) {
            $sellerWholeData = $observer->getItemwithseller();
            $twilioClient = $this->helperData->makeTwilloClient();
            $mpOrderHistoryUrl = $this->helperData->getMpOrderHistoryUrl();
            foreach ($sellerWholeData as $sellerId => $orderDetails) {
                if (empty($orderDetails)) {
                    continue;
                }
                $invoice = array_values($orderDetails)[0]['invoice'];
                $order = $invoice->getOrder();
                $orderIncrementId = $order->getIncrementId();
                $mpSellerCollection = $this->helperMarketplace->getSellerDataBySellerId($sellerId);
                foreach ($mpSellerCollection as $sellerData) {
                    $invoiceItemsString = $this->getInvoiceItemsToString($orderDetails);
                    $sellerFirstname = $this->helperData
                                       ->getCustomer($sellerId)
                                       ->getFirstname();
                    $sellerContactNumber = str_replace(
                        " ",
                        "",
                        $sellerData->getContactNumber()
                    );
                    $sellerEmail =
                        $this->helperData->getCustomer($sellerId)
                                         ->getEmail();
                    $body = __(
                        "Hi Seller %1, your Order #%2 for item(s) %3 has been invoiced." .
                        " Please visit %4 or check your mail %5 for more details",
                        $sellerFirstname,
                        $orderIncrementId,
                        $invoiceItemsString,
                        $mpOrderHistoryUrl,
                        $sellerEmail
                    );
                    try {
                        $this->helperData->sendMessage(
                            $twilioClient,
                            $sellerContactNumber,
                            $body
                        );
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                    break;
                }
            }
        }
    }

    /**
     * Get visible invoice items from list of invoice items.
     *
     * @param  array $invoiceItems
     * @return array
     */
    private function getVisibleInvoiceItems($invoiceItems)
    {
        foreach ($invoiceItems as $invoiceItem) {
            $orderItem = $this->orderItemRepository->get($invoiceItem['order_item_id']);
            if (empty($orderItem->getParentItemId()) &&
                !empty($invoiceItem['qty'])
            ) {
                $visibleInvoiceItems[] = $invoiceItem;
            }
        }

        return $visibleInvoiceItems;
    }

    /**
     * Get a comma seperated string of invoice items product name
     * including quantity from list of invoice items
     *
     * @param  \Magento\Sales\Api\Data\InvoiceItemInterface[] $invoiceItems
     * @return string
     */
    private function getInvoiceItemsToString($invoiceItems)
    {
        $invoiceItems = $this->getVisibleInvoiceItems($invoiceItems);
        $lastInvoiceItem = array_pop($invoiceItems);
        $lastInvoiceItem = empty($lastInvoiceItem)
            ? ''
            : $this->helperData->getOrderItemName(
                $this->orderItemRepository->get(
                    $lastInvoiceItem['order_item_id']
                )
            ) . ' (x' . (int)$lastInvoiceItem['qty'] . ')';
        $resultString =
            ltrim(
                array_reduce(
                    $invoiceItems,
                    function ($carry, $invoiceItem) {
                        return $carry . ', ' . $this->helperData->getOrderItemName(
                            $this->orderItemRepository->get($invoiceItem['order_item_id'])
                        ) . ' (x' . (int)$invoiceItem['qty'] . ')';
                    }
                ),
                ', '
            );

        return empty($resultString) ? $lastInvoiceItem : ($resultString . ', '.__('and').' ' . $lastInvoiceItem);
    }
}
