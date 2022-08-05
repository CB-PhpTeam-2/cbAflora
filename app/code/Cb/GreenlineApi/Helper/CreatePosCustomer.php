<?php
namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\CustomerFactory;

class CreatePosCustomer extends AbstractHelper {

    protected $_resource;
    protected $customerFactory;
    protected $_moduleHelper;
    protected $quoteRepository;
    protected $mpHelper;

    public function __construct(
    \Magento\Framework\View\Element\Context $context,
    \Magento\Framework\App\ResourceConnection $resource,
    CustomerFactory $customerFactory,
    \Cb\GreenlineApi\Helper\Data $moduleHelper,
    CheckoutSession $checkoutSession,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
    \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->_moduleHelper = $moduleHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->mpHelper = $mpHelper;
    }

    public function getPosCustomerName() {

        if ($this->_moduleHelper->chkIsModuleEnable()) {
            $quoteId = (int)$this->checkoutSession->getQuote()->getId();
            $quote = $this->quoteRepository->get($quoteId);

            $items = $quote->getAllItems();
            $seller_id = '';
            $greenlineapiProductId = 1;
            foreach($items as $item) {
                $product = $this->_moduleHelper->getProductBySku($item->getSku());
                $mpProduct = $this->mpHelper->getSellerProductDataByProductId($item->getProductId());
                $seller_id = $mpProduct->getFirstItem()->getSellerId();
                if($product->getGreenlineapiProductId() == ''){
                    $greenlineapiProductId = 0;
                    break;
                }
            }

            if($seller_id != '' && is_numeric($seller_id)){
                if(!$this->_moduleHelper->getIsGreenlineEnable($seller_id)){
                    return $this;
                }else{
                    if($greenlineapiProductId == 0){
                        return $this;
                    }
                }
            }else{
                return $this;
            }

            $email = $quote->getCustomerEmail();
            $customerId = $quote->getCustomer()->getId();
            $phone_number = $quote->getShippingAddress()->getTelephone();

            /*$customerResponse = $this->getCustomer($seller_id, $email, $phone_number);

            if(sizeof($customerResponse) > 0 && array_key_exists('name', $customerResponse)){
                $customerName = $customerResponse['name'];
                $this->checkoutSession->setPosCustomerName($customerName);
            }*/
            $this->checkoutSession->setPosCustomerName('Test CUSTOMER2');
        }

        return $this;
    }

    public function searchCustomer($sellerId, $email, $phone) {
        
        $dataArray = [];
        $apiUrl = trim($this->_moduleHelper->getApiUrl());
        $apiUrl = trim($apiUrl, '/');
        $apiKey = trim($this->_moduleHelper->getApiKey($sellerId));
        $companyId = trim($this->_moduleHelper->getCompanyId($sellerId));
        $locationId = trim($this->_moduleHelper->getLocationId($sellerId));

        $limit = 10;
        $base_url = $apiUrl . '/api/v2/external/companies/'.$companyId.'/customers';
        $search_url = $base_url . '/search?limit='.$limit.'&offset=0';

        $data = [];
        $data['both'] = 'email='.$email.'&phone='.$phone;
        $data['email'] = $email;
        $data['phone'] = $phone;

        $createPosCustomer = 1;
        $customerResponse = [];
        foreach($data as $key => $value){

            if($key == 'both'){
                $curl_url = $search_url . '&'.$value;
            }else{
                $curl_url = $search_url . '&'.$key.'='.$value;
            }
            
            $response = $this->getCurlResponse($apiKey, $curl_url);
            if(sizeof($response) > 0){
                $customerResponse = $response;
                $createPosCustomer = 0;
                break;
            }
        }
        
        if($createPosCustomer == 1){
            $response = '';

            $quoteId = (int)$this->checkoutSession->getQuote()->getId();
            $quote = $this->quoteRepository->get($quoteId);
            $shippingAddress = $quote->getShippingAddress();

            $address = [];
            $address['address'] = $shippingAddress->getData('street');
            $address['city'] = $shippingAddress->getData('city');
            $address['province'] = $shippingAddress->getData('region');
            $address['country'] = $shippingAddress->getData('country_id');
            $address['postalCode'] = $shippingAddress->getData('telephone');

            $fullName = '';
            if ($shippingAddress->getData('firstname') != '') {
                $fullName .= $shippingAddress->getData('firstname');
            }
            if ($shippingAddress->getData('middlename') != '') {
                $fullName .= ' ' . $shippingAddress->getData('middlename');
            }
            if ($shippingAddress->getData('lastname')  != '') {
                $fullName .= ' ' . $shippingAddress->getData('lastname');
            }

            $customer = $this->customerSession->getCustomer();
            $genderText = $customer->getAttribute('gender')->getSource()->getOptionText($customer->getData('gender'));

            $customerData = [];
            $customerData['name'] = $fullName;
            $customerData['email'] = $quote->getCustomerEmail();
            $customerData['gender'] = strtolower($genderText);
            $customerData['birthday'] = $customer->getDob();
            $customerData['phone'] = sprintf($shippingAddress->getData('telephone'));
            $customerData['address'] = $address;

            $customerJson = json_encode($customerData);
            //$customerJson = json_encode($customerData, JSON_NUMERIC_CHECK);

            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $base_url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => $customerJson,
              CURLOPT_HTTPHEADER => array(
                'api-key: '.$apiKey,
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $dataArray = array();
            if($response != '' && $this->_moduleHelper->isJSON($response)){
                $dataArray = json_decode($response, true);
            }
            $customerResponse = $dataArray;
        }

        return $customerResponse;
    }
    
    public function getCurlResponse($apiKey, $curl_url) {

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $curl_url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'api-key: '.$apiKey
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $dataArray = array();
        if($response != '' && $this->_moduleHelper->isJSON($response)){
            $responseData = json_decode($response, true);
            if(array_key_exists("data", $responseData)){
                if(sizeof($responseData['data']) > 0){
                    $dataArray = $responseData['data'][0];
                }
            }
            //echo "<pre>"; print_r($dataArray);die;
        }

        return $dataArray;
    }

}
