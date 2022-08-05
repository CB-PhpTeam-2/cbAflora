<?php
 
namespace Cb\Pickup\Plugin;
 
class ApplyShipping
{
 
    public function __construct(\Webkul\MpHyperLocal\Helper\Data $mpHyHelperData)
    {
        $this->mpHyHelperData = $mpHyHelperData;
    }
 
    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        $request
    )
    {
        $savedAddress = $this->mpHyHelperData->getSavedAddress();

        $serviceType = '';
        if(sizeof($savedAddress) > 0){
            $serviceType = $savedAddress['service_type'];
        }

        if ($carrierCode == 'freeshipping' && $serviceType == 1) {
            // To disable the shipping method return false
            return false;
        }

        if ($carrierCode == 'mplocalship' && $serviceType == 2) {
            // To disable the shipping method return false
            return false;
        }

        // To enable the shipping method
        return $proceed($carrierCode, $request);
    }
}