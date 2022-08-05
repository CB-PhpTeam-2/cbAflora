<?php

namespace Paysafe\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Paysafe\Payment\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;
use Paysafe\PaysafeApiClient;
use Paysafe\Environment;
use Paysafe\CardPayments\Authorization;


class PaysafeClient
{
    /** @var Data */
    private $helper;

    private $checkoutSession;

    private $_resource;

    private $client = null;

    public function __construct(
        Data $helper,
        EncryptorInterface $encryptor,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\ResourceConnection $resource
    ){
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->checkoutSession = $checkoutSession;
        $this->_resource = $resource;
    }

    public function getClient()
    {
        $connection = $this->_resource->getConnection();
        $table = $connection->getTableName('marketplace_product');

        $sellerId = '';
        $allVisibleItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($allVisibleItems as $item) {
            $productId = $item->getProductId();
            $collection = [];
            $query = "SELECT * FROM `".$table."` WHERE `mageproduct_id` =".$productId;
            $collection = $connection->fetchAll($query);
            if(sizeof($collection) > 0){
                $sellerId = $collection[0]['seller_id'];
                break;
            }
        }
        
        $paysafeApiKeyId = '';
        $paysafeApiKeySecret = '';
        $paysafeAccountNumber = '';

        $collection = [];
        if($sellerId != ''){
            $table1 = $connection->getTableName('customer_entity');
            $query = "SELECT * FROM `".$table1."` WHERE `entity_id` =".$sellerId;
            $collection = $connection->fetchAll($query);

            if(sizeof($collection) > 0){
                $collection = $collection[0];
                $paysafeApiKeyId = $collection['paysafe_api_key'];
                $paysafeApiKeySecret = $this->encryptor->decrypt($collection['paysafe_api_secret_key']);
                $paysafeAccountNumber = $collection['paysafe_account_number'];
            }
        }

        
        if ($this->client === null) {
            //$paysafeApiKeyId = $this->helper->getAPIUsername();
            //$paysafeApiKeySecret = $this->helper->getAPIPassword();
            //$paysafeAccountNumber = $this->helper->getAccountNumber();
            $mode = $this->helper->isTestMode() ? Environment::TEST : Environment::LIVE;
            try {
                $client = new PaysafeApiClient($paysafeApiKeyId, $paysafeApiKeySecret, $mode, $paysafeAccountNumber);
                $this->client = $client;
            } catch (\Exception $e) {
                throw new LocalizedException(__('Can not instance Paysafe SDK'));
            }
        }

        return $this->client;
    }
}