<?php 

namespace Cb\CustomFrontend\Plugin\Block\Checkout;

class LayoutProcessor
{

	public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['elementTmpl'] = 'Cb_CustomFrontend/form/element/telephone';
        return $jsLayout;
    }

	/*protected function updateElementTmpls(&$jsLayoutResult){

		$shippingAddress = &$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
		$billingAddress = &$jsLayoutResult['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['billing-address']['children']['form-fields']['children'];

		$inputPathPhone = 'Cb_CustomFrontend/form/element/telephone';

	    $shippingAddress['telephone']['config']['elementTmpl'] = $inputPathPhone;
	    $billingAddress['telephone']['config']['elementTmpl'] = $inputPathPhone;

 	}*/

}
