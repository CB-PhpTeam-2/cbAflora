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
namespace Webkul\MpHyperLocal\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class MassDeleteArea extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    private $shipArea;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ShipAreaFactory $shipArea
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShipAreaFactory $shipArea,
        FormKeyValidator $formKeyValidator
    ) {
        $this->customerSession = $customerSession;
        $this->shipArea = $shipArea;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Area mass Delete
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath(
                'mphyperlocal/account/addshiparea',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $data = $this->getRequest()->getParams();
        if ($data && isset($data['ship_area_mass_delete'])) {
            $sellerId = $this->customerSession->getCustomerId();
            $shipAreaList = $this->shipArea->create()->getCollection()
                                            ->addFieldToFilter('seller_id', $sellerId)
                                            ->addFieldToFilter('entity_id', ['in' => $data['ship_area_mass_delete']]);
            $recordelete = 0;
            foreach ($shipAreaList as $shipArea) {
                $this->deleteObj($shipArea);
                $recordelete++;
            }
            $this->messageManager->addSuccess(__('Ship area deleted successfully.'));
        } else {
            $this->messageManager->addError(__('Invalid request.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mphyperlocal/account/addshiparea'));
    }

    /**
     * deleteObj
     * @param Object
     * @return void
     */
    private function deleteObj($object)
    {
        $object->delete();
    }
}
