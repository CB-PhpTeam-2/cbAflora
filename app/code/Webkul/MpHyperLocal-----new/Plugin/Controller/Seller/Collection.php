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
namespace Webkul\MpHyperLocal\Plugin\Controller\Seller;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MpHyperLocal\Helper\Data;
use Magento\Framework\UrlFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Collection
{
    /**
     * @param \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @param \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @param UrlFactory
     */
    protected $urlModel;

    /**
     * @param MpHelper
     */
    protected $mpHelper;

    /**
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param Data $helper
     * @param UrlFactory $urlModel
     * @param MpHelper $mpHelper
     */
    public function __construct(
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        Data $helper,
        UrlFactory $urlModel,
        MpHelper $mpHelper
    ) {
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->helper = $helper;
        $this->urlModel = $urlModel;
        $this->mpHelper = $mpHelper;
    }
    
    /**
     * @see \Magento\Catalog\Controller\Product\View::execute()
     */
    public function afterExecute(\Webkul\Marketplace\Controller\Seller\Collection $subject, $result)
    {
        $helper = $this->helper;
        $status = true;
        $shopUrl = $this->mpHelper->getCollectionUrl();
        if (!$shopUrl) {
            $shopUrl = $subject->getRequest()->getParam('shop');
        }
        $outlet = $subject->getRequest()->getParam('outlet');
        if ($shopUrl) {
            $sellerCollection = $this->mpHelper
                                    ->getSellerCollectionObjByShop($shopUrl)
                                    ->getFirstItem();
            $sellerId = $sellerCollection->getSellerId();
            if ($outlet) {
                $status = $helper->getOutletStatus($sellerId, $outlet);
            } else {
                $sellerIds = $helper->getNearestSellers();
                if (!in_array($sellerId, $sellerIds)) {
                    $status = false;
                }
            }
        }
        if (!$status) {
            $url = $this->urlModel->create()->getUrl();
            $this->messageManager->addNotice(__("store does not provide service on your location."));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($url);
        }
        return $result;
    }
}
