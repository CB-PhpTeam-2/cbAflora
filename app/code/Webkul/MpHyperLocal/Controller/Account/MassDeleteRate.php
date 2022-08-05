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
use Webkul\MpHyperLocal\Model\ShipRateFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class MassDeleteRate extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Webkul\MpHyperLocal\Model\ShipRateFactory
     */
    private $shipRate;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ShipRateFactory $shipRate
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShipRateFactory $shipRate,
        FormKeyValidator $formKeyValidator
    ) {
        $this->customerSession = $customerSession;
        $this->shipRate = $shipRate;
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
                'mphyperlocal/account/addrate',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $data = $this->getRequest()->getParams();
        if ($data && isset($data['ship_rate_mass_delete'])) {
            $sellerId = $this->customerSession->getCustomerId();
            $shipRateList = $this->shipRate->create()->getCollection()
                                            ->addFieldToFilter('seller_id', $sellerId)
                                            ->addFieldToFilter('entity_id', ['in' => $data['ship_rate_mass_delete']]);
            $recordelete = 0;
            foreach ($shipRateList as $shipRate) {
                $this->deleteObj($shipRate);
                $recordelete++;
            }
            $this->messageManager->addSuccess(__('Ship rate deleted successfully.'));
        } else {
            $this->messageManager->addError(__('Invalid request.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mphyperlocal/account/addrate'));
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
