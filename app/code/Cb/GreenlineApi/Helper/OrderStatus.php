<?php

namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\CustomerFactory;

class OrderStatus extends AbstractHelper {

    protected $_moduleHelper;
    protected $_invoiceHelper;
    protected $_shipmentHelper;
    protected $_cancelOrderHelper;
    protected $_orderModel;
    protected $_orderExportHistoryFactory;

    public function __construct(
    \Magento\Framework\View\Element\Context $context,
    \Cb\GreenlineApi\Helper\Data $moduleHelper,
    \Cb\GreenlineApi\Helper\CreateInvoice $invoiceHelper,
    \Cb\GreenlineApi\Helper\CreateShipment $shipmentHelper,
    \Cb\GreenlineApi\Helper\CancelOrder $cancelOrderHelper,
    \Magento\Sales\Model\OrderFactory $orderModel,
    \Cb\GreenlineApi\Model\OrderExportHistoryFactory $orderExportHistoryFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_moduleHelper = $moduleHelper;
        $this->_invoiceHelper = $invoiceHelper;
        $this->_shipmentHelper = $shipmentHelper;
        $this->_cancelOrderHelper = $cancelOrderHelper;
        $this->_orderModel = $orderModel;
        $this->_orderExportHistoryFactory = $orderExportHistoryFactory;
    }

    public function OrderStatusArray() {
        $statusArr = [
                        'pending' => 'pending',
                        'processing' => 'ready for pickup',
                        'complete' => 'completed',
                        'canceled' => 'cancelled'
                    ];
        return $statusArr;
    }

    public function updateOrderStatus() {

        if ($this->_moduleHelper->chkIsModuleEnable()) {
            $orderStatusArray = $this->OrderStatusArray();

            $parkedSales = $this->_orderExportHistoryFactory->create()->getCollection()->addFieldToFilter('status', ['neq' => 'completed']);
            //echo "<pre>"; print_r($parkedSales->getData());die;

            foreach($parkedSales as $key => $parkedSale){
                $sellerId = $parkedSale->getSellerId();
                $response = $this->getParkedSaleStatus($parkedSale->getParkedSaleId(), $sellerId);
                $status = '';
                if(array_key_exists("status", $response)){
                    $status = $response['status'];
                }
                if($status != '' && $parkedSale->getStatus() != $status){

                    if(in_array($status, $orderStatusArray)){
                        $orderId = $parkedSale['order_id'];
                        if($status == 'ready for pickup'){
                            $this->_invoiceHelper->generateInvoice($orderId, $sellerId);
                        }

                        if($status == 'completed'){
                            $this->_shipmentHelper->generateShipment($orderId, $sellerId);
                        }

                        if($status == 'cancelled'){
                            $this->_cancelOrderHelper->cancelOrder($orderId, $sellerId);
                        }
                    }

                    $parkedSale->setData('is_paid', 1);
                    $parkedSale->setData('status', $status);
                    if($status == 'completed'){
                        $greenline_sale_id = $response['completedPayment']['customId'];
                        $parkedSale->setData('greenline_sale_id', $greenline_sale_id);
                    }
                    $parkedSale->save();
                }
            }
        }

        return $this;
    }

    public function getParkedSaleStatus($parkedSaleId, $sellerId) {

        $dataArray = [];
        $apiUrl = trim($this->_moduleHelper->getApiUrl());
        $apiUrl = trim($apiUrl, '/');
        $apiKey = trim($this->_moduleHelper->getApiKey($sellerId));
        $companyId = trim($this->_moduleHelper->getCompanyId($sellerId));
        $locationId = trim($this->_moduleHelper->getLocationId($sellerId));


        $url = $apiUrl . '/api/v1/external/company/'.$companyId.'/location/'.$locationId.'/paymentQueues/'.$parkedSaleId;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'api-key: '.$apiKey,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $dataArray = array();
        $status = '';
        if($response != '' && $this->_moduleHelper->isJSON($response)){
            $dataArray = json_decode($response, true);
            /*if(sizeof($dataArray) > 0){
                if(array_key_exists("status", $dataArray)){
                    $status = $dataArray['status'];
                }
            }*/
        }
        
        return $dataArray;
    }

}
