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

namespace Webkul\MpHyperLocal\Controller\Outlet;

use Magento\Framework\App\RequestInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;

class Edit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Webkul\MpHyperLocal\Model\OutletFactory
     */
    protected $outletFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var SourceInterfaceFactory
     */
    protected $sourceFactory;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\MpHyperLocal\Model\OutletFactory $outletFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $modelUrl
     * @param SourceInterfaceFactory $sourceFactory
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Marketplace\Helper\Data $helperData,
        \Magento\Framework\App\Action\Context $context,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $modelUrl,
        SourceInterfaceFactory $sourceFactory
    ) {
        $this->helperData        = $helperData;
        $this->coreRegistry      = $coreRegistry;
        $this->outletFactory     = $outletFactory;
        $this->_customerSession  = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->modelUrl          = $modelUrl;
        $this->sourceFactory = $sourceFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->modelUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper     = $this->helperData;
        $model      = $this->outletFactory->create();
        $outletId   = $this->getRequest()->getParam("id");
        $isPartner  = $helper->isSeller();
        $sellerId = $helper->getCustomerId();
        $resultPage = $this->_resultPageFactory->create();
        if ($isPartner == 1) {
            if ($outletId) {
                $collection = $model->getCollection()
                                    ->addFieldToFilter('source_code', $outletId)
                                    ->addFieldToFilter('seller_id', $sellerId);
                $sourceModel = $this->sourceFactory->create()->load($outletId);
                if ($collection->getSize() && $sourceModel->getSourceCode()) {
                    if ($helper->getIsSeparatePanel()) {
                        $resultPage->addHandle('mphyperlocal_layout2_outlet_edit');
                    }
                    $resultPage->getConfig()->getTitle()->set(
                        __('Edit Outlet %1', $model->getOutletName())
                    );
                } else {
                    $this->messageManager->addError(__("This outlet no longer exists."));
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $resultPage->getConfig()->getTitle()->set(
                    __('Add New outlet')
                );
            }
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
