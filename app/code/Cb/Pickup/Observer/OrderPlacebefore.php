<?php 

namespace Cb\Pickup\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderPlacebefore implements ObserverInterface
{
	protected $_objectManager;
	protected $_checkoutsession;
	protected $_resource;
	protected $mpHypHelper;
	protected $_createPosCustomerHelper;

	public function __construct(
	    \Magento\Framework\ObjectManagerInterface $objectManager,
	    \Magento\Checkout\Model\Session $checkoutsession,
	    \Magento\Framework\App\ResourceConnection $resource,
	    \Webkul\MpHyperLocal\Helper\Data $mpHypHelper,
	    \Cb\GreenlineApi\Helper\CreatePosCustomer $createPosCustomerHelper
	) {
	    $this->_objectManager = $objectManager;
	    $this->_checkoutsession = $checkoutsession;
	    $this->_resource = $resource;
	    $this->mpHypHelper = $mpHypHelper;
	    $this->_createPosCustomerHelper = $createPosCustomerHelper;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
	    $quote = $observer->getQuote();
		$order = $observer->getOrder();

		$savedAddress = $this->mpHypHelper->getSavedAddress();
        $service_type = 1;
        if(sizeof($savedAddress) > 0){
            $service_type = $savedAddress['service_type'];
        }

        $quote->setData('service_type', $service_type);
		$quote->save();
		
		$order->setData('service_type', $service_type);
		$order->save();

		//$this->_createPosCustomerHelper->getPosCustomerName();
	}
}