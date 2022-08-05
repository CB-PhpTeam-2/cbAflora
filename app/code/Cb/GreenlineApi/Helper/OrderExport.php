<?php

namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

class OrderExport extends AbstractHelper {

    protected $_resource;
    protected $_orderModel;
    protected $customerFactory;
    protected $_moduleHelper;
    protected $_orderExportHistoryFactory;
    protected $_invoiceSender;
    protected $helper;
    protected $mpHelper;

    public function __construct(
    \Magento\Framework\View\Element\Context $context,
    \Magento\Framework\App\ResourceConnection $resource,
    \Magento\Sales\Model\OrderFactory $orderModel,
    CustomerFactory $customerFactory,
    CheckoutSession $checkoutSession,
    \Cb\GreenlineApi\Helper\Data $moduleHelper,
    \Cb\GreenlineApi\Model\OrderExportHistoryFactory $orderExportHistoryFactory,
    \Webkul\MpHyperLocal\Helper\Data $helper,
    \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_resource = $resource;
        $this->_orderModel = $orderModel;
        $this->customerFactory = $customerFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_moduleHelper = $moduleHelper;
        $this->_orderExportHistoryFactory = $orderExportHistoryFactory;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
    }

    public function exportOrder($orderId) {

        if ($this->_moduleHelper->chkIsModuleEnable()) {
            $orderData = array();
            $connection = $this->_resource->getConnection();
            $order = $this->_orderModel->create()->load($orderId);
            //echo "<pre>"; print_r($order->getData());die;
            $customerEmail = $order->getCustomerEmail();
            $customerId = $this->getOrderCustomerId($customerEmail);
            //$customerId = $order->getCustomerId();
            $storeId = $order->getStoreId();

            $fullName = '';
            if ($order->getCustomerFirstname()) {
                $fullName .= $order->getCustomerFirstname();
            }
            if ($order->getCustomerMiddlename()) {
                $fullName .= ' ' . $order->getCustomerMiddlename();
            }
            if ($order->getCustomerLastname()) {
                $fullName .= ' ' . $order->getCustomerLastname();
            }

            $orderId = $order->getId();
            $incrementId = $order->getIncrementId();

            $netAmount = $order->getGrandTotal() - $order->getTaxAmount();

            $orderItems = $order->getAllItems();

            $seller_id = '';
            $items = array();
            $greenlineapiProductId = 1;
            if ($orderItems) {
                foreach ($orderItems as $item) {
                    //if($item->getProductType() == 'simple'){
                    if (!array_key_exists($item->getSku(), $items)) {
                        $discountMessage = 'custom discount';
                        $product = $this->_moduleHelper->getProductBySku($item->getSku());
                        $mpProduct = $this->mpHelper->getSellerProductDataByProductId($item->getProductId());
                        $seller_id = $mpProduct->getFirstItem()->getSellerId();
                        if($product->getGreenlineapiProductId() == ''){
                            $greenlineapiProductId = 0;
                            break;
                        }
                        $items[$item->getSku()] = array(
                            'productId'     => $product->getGreenlineapiProductId(),
                            'quantity'      => (int) $item->getQtyOrdered(),
                            'discountAmount' => $item->getDiscountAmount()*100,
                            'discountMessage' => $discountMessage,
                            'depositFee'    => $item->getRowTotal()*100,
                            'pricePerUnit'  => $item->getPrice()*100,
                            'taxes'         => [array('taxId' =>  1,'amount'=> $item->getTaxAmount()*100)]
                            
                        );
                    }
                    //}
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

            $items = array_values($items);
            
            //$orderComment = $order->getOrderComment();
            $orderComment = 'Aflora Order';
            $savedAddress = $this->helper->getSavedAddress();
            $service_type = 'delivery';
            if(sizeof($savedAddress) > 0) {
                if($savedAddress['service_type'] == 2){
                    $service_type = 'pickup';
                }
            }
            
            //$fullName = $this->checkoutSession->getPosCustomerName();

            $orderData = array(
                'orderId'       => (int) $incrementId,
                'customerName'  => $fullName,
                'notes'         => $orderComment,
                'products'      => $items,
                'fees'          => [],
                'orderType'     => $service_type,
                'total'         => $order->getGrandTotal()*100, 
                'isPaid'        => true
            );

            $orderJson = json_encode($orderData, JSON_NUMERIC_CHECK);

            $response = $this->pushOrder($seller_id, $orderJson);

            /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/orderExport.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

            $logger->info("Export Order Id". $incrementId);
            $logger->info("Export Order Response ". print_r($response, true));*/
            
            $insertData = [];
            $insertData['order_id']       = $orderId;
            if(sizeof($response) > 1){
                //$insertData['increment_id'] = $response['externalOrderId'];
                $insertData['increment_id']   = $incrementId;
                $insertData['parked_sale_id'] = $response['id'];
                $insertData['seller_id']      = $seller_id;
                $insertData['customer_name']  = $response['customerName'];
                $insertData['status']         = $response['status'];
                $insertData['order_type']     = $response['orderType'];
                $insertData['is_paid']        = (int) $response['isPaid'];
                $insertData['response']       = json_encode($response);
                $insertData['message']        = 'success';
            }else{
                $insertData['increment_id']   = $incrementId;
                $insertData['seller_id']      = $seller_id;
                $insertData['response']       = json_encode($response);
                $insertData['message']        = $response['message'];
            }

            $historyFactory = $this->_orderExportHistoryFactory->create();
            $historyFactory->setData($insertData);
            $historyFactory->save();

            //$this->checkoutSession->unsPosCustomerName();
        }

        return $this;
    }

    public function pushOrder($sellerId, $orderJson) {
        
        $dataArray = [];
        $apiUrl = trim($this->_moduleHelper->getApiUrl());
        $apiUrl = trim($apiUrl, '/');
        $apiKey = trim($this->_moduleHelper->getApiKey($sellerId));
        $companyId = trim($this->_moduleHelper->getCompanyId($sellerId));
        $locationId = trim($this->_moduleHelper->getLocationId($sellerId));

        $curl_base_url = $apiUrl . '/api/v3/external/companies/'.$companyId.'/locations/'.$locationId;
        $prevalidationUrl = $curl_base_url.'/payment-queues/pre-validation';
        $signature = '';
        $response = '';

        $headers = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $prevalidationUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $orderJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                    'api-key: '.$apiKey,
                                                    'Content-Type: application/json'
                                                  ));
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );

        $response = curl_exec($ch);

        if(sizeof($headers) > 0){
            if(array_key_exists('signature', $headers)){
                $signature = $headers['signature'][0];
            }
        }
        
        if($signature != ''){
            $paymentQueuesUrl = $curl_base_url.'/payment-queues';
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $paymentQueuesUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $response,
                CURLOPT_HTTPHEADER => array(
                    'signature: '.$signature,
                    'api-key: '.$apiKey,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
        }

        $dataArray = array();
        if($response != '' && $this->_moduleHelper->isJSON($response)){
            $dataArray = json_decode($response, true);
            //echo "<pre>"; print_r($dataArray);die;
        }
        
        return $dataArray;
    }
    
    public function getOrderCustomerId($customerEmail) {

        $customerId = 0;
        $connection = $this->_resource->getConnection();
        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $table = $connection->getTableName($tablePrefix . 'customer_entity');

        $coll = array();
        $query = "SELECT * FROM `" . $table . "` AS maintable WHERE maintable.`email` LIKE '" . $customerEmail . "'";
        $coll = $connection->fetchAll($query);
        if (sizeof($coll) > 0) {
            $coll = array_shift($coll);
            $customerId = $coll['entity_id'];
        }
        return $customerId;
    }

}
