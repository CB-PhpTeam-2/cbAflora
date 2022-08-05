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
use Webkul\Marketplace\Model\Orders;
use Webkul\MpTwilioSMSNotification\Helper\Data;

class SalesOrderCreditmemoRefundObserver implements ObserverInterface
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
     * @var Webkul\Marketplace\Model\Orders
     */
    private $orderModelMp;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface;
     */
    private $orderItemRepository;

    /**
     * @param Data                                      $helperData
     * @param MpHelper                                  $helperMarketplace
     * @param Product                                   $productModel
     * @param ManagerInterface                          $messageManager
     * @param OrderItemRepositoryInterface              $orderItemRepository
     */
    public function __construct(
        Data $helperData,
        Customer $customerModel,
        Orders $orderModelMp,
        MpHelper $helperMarketplace,
        Product $productModel,
        ManagerInterface $messageManager,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->orderModelMp = $orderModelMp;
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
            $client = $this->helperData->makeTwilloClient();
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $order = $creditmemo->getOrder();
            $orderIncrementId = $order->getIncrementId();
            $mpOrderHistoryUrl= $this->helperData->getMpOrderHistoryUrl();
            $sellerOrders = $this->orderModelMp->getCollection()
                                               ->addFieldToFilter(
                                                   'order_id',
                                                   $order->getEntityId()
                                               )->addFieldToFilter(
                                                   'seller_id',
                                                   ['neq' => 0]
                                               );
            $creditmemoItems = $this->getVisibleCreditmemoItems($creditmemo->getItems());
            $creditmemoItemsCategorizedBySeller = $this
                ->getCreditmemoItemsCategorizedBySeller($creditmemoItems, $sellerOrders);
            if ($this->helperData->isCustomerNotificationEnabled()) {
                $allItemsString = $this->getCreditmemoItemsToString($creditmemoItems);
                $messageContent = "Hi %1, A creditmemo has been generated for your Order %2 for item(s) %3,".
                        " Please visit %4 or check your mail %5 for more details";
                if (!empty($allItemsString)) {
                    $this->helperData->sendSMSToCustomer(
                        $order,
                        $allItemsString,
                        $this->messageManager,
                        $messageContent
                    );
                }
            }
            foreach ($creditmemoItemsCategorizedBySeller as $sellerId => $sellerCreditmemoItems) {
                $userData = $this->helperData->getCustomer($sellerId);
                $mpSellerCollection = $this->helperMarketplace->getSellerDataBySellerId($sellerId);
                $sellerCreditmemoItemsStringRepresentation =
                    $this->getCreditmemoItemsToString($sellerCreditmemoItems);
                $sellerEmail = $userData->getEmail();
                foreach ($mpSellerCollection as $sellerdata) {
                    $sellerFirstname = $userData->getFirstname();
                    $sellerContactNumber = str_replace(
                        " ",
                        "",
                        $sellerdata->getContactNumber()
                    );
                    $content = __(
                        "Hi Seller %1, A Creditmemo for item(s) %2 has been generated for your Order #%3," .
                        " Please visit %4 or check your mail %5 for more details",
                        $sellerFirstname,
                        $sellerCreditmemoItemsStringRepresentation,
                        $orderIncrementId,
                        $mpOrderHistoryUrl,
                        $sellerEmail
                    );
                    try {
                        $this->helperData->sendMessage(
                            $client,
                            $sellerContactNumber,
                            $content
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
     * Get Visible Creditmemo Items from list of creditmemo items.
     *
     * @param  \Magento\Sales\Api\Data\CreditmemoItemInterface[]
     *         \Magento\Sales\ResourceModel\Order\Creditmemo\Item\Collection
     *         $creditmemoItems
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[]
     */
    private function getVisibleCreditmemoItems($creditmemoItems)
    {
        $creditmemoItemsArray = [];
        foreach ($creditmemoItems as $creditmemoItem) {
            if (empty($this->orderItemRepository
                    ->get($creditmemoItem->getOrderItemId())
                    ->getParentItemId()) &&
                !empty($creditmemoItem->getQty())
            ) {
                $creditmemoItemsArray[] = $creditmemoItem;
            }
        }

        return $creditmemoItemsArray;
    }

    /**
     * Get a comma seperated string of creditmemo items product name
     * including quantity from creditmemo items list.
     *
     * @param  \Magento\Sales\Api\Data\CreditmemoItemInterface[] $creditmemoItems
     * @return string
     */
    private function getCreditmemoItemsToString($creditmemoItems)
    {
        $lastCreditmemoItem = array_pop($creditmemoItems);
        $lastCreditmemoItem = empty($lastCreditmemoItem)
            ? ''
            : $this->helperData
                ->getOrderItemName($this->orderItemRepository->get($lastCreditmemoItem->getOrderItemId())) . ' (x' .
                    (int)$lastCreditmemoItem->getQty() . ')';
        $resultString =
            ltrim(
                array_reduce(
                    $creditmemoItems,
                    function ($carry, $creditmemoItem) {
                        return $carry . ', ' . $this->helperData->getOrderItemName(
                            $this->orderItemRepository->get($creditmemoItem->getOrderItemId())
                        ) . ' (x' . (int)$creditmemoItem->getQty() . ')';
                    }
                ),
                ', '
            );

        return empty($resultString) ? $lastCreditmemoItem : ($resultString . ', '.__('and').' ' . $lastCreditmemoItem);
    }

    /**
     * Get creditmemo items categorized by seller id
     *
     * @param  \Magento\Sales\Api\Data\CreditmemoItemInterface[]|
     *         \Magento\Sales\Model\ResoureceModel\Order\Creditmemo\Collection
     *         $creditmemoItems
     * @param  \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $sellerOrderCollection
     * @return array
     */
    private function getCreditmemoItemsCategorizedBySeller($creditmemoItems, $sellerOrderCollection)
    {
        $creditmemoItemsCategorizedBySeller = [];
        foreach ($sellerOrderCollection as $sellerOrder) {
            $sellerCreditmemoItemsProductIds = $sellerOrder->getProductIds();
            foreach ($creditmemoItems as $creditmemoItem) {
                $creditmemoItemProductId = $creditmemoItem->getProductId();
                $pattern = "/^\s*$creditmemoItemProductId\s*$|" .
                            "^\s*$creditmemoItemProductId\s*,|" .
                            ",\s*$creditmemoItemProductId\s*$|" .
                            ",\s*$creditmemoItemProductId\s*,/";
                if (preg_match($pattern, $sellerCreditmemoItemsProductIds)) {
                    $creditmemoItemsCategorizedBySeller[$sellerOrder->getSellerId()][] = $creditmemoItem;
                }
            }
        }

        return $creditmemoItemsCategorizedBySeller;
    }
}
