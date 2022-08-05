<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpHyperLocal\Controller\Vendor;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Marketplace\Helper\Data as MarketplaceHelperData;
use Magento\Framework\Controller\ResultFactory;

/**
 * Marketplace Seller Collection controller.
 */
class Collection extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
	/**
     * @var MarketplaceHelperData
     */
	protected $mpHelper;
	
	protected $_storeManager;
	
	protected $messageManager;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param HelperData $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		MarketplaceHelperData $mpHelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_resultPageFactory = $resultPageFactory;
		$this->mpHelper = $mpHelper;
		$this->_storeManager = $storeManager;
		$this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Marketplace Seller's Product Collection Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
		//if($this->mpHelper->isSeller() == 1){
			$resultPage = $this->_resultPageFactory->create();
			return $resultPage;
		/*}else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_storeManager->getStore()->getBaseUrl());
			$message = __('SOrry, You are not authorised to view this page.');
			$this->messageManager->addErrorMessage($message);
            return $resultRedirect;
		}*/
    }
}
