<?php

namespace Webkul\MpHyperLocal\Controller\Session;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
/**
 * Webkul MpHyperLocal address controller.
 */
class Index extends Action
{
    protected $_resultFactory;
    protected $_session;
    protected $_customerSession;
    protected $_jsonHelper;

    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param NotificationHelper              $notificationHelper
     * @param CollectionFactory               $collectionFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_resultFactory = $context->getResultFactory();
        $this->_session = $session;
        $this->_customerSession = $customerSession;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $value = $this->getRequest()->getParam('seller_id');
        $this->_session->start();
        $status = 0;

        if($value != ''){
            $this->_session->setData('visitor_view_seller_id', $value);
            $status = 1;
        }

        $response = $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'status' => $status
                ]
            )
        );
        return $response;
    }
}
