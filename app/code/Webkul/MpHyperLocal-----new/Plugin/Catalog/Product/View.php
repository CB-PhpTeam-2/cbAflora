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
namespace Webkul\MpHyperLocal\Plugin\Catalog\Product;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MpHyperLocal\Helper\Data;
use Magento\Framework\UrlFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\HTTP\Header;

class View
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
     * @var \Magento\Framework\HTTP\Header
     */
    protected $httpHeader;

    /**
     * @param ManagerInterface    $messageManager
     * @param ResultFactory       $resultFactory
     * @param Data                $helper
     * @param UrlFactory          $urlModel
     * @param MpHelper            $mpHelper
     * @param Header              $httpHeader
     */
    public function __construct(
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        Data $helper,
        UrlFactory $urlModel,
        MpHelper $mpHelper,
        Header $httpHeader
    ) {
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->helper = $helper;
        $this->urlModel = $urlModel;
        $this->mpHelper = $mpHelper;
        $this->httpHeader = $httpHeader;
    }
    
    /**
     * @see \Magento\Catalog\Controller\Product\View::execute()
     */
    public function afterExecute(\Magento\Catalog\Controller\Product\View $subject, $result)
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        if (strpos($userAgent, 'curl') === false) {
            if ($this->helper->isEnabled()) {
                $productId = (int) $subject->getRequest()->getParam('id');
                $url = $this->urlModel->create()->getUrl();
                $savedAddress = $this->helper->getSavedAddress();
                if ($savedAddress) {
                    $status = true;
                    $sellerIds = $this->helper->getNearestSellers();
                    $sellerId = $this->mpHelper->getSellerIdByProductId($productId);
                    if (!in_array($sellerId, $sellerIds)) {
                        if (!$this->helper->getOutletStatus($sellerId)) {
                            $this->messageManager->addNotice(__("store does not provide service on your location."));
                            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($url);
                        }
                    }
                } else {
                    $this->messageManager->addNotice(__('Please select your location!'));
                    return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($url);
                }
            }
        }
        return $result;
    }
}
