<?php

namespace Paysafe\Payment\Plugin\SellerSubAccount\Helper;

class Data
{

	/**
     * getAllPermissionTypes.
     *
     * @return array
     */
    public function afterGetAllPermissionTypes(\Webkul\SellerSubAccount\Helper\Data $subject, $result){

    	$result["paysafe/account/configuration"] = __("Paysafe Configuration");

        return $result;
    }

}
