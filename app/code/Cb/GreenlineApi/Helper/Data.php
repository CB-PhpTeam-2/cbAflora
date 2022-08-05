<?php
namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Cb\GreenlineApi\Model\GreenlineCredentialFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Magento\Framework\App\Request\DataPersistorInterface;

class Data extends AbstractHelper {

	const Cb_GreenlineApi_XML_PATH_EXTENSIONS = 'greenlineapi/general_settings/';
    const GREENLINEAPI_IMPORT_HISTORY_TABLE = 'greenlineapi_import_history';
    const GREENLINEAPI_IMPORT_PRODUCT_TABLE = 'greenlineapi_import_product_history';
    const GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE = 'greenlineapi_seller_history';

	protected $scopeConfig;
	protected $_objectManager;
    protected $_storeManager;
    protected $_resource;
    protected $encryptor;
    protected $_websiteCollectionFactory;
    protected $_productRepository;
    protected $_mpProductCollectionFactory;
    protected $greenlineCredentialFactory;
    protected $_mpHelper;
    protected $dataPersistor;
	
	public function __construct(
		\Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        EncryptorInterface $encryptor,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        GreenlineCredentialFactory $greenlineCredentialFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        MpProductCollection $mpProductCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        DataPersistorInterface $dataPersistor
	){
        $this->scopeConfig = $context->getScopeConfig();
		$this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->encryptor = $encryptor;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->greenlineCredentialFactory = $greenlineCredentialFactory;
        $this->_productRepository = $productRepository;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->productFactory = $productFactory;
        $this->_mpHelper = $mpHelper;
        $this->dataPersistor = $dataPersistor;
    }

    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::Cb_GreenlineApi_XML_PATH_EXTENSIONS .$path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCustomModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function chkIsModuleEnable(){

    	return $this->getModuleConfig('enable');
    }

    public function getApiUrl() {
        return $this->getModuleConfig('api_url');
    }

    public function getCompanyId($sellerId) {
        return $this->getSellerGreenlineCredential($sellerId)->getData('company_id');
    }

    public function getLocationId($sellerId) {
        return $this->getSellerGreenlineCredential($sellerId)->getData('location_id');
    }

    public function getApiKey($sellerId) {
        //$apiKey = $this->getSellerGreenlineCredential($sellerId)->getData('api_key');
        //return $this->encryptor->decrypt($apiKey);
		return $this->getModuleConfig('api_key');
    }

    public function allowSellerProductEdit(){
        return (int) $this->getCustomModuleConfig('marketplace/general_settings/edit_product_list_allow');
    }

    public function getTablePrefix(){
        $deploymentConfig = $this->_objectManager->get('Magento\Framework\App\DeploymentConfig');
        return $deploymentConfig->get('db/table_prefix');
    }

    public function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public function getWebsiteCollection()
    {
        $collection = $this->_websiteCollectionFactory->create();
        return $collection;
    }

    public function getAttributeId($attributeCode, $entityTypeId) {     

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $table = $connection->getTableName($tablePrefix.'eav_attribute');

        $query = "SELECT * FROM `".$table."` WHERE `entity_type_id` = $entityTypeId AND `attribute_code` LIKE '$attributeCode'";
         $collection = $connection->fetchAll($query);

        $attributeId = '';
        if(sizeof($collection) > 0){
            $collection = array_shift($collection);
            $attributeId = $collection['attribute_id'];
        }
        return $attributeId;
    }

    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    public function getSellerGreenlineCredential($sellerId)
    {
        $sellerCredential = $this->greenlineCredentialFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->setPageSize(1)->getFirstItem();
        return $sellerCredential;
    }

    public function getIsGreenlineEnable($sellerId)
    {
        $sellerCredential = $this->greenlineCredentialFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->setPageSize(1)->getFirstItem();
        return $sellerCredential->getStatus();
    }

    public function getItemSize() {
        return $this->getModuleConfig('itemsize');
    }


    public function insertGreenlineSellerHistory(){

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();

        $table = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE);

