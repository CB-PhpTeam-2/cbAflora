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
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory;
use Webkul\MpHyperLocal\Helper\Data as HelperData;

class PredispatchSalesOrderReorder implements ObserverInterface
{
    /**
     * @var Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Webkul\MpHyperLocal\Helper\Data
     */
    private $helperData;

    /**
     * @var Webkul\Marketplace\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @param OrderRepository $orderRepository
     * @param HelperData $helperData,
     * @param ProductFactory $productFactory
     */
    public function __construct(
        UrlInterface $urlInterface,
        MessageManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        HelperData $helperData,
        ProductFactory $productFactory
    ) {
        $this->urlInterface = $urlInterface;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->helperData = $helperData;
        $this->productFactory = $productFactory;
    }

    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance Order */
        $orderId = $observer->getRequest()->getParam('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $orderItems = $order->getAllItems();
            $nearestSellers = $this->helperData->getNearestSellers();
            foreach ($orderItems as $item) {
                $sellerId = $this->getProductSellerId($item->getProductId());
                if (!in_array($sellerId, $nearestSellers)) {
                    $this->messageManager->addError(__('Seller do not provide shipping at your location.'));
                    $orderHistory = $this->urlInterface->getUrl('sales/order/history');
                    $observer->getRequest()->setParam('order_id', null);
                    $observer->getControllerAction()->getResponse()->setRedirect($orderHistory);
                }
            }
        }
    }

    /**
     * getProductSellerLocation
     * @param int $proId
     * @return array
     */

    private function getProductSellerId($proId)
    {
        $sellerId = 0;
        $sellerPro = $this->productFactory->create()->getCollection()
                                            ->addFieldToFilter('mageproduct_id', $proId)
                                            ->setPageSize(1)->getFirstItem();
        if ($sellerPro->getEntityId()) {
            $sellerId =  $sellerPro->getSellerId();
        }
        return $sellerId;
    }
}
