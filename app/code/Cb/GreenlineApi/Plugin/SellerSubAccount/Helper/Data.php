<?php

namespace Cb\GreenlineApi\Plugin\SellerSubAccount\Helper;

class Data
{

	/**
     * getAllPermissionTypes.
     *
     * @return array
     */
    public function afterGetAllPermissionTypes(\Webkul\SellerSubAccount\Helper\Data $subject, $result){

    	$result["greenlineapi/account/configuration"] = __("Greenline Configuration");
        $result["greenlineapi/exportorder/index"] = __("Greenline Parked Sales History");

        return $result;
    }

}
