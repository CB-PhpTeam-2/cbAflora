<?php
namespace Cb\Pickup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\MpHyperLocal\Helper\Data $mpHyHelperData,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->mpHyHelperData = $mpHyHelperData;
        $this->request = $request;
    }

	public function allowDifferentShippingAddress()
	{  
        $action = $this->request->getFullActionName();

        if($action == 'checkout_index_index'){
    	    $savedAddress = $this->mpHyHelperData->getSavedAddress();

            $serviceType = '';
            if(sizeof($savedAddress) > 0 && array_key_exists('service_type', $savedAddress)){
                $serviceType = $savedAddress['service_type'];
                if ($serviceType == 2) {
                	return false;
                }
            }
        }

        return true;
	}
}