        $sellerList = $this->_mpHelper->getSellerList();
        $response = [];
        foreach ($sellerList as $key => $seller) {
            if($seller['value'] != ''){
                $seller_id = $seller['value'];

                $sellerCredential = $this->greenlineCredentialFactory->create()->getCollection()->addFieldToFilter('seller_id', $seller_id);

                $isGreenlineApiActive = 0;
                if($sellerCredential->getSize() > 0){
                    $isGreenlineApiActive = (int) $sellerCredential->getFirstItem()->getStatus();
                }

                if($isGreenlineApiActive){
                    $query = "SELECT * FROM `".$table."` WHERE `seller_id` = '$seller_id'";
                    $collection = $connection->fetchAll($query);

                    if(sizeof($collection) < 1){
                        $tableData = [];
                        $tableData[] = [$seller_id, 0, 0];
                        $connection->insertArray($table ,['seller_id', 'status', 'inventory_status'], $tableData);
                    }
                }
            }
        }
        return $this;
    }

    public function getNeedToUpdateSeller(){
    
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();

        $table = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE);

        $importHistory_table = $connection->getTableName($tablePrefix.'greenlineapi_import_history');

        $query = "SELECT *, `import_history`.offset, `import_history`.last_offset FROM `".$table."` As `seller_history` LEFT JOIN `".$importHistory_table."` As `import_history` ON `seller_history`.seller_id = `import_history`.seller_id WHERE `import_history`.offset != `import_history`.last_offset";
        $collection = [];
        $collection = $connection->fetchAll($query);

        $seller_id = '';
        if(sizeof($collection) > 0){
            $seller_id = $collection[0]['seller_id'];
        }else{
            /*$sellercron_table = $connection->getTableName($tablePrefix.'greenlineapi_sellercron_allow');

            $query = "SELECT * FROM `".$sellercron_table."` WHERE `allow` = '1' ORDER BY `updated_at` ASC";
            $collection = [];
            $collection = $connection->fetchAll($query);

            if(sizeof($collection) > 0){
                $seller_id = $collection[0]['seller_id'];
            }else{

                $query = "SELECT * FROM `".$table."` WHERE `status` IS NULL OR `status` = '0'";
                $_collection = [];
                $_collection = $connection->fetchAll($query);

                if(sizeof($_collection) > 0){
                    $seller_id = $_collection[0]['seller_id'];
                }
            }*/
			
			$query = "SELECT * FROM `".$table."` WHERE `status` IS NULL OR `status` = '0'";
			$_collection = [];
			$_collection = $connection->fetchAll($query);

			if(sizeof($_collection) > 0){
				$seller_id = $_collection[0]['seller_id'];
			}else{
				$_query= "UPDATE `".$table."` SET `status` = '0'  WHERE 1";
				$connection->query($_query);
				$collec = [];
				$collec = $connection->fetchAll($query);
                if(sizeof($collec) > 0){
                    $seller_id = $collec[0]['seller_id'];
                }
			}
        }
        return (int) $seller_id;
    }

    public function getNeedToUpdateSellerInventoty(){
    
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();

        $table = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE);

        $query = "SELECT * FROM `".$table."` WHERE `inventory_status` IS NULL OR `inventory_status` = '0'";
        $_collection = [];
        $_collection = $connection->fetchAll($query);

        $seller_id = '';
        if(sizeof($_collection) > 0){
            $seller_id = $_collection[0]['seller_id'];
        }else{
            $_query = "UPDATE `".$table."` SET `inventory_status` = '0' WHERE 1";
            $connection->query($_query);
            $collection = [];
            $collection = $connection->fetchAll($query);
            if(sizeof($collection) > 0){
                $collection = array_shift($collection);
                $seller_id = $collection['seller_id'];
            }
        }
        return (int) $seller_id;
    }

    public function getCurlResponse($serviceName, $sellerId){
        $apiUrl = trim($this->getApiUrl());
        $apiUrl = trim($apiUrl, '/');
        $apiKey = trim($this->getApiKey($sellerId));
        $companyId = trim($this->getCompanyId($sellerId));
        $locationId = trim($this->getLocationId($sellerId));

        $itemSize = (int) trim($this->getItemSize());
        $offset = $this->getOffset($serviceName, $sellerId);
        $lastOffset = $this->getLastOffset($serviceName, $sellerId);

        if($lastOffset != 0 && $offset == $lastOffset){
            return $this;
        }

        $curl_url = '';
        if($serviceName == "Products"){
            $curl_url = $apiUrl.'/api/v1/external/company/'.$companyId.'/location/'.$locationId.'/products?limit='.$itemSize.'&offset='.$offset;
        }
        
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
        if($response != '' && $this->isJSON($response)){   
            $dataArray = json_decode($response, true);
            if(is_array($dataArray)){
                $dataArray = array_filter($dataArray);
            }
            //echo "<pre>"; print_r($dataArray);die('Hi');
        }

        if($offset == 0 && array_key_exists('products', $dataArray) && sizeof($dataArray['products']) > 0){
            $total = (int) sizeof($dataArray['products']);
            $last_offset = $total - 1;

            $connection  = $this->_resource->getConnection();
            $tablePrefix = $this->getTablePrefix();
            $greenlineImportSchema = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_HISTORY_TABLE);
            $query = "UPDATE `".$greenlineImportSchema."` SET `last_offset` = '$last_offset', `updated_at` = CURRENT_TIMESTAMP WHERE `".$greenlineImportSchema."`.`seller_id` = '$sellerId'";
            $connection->query($query);

            if($serviceName == "Products"){
                $table = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_PRODUCT_TABLE);
                $is_updated = 0;

                $query= "UPDATE `".$table."` SET `is_updated` = '$is_updated' WHERE `seller_id` = ".$sellerId;
                $connection->query($query);

                /*$table = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE);

                $query = "SELECT * FROM `".$table."` WHERE `status` = '1'";
                $coll = [];
                $coll = $connection->fetchAll($query);
                if(sizeof($coll) > 0) {
                    $status = 0;
                    $query= "UPDATE `".$table."` SET `status` = '$status' WHERE 1";
                    $connection->query($query);
                }*/
            }
        }

        $collection = array();
        if(sizeof($dataArray['products']) && array_key_exists('products', $dataArray)){
            $collection = $dataArray['products'];
        }

        return $collection;
    }

    public function getInventoryCurlResponse($sellerId){

        $apiUrl = trim($this->getApiUrl());
        $apiUrl = trim($apiUrl, '/');
        $apiKey = trim($this->getApiKey($sellerId));
        $companyId = trim($this->getCompanyId($sellerId));
        $locationId = trim($this->getLocationId($sellerId));
        
        $curl_url = $apiUrl.'/api/v1/external/company/'.$companyId.'/location/'.$locationId.'/posListings/inventory';

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
        if($response != '' && $this->isJSON($response)){   
            $dataArray = json_decode($response, true);
            if(is_array($dataArray)){
                $dataArray = array_filter($dataArray);
            }
            //echo "<pre>"; print_r($dataArray);die('Hi');
        }

        $collection = array();
        if(sizeof($dataArray['data']) && array_key_exists('data', $dataArray)){
            $collection = $dataArray['data'];
        }

        return $collection;
    }
    
    public function getOffset($serviceName, $sellerId){
        $offset = 0;
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $greenlineImportSchema = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_HISTORY_TABLE);
        $coll = array();
        $query = "SELECT * FROM `".$greenlineImportSchema."` WHERE `seller_id` = '".$sellerId."'";
        $coll = $connection->fetchAll($query);
        if(sizeof($coll) > 0) {
            $coll = array_shift($coll);
            $offset = $coll['offset'];
            $lastOffset = $coll['last_offset'];
            if($offset == $lastOffset){
                $offset = 0;
                $query = "UPDATE `".$greenlineImportSchema."` SET `offset` = '$offset', `updated_at` = CURRENT_TIMESTAMP WHERE `".$greenlineImportSchema."`.`seller_id` = '$sellerId'";
                $connection->query($query);
            }
        }else{
            $response = array();
            $response['seller_id'] = $sellerId;
            $response['offset'] = $offset;
            $response['last_offset'] = 0;

            $tableColumn = array();
            $tableData = array();
            $tableColumn = array_keys($response);
            $tableData[] = array_values($response);
            $connection->insertArray($greenlineImportSchema ,$tableColumn, $tableData);
        }
        return $offset;
    }

    public function getLastOffset($serviceName, $sellerId){
        $lastOffset = '';
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $greenlineImportSchema = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_HISTORY_TABLE);
        $coll = array();
        $query = "SELECT * FROM `".$greenlineImportSchema."` WHERE `seller_id` = '".$sellerId."'";
        $coll = $connection->fetchAll($query);
        if(sizeof($coll) > 0) {
            $coll = array_shift($coll);
            $lastOffset = $coll['last_offset'];
        }
        return $lastOffset;
    }

    public function setOffset($serviceName, $sellerId){

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $greenlineImportSchema = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_HISTORY_TABLE);

        $query = "SELECT * FROM `".$greenlineImportSchema."` WHERE `seller_id` = '".$sellerId."'";
        $coll = $connection->fetchAll($query);
        if(sizeof($coll) > 0) {
            $coll = array_shift($coll);
            $offset = (int) $coll['offset'];
            $lastOffset = (int) $coll['last_offset'];
            
            $itemSize = (int) trim($this->getItemSize());
            
            $offset = $offset + $itemSize;
            if($offset > $lastOffset){
                $offset = $lastOffset;
            }
            $updateQuery = "UPDATE `".$greenlineImportSchema."` SET `offset` = '$offset', `updated_at` = CURRENT_TIMESTAMP WHERE `".$greenlineImportSchema."`.`seller_id` = '$sellerId'";
            $connection->query($updateQuery);

            if($offset == $lastOffset){
                $sellerHistoryTable = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE);

                $query= "UPDATE `".$sellerHistoryTable."` SET `status` = '1' WHERE `seller_id` = ".$sellerId;
                $connection->query($query);

                $sellercron_table = $connection->getTableName($tablePrefix.'greenlineapi_sellercron_allow');

                $query= "UPDATE `".$sellercron_table."` SET `allow` = '0' WHERE `seller_id` = ".$sellerId;
                $connection->query($query);
            }
        }
        
        return $this;
    }

    public function getDefaultSellerLabel(){
        return 'Aflora Default';
    }

    public function getDefaultSellerId(){

        $defaultSellerId = 0;
        $sellerList = $this->_mpHelper->getSellerList();
        $defaultSellerLabel = $this->getDefaultSellerLabel();
        foreach ($sellerList as $key => $seller) {
            if($seller['value'] != ''){
                $label = trim($seller['label']);
                if(strtolower($label) == strtolower($defaultSellerLabel)){
                    $defaultSellerId = $seller['value'];
                }
            }
        }

        return $defaultSellerId;
    }

    public function canImportThisProduct($barcode){

        $defaultSellerId = $this->getDefaultSellerId();

        $attribute_id = $this->getAttributeId('barcode', 4);
        $_barcode = "'".$barcode."'";
        $collection = $this->_mpProductCollectionFactory->create();

        $collection->getSelect()->joinLeft(
             ['sup_table' => 'catalog_product_entity_varchar'],
             'main_table.mageproduct_id = sup_table.entity_id AND sup_table.attribute_id = '.$attribute_id,
             ['sup_table.value']
            )->where("main_table.seller_id = ".$defaultSellerId." AND sup_table.value = ".$_barcode);
        
        if($collection->getSize() > 0){
            return $collection->getFirstItem()->getData('mageproduct_id');
        }
        return 0;
    }

    public function getSellerUrlBySellerId($sellerId)
    {
        $shop_url = '';
        /*$seller = $this->_mpHelper->getSellerDataBySellerId($sellerId);
        if($seller->getSize() > 0){
            $shop_url = $seller->getFirstItem()->getData('shop_url');
        }*/

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $marketplaceUserdata = $connection->getTableName($tablePrefix.'marketplace_userdata');

        $query = "SELECT * FROM `".$marketplaceUserdata."` WHERE `seller_id` = '".$sellerId."' AND `store_id` = '0'";
        $coll = [];
        $coll = $connection->fetchAll($query);
        if(sizeof($coll) > 0) {
            $shop_url = $coll[0]['shop_url'];
        }

        return $shop_url;
    }

     /**
     * Pass here attribute code and option label as param
     */
    public function getAttrOptIdByLabel($attrCode,$optLabel)
    {
        $product = $this->productFactory->create();
        $isAttrExist = $product->getResource()->getAttribute($attrCode);
        $optId = '';
        if ($isAttrExist && $isAttrExist->usesSource()) {
            $optId = $isAttrExist->getSource()->getOptionId($optLabel);
        }
        return $optId;
    }

    /**
     * Get Product
     *
     * @param int $productId [optional]
     *
     * @return object
     */
    public function getProduct($productId = 0)
    {
        if ($productId == 0) {
            $productId = $this->getProductId();
        }
        $product = $this->productFactory->create()->load($productId);
        return $product;
    }

    public function getPersistentData()
    {
        $partner = $this->_mpHelper->getSeller();
        $persistentData = (array)$this->dataPersistor->get('seller_profile_data');
        foreach ($partner as $key => $value) {
            if (empty($persistentData[$key])) {
                $persistentData[$key] = $value;
            }
        }
        $this->dataPersistor->clear('seller_profile_data');

        if (empty($persistentData)) {
            $persistentData = $this->_mpHelper->getSeller();
        }

        return $persistentData;
    }

}
