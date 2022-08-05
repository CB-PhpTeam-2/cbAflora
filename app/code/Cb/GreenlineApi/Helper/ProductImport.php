<?php
namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class ProductImport extends AbstractHelper {

    const GREENLINEAPI_IMPORT_PRODUCT_TABLE = 'greenlineapi_import_product_history';
    const GREENLINEAPI_PRODUCTS_CATEGORY_ID = 2;

    protected $scopeConfig;
    protected $_objectManager;
    protected $_storeManager;
    protected $_resource;
    protected $productCopier;
    protected $stockRegistry;
    protected $productTierPriceFactory;
    protected $_productHistoryFactory;
    protected $_moduleHelper;
    protected $file;
    protected $_dir;
    protected $logger;
    
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        \Magento\Framework\App\ResourceConnection $resource,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Cb\GreenlineApi\Model\ProductHistoryFactory $productHistoryFactory,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        MpProductFactory $mpProductFactory,
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
		$this->_eventManager = $eventManager;
        $this->indexFactory = $indexFactory;
        $this->indexCollection = $indexCollection;
        $this->_resource = $resource;
        $this->_stockRegistryProvider = $stockRegistryProvider;
        $this->_stockConfiguration = $stockConfiguration;
        $this->_stockStateInterface = $stockStateInterface;
        $this->_stockRegistry = $stockRegistry;
        $this->_productHistoryFactory = $productHistoryFactory;
        $this->productCopier = $productCopier;
        $this->_moduleHelper = $moduleHelper;
        $this->file = $file;
        $this->_dir = $dir;
        $this->_date = $date;
        $this->mpProductFactory = $mpProductFactory;
        $this->logger = $loggerInterface;
    }

    public function importProducts($collection,$serviceName, $sellerId) {
        //echo "<pre>"; print_r($collection);die;
        foreach ($collection as $key => $value) {
            
            $barcode = '';
            if(array_key_exists('barcode', $value)){
                $barcode = trim($value['barcode']);
            }

            if($barcode == ''){
                if(array_key_exists('variants', $value) && sizeof($value['variants']) > 0){
                    foreach ($value['variants'] as $subkey => $subValue) {
                        $variant_barcode = trim($subValue['barcode']);
                        if($variant_barcode != ''){
                            $this->prepareData($subValue, $sellerId);
                            $this->setIsUpdated($subValue, $sellerId);
                        }
                    }
                }
            }else{
                $this->prepareData($value, $sellerId);
                $this->setIsUpdated($value, $sellerId);
            }
        }
        $this->_moduleHelper->setOffset($serviceName, $sellerId);
		
		$indexerCollection = $this->indexCollection->create();
		$indexids = $indexerCollection->getAllIds();
	 
		foreach ($indexids as $indexid){
			$indexidarray = $this->indexFactory->create()->load($indexid);
	 
			//If you want reindex all use this code.
			 $indexidarray->reindexAll($indexid);
	 
			//If you want to reindex one by one, use this code
			 $indexidarray->reindexRow($indexid);
		}
    }

    public function prepareData($value, $sellerId) {

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $greenlineapiImportHistorySchema = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_PRODUCT_TABLE);

        if($connection->isTableExists($greenlineapiImportHistorySchema) != true){
            echo "The product import process has been declined.!! ".'<br>';
            echo 'The table "'.self::GREENLINEAPI_IMPORT_PRODUCT_TABLE.'" does not exist. Please create the table first.!!';exit();
            return false;
        }

        $data = array();
        $coll = array();
        $response = array();

        $data['seller_id'] = $sellerId;
        $barcode = trim($value['barcode']);
        $data['barcode'] = $barcode;
        $_barcode = "'".$barcode."'";
        $query = "SELECT * FROM `".$greenlineapiImportHistorySchema."` WHERE barcode LIKE ".$_barcode." AND seller_id = ".$sellerId;
        $coll = $connection->fetchAll($query);

        $allow = 1;
        $existId = 0;
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');

        if(!$this->_moduleHelper->canImportThisProduct($barcode)){
            $allow = 0;
        }
        
        if(sizeof($coll) > 0) {
            $coll = array_shift($coll);
            $existId = $coll['id'];
        }

        if($allow == 1){
            $data['ocs_product_id'] = $this->_moduleHelper->canImportThisProduct($barcode);

            $productExist = 0;
            $shopUrl = $this->_moduleHelper->getSellerUrlBySellerId($sellerId);
            //$sku = trim($value['sku']);
            $sku = $barcode.'-'.$shopUrl;
            if($product->getIdBySku($sku)) {
                $productExist = $product->getIdBySku($sku);
            }

            $data['greenlineapi_product_id'] = trim($value['id']);
            $data['qty'] = $this->getInventoryStock($sellerId, $data['greenlineapi_product_id']);
            $data['name'] = trim($value['name']);
            $data['sku'] = $sku;

            $data['short_description'] = '';
            if (array_key_exists('short_description', $value)) {
                $data['short_description'] = trim($value['short_description']);
            }

            $data['description'] = '';
            if (array_key_exists('description', $value)) {
                $data['description'] = trim($value['description']);
            }
            $value['price'] = trim($value['price']);
			$data['price'] = 0;
			if($value['price'] != '' && $value['price'] > 0){
				$data['price'] = $value['price'] / 100;
			}
            $data['weight'] = trim($value['weight']);
            
            $data['thc_low'] = '';
            $data['thc_high'] = '';
            $data['cbd_low'] = '';
            $data['cbd_high'] = '';
            $data['uom'] = '';
            if (array_key_exists('metaData', $value) && sizeof($value['metaData']) > 0) {
                $metaData = $value['metaData'];
                $data['thc_low'] = trim($metaData['minTHC']);
                $data['thc_high'] = trim($metaData['maxTHC']);
                $data['cbd_low'] = trim($metaData['minCBD']);
                $data['cbd_high'] = trim($metaData['maxCBD']);
                $data['uom'] = trim($metaData['unit']);
            }
            $data['brands'] = trim($value['supplierName']);

            $data['image'] = '';
            if (array_key_exists('imageUrl', $value)) {
                $data['image'] = trim($value['imageUrl']);
            }

            $data['categoryName'] = trim($value['categoryName']);
            $data['subcategoryName'] = '';
            $data['assign_to_category'] = self::GREENLINEAPI_PRODUCTS_CATEGORY_ID;
            
            $response = $this->insertData($data,$productExist);
            
            if(is_array($response)){
                if(sizeof($response) > 0){      
                    $json_data = json_encode($value);
                    $response['json_data'] = $json_data;
                    if($existId == 0){
                        $historyFactory = $this->_productHistoryFactory->create();
                        $historyFactory->setData($response);
                        $historyFactory->save();
                    }else{  
                        $update_at = date('Y-m-d H:i:s', time());
                        $historyFactory = $this->_productHistoryFactory->create()->load($existId);
                        $historyFactory->setData('updated_at', $update_at);
                        $historyFactory->setData('json_data', $json_data);
                        $historyFactory->save();
                    }
                    //return $response;
                    $response['status'] = 'success';
                    $response['message'] = 'Successfully updated';
                }
                //die('1 product updated');
            }
        }else{
            $defaultSellerLabel= $this->_moduleHelper->getDefaultSellerLabel();
            $response['status'] = 'error';
            $response['message'] = 'Unfortunately not  imported because barcode "'.$barcode.'" does not exist in "'.$defaultSellerLabel.'" Seller';
        }
                            
        return $response;
    }

    public function insertData($data,$productExist) {
        
        $writer1 = new \Zend\Log\Writer\Stream(BP . '/var/log/prodimage.log');
        $logger1 = new \Zend\Log\Logger();
        $logger1->addWriter($writer1);

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $result = array();
        $currentStore = $this->_storeManager->getStore();
        $websiteIds = array_keys($this->_storeManager->getWebsites());
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        //$product->setStoreId(0);
        $ocs_product_id = $data['ocs_product_id'];
        if($productExist){
            $product->load($productExist);
        }else{
            $_product = $this->_moduleHelper->getProduct($ocs_product_id);
            $product = $this->productCopier->copy($_product);

            $product->setStatus(Status::STATUS_ENABLED);
            $product->setSku($data['sku']);
        }
        
        /*$categoryIds = array();
        if($data['categoryName'] != ''){
          $categoryIds = $this->getCategoryId($data);
        }else{
            $categoryIds[] = 2;
        }

        if(!$productExist){
            if($data['name'] == ""){
                $data['name'] = $data['sku'];
            }

            $urlKey = $this->createUrlKey($data['name'],$data['sku']);

            $product->setAttributeSetId(4); // Set Attribute Set ID  
            $product->setTypeId('simple'); // Set Product Type Id
            $product->setVisibility(4);
            $product->setUrlKey($urlKey);
            $product->setPriceType(0);
            $product->setPriceView(0);
            $product->setStatus(Status::STATUS_ENABLED);
        }

        $urls = [];
        if($data['image'] != ''){
            $urls[] = $data['image'];
        }

        $validImageUrls = [];
        if(sizeof($urls) > 0 ){
            $sku = $data['sku'];
            $imageProcessor = $this->_objectManager->create('\Magento\Catalog\Model\Product\Gallery\Processor');
            $images = $product->getMediaGalleryImages();
            // if($images->getSize() > 0){
            //     foreach ($images as $image) {
            //         if($image->getFile() != ""){
            //             $imageProcessor->removeImage($product, $image->getFile());  
            //         }
            //     }   
            // }
            foreach ($urls as $key => $url) {
                $validStatus = $this->isValidImageUrl($url, $sku);
                if($url != '' && basename($url) != '' && $validStatus == 1){
                    $validImageUrls[] = $url;
                }
            }

            if($images->getSize() < 1){
                foreach ($urls as $key => $url) {
                    $validStatus = $this->isValidImageUrl($url, $sku);
                    if($url != '' && basename($url) != '' && $validStatus == 1){
                        $tmpDir = $this->_dir->getRoot().'/pub/media/uploads/product_images';
                        $existDir = $this->_dir->getRoot().'/uploads/product_images';

                        //if(!$productExist || sizeof($exceptImages) < 1){
                            if ( ! file_exists($existDir)) {
                                $this->file->mkdir($existDir);
                            }
                            if ( ! file_exists($tmpDir)) {
                                $this->file->mkdir($tmpDir);
                            }
                            //$imageName = time().'-'.basename($url);
                            $imgPrefix = strtolower($sku);
                            $imgPrefix = str_replace(" ","_",$imgPrefix);
                            //$imageName = $imgPrefix.'-'.basename($url);
                            $filename = pathinfo($url, PATHINFO_FILENAME);
                            $extension = pathinfo($url, PATHINFO_EXTENSION);
                            $unique = strlen($filename);
                            $unique = (int) $unique + $key;
                            $imageName= strtolower($imgPrefix.'-'.$filename.'_'.$unique).'.'.$extension;

                            $imageUrl = $existDir.DIRECTORY_SEPARATOR.$imageName;
                            if (!file_exists($imageUrl)){
                                $content = file_get_contents($url);
                                file_put_contents($imageUrl, $content);
                            }

                            $newimageUrl = $tmpDir.DIRECTORY_SEPARATOR.$imageName;
                            if (file_exists($imageUrl)) {
                                copy($imageUrl, $newimageUrl);
                            }
                            
                            if (file_exists($newimageUrl)) {
                                if($key == 0){
                                    $product->addImageToMediaGallery($newimageUrl, array('image', 'small_image', 'thumbnail'), true, false);
                                }else{
                                    $product->addImageToMediaGallery($newimageUrl, array(), false, false);
                                }
                            }     
                        //}
                    }else{
                        $msg= 'The product SKU "'.$sku.'" has not valid image url '.$url;
                        $logger1->info($msg);
                    }
                }
            }

        }else{
            $msg= 'The product SKU "'.$sku.'" has not image url ';
            $logger1->info($msg);
        }
        
        $product->setWebsiteIds($websiteIds); // Set Website Ids

        $product->setStockData(
            array(
                'use_config_manage_stock' => 1,
                'manage_stock' => 1,
                'is_in_stock' => 1,
                'qty' => $data['qty']
            )
        );
        $product->setName($data['name']); // Set Product Nam
        $product->setDescription($data['description']);
        $product->setShortDescription($data['short_description']);
          
        $product->setCategoryIds($categoryIds); // Assign Category Ids

        $uomOptionId = '';
        if (str_contains($data['uom'], 'mg')) {
            $uomOptionId = $this->_moduleHelper->getAttrOptIdByLabel('uom','mg');
        }else{
            $uomOptionId = $this->_moduleHelper->getAttrOptIdByLabel('uom','%');
        }
        
        //$product->setSpecialPrice($data['specialPrice']);
        $product->setWeight($data['weight']);
        $product->setThcLow($data['thc_low']);
        $product->setThcHigh($data['thc_high']);
        $product->setCbdLow($data['cbd_low']);
        $product->setCbdHigh($data['cbd_high']);
        $product->setUom($uomOptionId);
        $product->setBarcode($data['barcode']);
        $product->setBrands($data['brands']);*/

        
        if($data['price'] != ''){
            $product->setPrice($data['price']);
        }
        $product->setGreenlineapiProductId($data['greenlineapi_product_id']);
        //$taxClassId = 0;
        //$product->setTaxClassId($taxClassId);
		
        
        //$product->setData('product_has_weight', 0);
		//$product->setWeight($data['weight']);
        // $product->setStockData(
        //     array(
        //         'use_config_manage_stock' => 1,
        //         'manage_stock' => 1,
        //         'is_in_stock' => 1,
        //         'qty' => $data['qty']
        //     )
        // );
        // $product->setStockData(['qty' => $data['qty'], 'is_in_stock' => $data['qty'] > 0]);
        //$product->setQuantityAndStockStatus(['qty' => $data['qty'], 'is_in_stock' => 1]);

        //$product->setStockData(['qty' => 10, 'is_in_stock' => 10 > 0]);

        try {
            $product->save();
            $productId = $product->getId();

        	// $stockItem = $this->_stockRegistry->getStockItemBySku($data['sku']);
         //    $stockItem->setQty($data['qty']);
         //    $stockItem->setIsInStock((bool)$data['qty']);
         //    $this->_stockRegistry->updateStockItemBySku($data['sku'], $stockItem);

			//$this->updateStockData($productId, $data['qty'], 1);

            $is_in_stock = 0;
            if($data['qty'] > 0){
                $is_in_stock = 1;
            }

            $cataloginventory_stock_item = $connection->getTableName($tablePrefix.'cataloginventory_stock_item');
            $cataloginventory_stock_status = $connection->getTableName($tablePrefix.'cataloginventory_stock_status');

            $query = "SELECT * FROM `".$cataloginventory_stock_item."` WHERE `product_id` = '$productId' LIMIT 1";
            $stock_item = [];
            $stock_item = $connection->fetchAll($query);
            if(sizeof($stock_item) < 1){
                $query = "SELECT * FROM `".$cataloginventory_stock_item."` WHERE `product_id` = '$ocs_product_id' LIMIT 1";
                $orid_stock_item = [];
                $orid_stock_item = $connection->fetchAll($query);

                if(sizeof($orid_stock_item) > 0){
                    $orid_stock_item = $orid_stock_item[0];
                    $orid_stock_item['item_id'] = '';
                    $orid_stock_item['product_id'] = $productId;
                    $orid_stock_item['is_in_stock'] = $is_in_stock;
                    $orid_stock_item['manage_stock'] = 1;
                    $orid_stock_item['qty'] = $data['qty'];
                    $orid_stock_item['max_sale_qty'] = $data['qty'];
                    $tablesColumns = [];
                    $tablesColumns = array_keys($orid_stock_item);
                    $tablesColumns = array_values($tablesColumns);
                    
                    $tableData = [];
                    $tableData[] = array_values($orid_stock_item);
                    $connection->insertArray($cataloginventory_stock_item ,$tablesColumns, $tableData);
                }

                $query = "SELECT * FROM `".$cataloginventory_stock_status."` WHERE `product_id` = '$ocs_product_id' LIMIT 1";
                $orid_stock_status = [];
                $orid_stock_status = $connection->fetchAll($query);

                if(sizeof($orid_stock_status) > 0){
                    $orid_stock_status = $orid_stock_status[0];
                    $orid_stock_status['stock_id'] = 1;
                    $orid_stock_status['product_id'] = $productId;
                    $orid_stock_status['stock_status'] = $is_in_stock;
                    $tablesColumns = [];
                    $tablesColumns = array_keys($orid_stock_status);
                    $tablesColumns = array_values($tablesColumns);
                    $tableData = [];
                    $tableData[] = array_values($orid_stock_status);
                    $connection->insertArray($cataloginventory_stock_status ,$tablesColumns, $tableData);
                }

            }else{
                $stock_item = $stock_item[0];
                $_data = [];
                $_data = ["stock_id"=>1,"is_in_stock"=>$is_in_stock,"manage_stock"=>1, "qty"=>$data['qty'], "max_sale_qty"=>$data['qty']];
                $item_id = $stock_item['item_id'];
                $where = ['item_id = ?' => (int)$item_id];
                $connection->update($cataloginventory_stock_item, $_data, $where);

                $query = "SELECT * FROM `".$cataloginventory_stock_status."` WHERE `product_id` = '$productId' LIMIT 1";
                $orid_stock_status = [];
                $orid_stock_status = $connection->fetchAll($query);

                if(sizeof($orid_stock_status) > 0){
                    $orid_stock_status = $orid_stock_status[0];
                    $_data = [];
                    $_data = ["stock_id"=>1,"qty"=>$data['qty'], "stock_status"=>$is_in_stock];
                    $product_id = $orid_stock_status['product_id'];
                    $where = ['product_id = ?' => (int)$product_id];
                    $connection->update($cataloginventory_stock_status, $_data, $where);
                }
            }


            /*------ Assign product to Seller ------*/

            $productCollection = $this->mpProductFactory->create()
                                ->getCollection()
                                ->addFieldToFilter(
                                    'mageproduct_id',
                                    $productId
                                );
            if($productCollection->getSize() < 1){                    
                $mpProductModel = $this->mpProductFactory->create();
                $mpProductModel->setMageproductId($productId);
                $mpProductModel->setSellerId($data['seller_id']);
                $mpProductModel->setStatus($product->getStatus());
                $mpProductModel->setAdminassign(1);
                $mpProductModel->setIsApproved(1);
                $mpProductModel->setCreatedAt($this->_date->gmtDate());
                $mpProductModel->setUpdatedAt($this->_date->gmtDate());
                $mpProductModel->save();
            }

            $product->cleanCache();
            $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $product]);
			
            $result['product_id'] = $product->getId();
            $result['seller_id'] = $data['seller_id'];
            $result['barcode'] = $data['barcode'];

        } catch (Exception $e) {echo $e->getMessage();die('dgde');
            $result['error'] = $e->getMessage();
            //die('die from product helper');
            //return true;
            $message = "The product sku ".$data['sku']." has error ".$e->getMessage();
            $this->logger->debug($message);
        }

        return $result;
    }
	
	public function updateStockData($productId, $qty = 0, $isInStock = 0)
    {
        try {
            $scopeId = $this->_stockConfiguration->getDefaultScopeId();
            $stockItem = $this->_stockRegistryProvider->getStockItem($productId, $scopeId);
            if ($stockItem->getItemId()) {
                $stockItem->setQty($qty);
                $stockItem->setIsInStock($isInStock);
                $stockItem->save();
            }
        } catch (\Exception $e) {
            /*$this->_marketplaceHelperData->logDataInLogger(
                "Controller_Product_SaveProduct updateStockData : ".$e->getMessage()
            );*/
        }
		return $this;
    }
	
    public function getCategoryId($data) {

        $imCategoryId = '';
        $categoryName = $data['categoryName'];
        $assignTo = $data['assign_to_category'];

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $table = $connection->getTableName($tablePrefix.'eav_attribute');

        $entityTypeId = 3;      
        $attributeCode = "name";

        $query = "SELECT * FROM `".$table."` WHERE `entity_type_id` = $entityTypeId AND `attribute_code` LIKE '$attributeCode'";
         $collection = $connection->fetchAll($query);

        $attributeId = '';
        if(sizeof($collection) > 0){
            $collection = array_shift($collection);
            $attributeId = $collection['attribute_id'];

            $table = $connection->getTableName($tablePrefix.'catalog_category_entity_varchar');
            $categoryName1 = addslashes($categoryName);
            $query = "SELECT `entity_id` FROM `".$table."` WHERE `value` LIKE '$categoryName1' AND `attribute_id` = $attributeId";
            $category = [];
            $category = $connection->fetchAll($query);
            
            $create = 1;
            if(sizeof($category) > 0){
                foreach ($category as $key => $value) {
                    $categoryId = $value['entity_id'];
                    $table = $connection->getTableName($tablePrefix.'catalog_category_entity');
                    $query = "SELECT `parent_id` FROM `".$table."` WHERE `entity_id` = $categoryId";
                    $parentId = $connection->fetchOne($query);

                    //if($parentId == $assignTo){
                    if($parentId != ''){
                        $imCategoryId = $categoryId;
                        $create = 0;
                        break;
                    }
                }
            }

            if($create == 1){ 
                $imCategoryId =  $this->createCategory($categoryName, $assignTo);
            }

            /* -------Start code  for subcategory -------- */
            /*if($imCategoryId != ""){
                $subcategoryName = $data['subcategoryName'];
                if($subcategoryName != ""){
                    $create = 1;
                    $table = $connection->getTableName($tablePrefix.'catalog_category_entity_varchar');
                    $subcategoryName1 = addslashes($subcategoryName);
                    $query = "SELECT `entity_id` FROM `".$table."` WHERE `value` LIKE '$subcategoryName1' AND `attribute_id` = $attributeId";
                    $category = [];
                    $category = $connection->fetchAll($query);
                    if(sizeof($category) > 0){
                        foreach ($category as $key => $value) {
                            $categoryId = $value['entity_id'];
                            $table = $connection->getTableName($tablePrefix.'catalog_category_entity');
                            $query = "SELECT `parent_id` FROM `".$table."` WHERE `entity_id` = $categoryId";
                            $parentId = $connection->fetchOne($query);

                            if($parentId == $imCategoryId){
                                $imCategoryId = $categoryId;
                                $create = 0;
                                break;
                            }
                        }
                    }
                    if($create == 1){
                        $this->createCategory($subcategoryName, $imCategoryId);
                    }
                }
            }*/
            /* -------End code  for subcategory -------- */
        }
        
        $categoryIds = [];
        $table = $connection->getTableName($tablePrefix.'catalog_category_entity');
        $query = "SELECT `parent_id` FROM `".$table."` WHERE `entity_id` = $imCategoryId";
        $parentId = $connection->fetchOne($query);
        $categoryIds[] = $parentId;
        $categoryIds[] = $imCategoryId;

        //return $imCategoryId;
        return $categoryIds;
    }

    public function createCategory($categoryName, $assignTo)
    {
        $category_obj = $this->_objectManager
                        ->create('Magento\Catalog\Model\Category');
        $parent_category = $this->_objectManager
                      ->create('Magento\Catalog\Model\Category')
                      ->load($assignTo);
        $title = $parent_category->getName().'/'.$categoryName;
        $urlKey = $this->createUrlKey($title, '');
        $category_obj->setPath($parent_category->getPath())
                    ->setParentId($assignTo)
                    ->setName($categoryName)
                    ->setUrlKey($urlKey)
                    ->setIsActive(true);
        $category_obj->save();
        $this->_objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface')->save($category_obj);

        return $category_obj->getId();
    }

    public function roundToTheNearestAnything($value, $roundTo)
    {
        $mod = $value%$roundTo;
        return $value+($mod<($roundTo/2)?-$mod:$roundTo-$mod);
    }

    public function createUrlKey($title, $sku)
    {   
        //echo $title." ";
        $url = preg_replace('#[^0-9a-z]+#i', '-', $title);
        $lastCharTitle = substr($title, -1);
        $lastUrlChar = substr($url, -1);
        if ($lastUrlChar == "-" && $lastCharTitle != "-"){
            $url = substr($url, 0, strlen($url) - 1);
        }
        $url = str_replace(array( '(', ')' ), '', $url);
        $url = str_replace('&', "-", trim($url));
        $url = str_replace("--", '-', $url);   
        $url = str_replace("*", '', $url);
        $urlKey = strtolower($url);
        $storeId = (int) $this->_storeManager->getStore()->getStoreId();

        /*$isUnique = $this->checkUrlKeyDuplicates($sku, $urlKey, $storeId);
        if ($isUnique) {
            return $urlKey;
        } else {
            return $urlKey . '-' . $sku;
        }*/
        return $this->checkUrlKeyExist($urlKey, 0);
    }

    public function checkUrlKeyDuplicates($sku, $urlKey, $storeId) 
    {
        $urlKey .= '.html';

        $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $tablename = $connection->getTableName($tablePrefix.'url_rewrite');

        $sql = $connection->select()->from(
                        ['url_rewrite' => $tablename], ['request_path', 'store_id']
                )->joinLeft(
                        ['cpe' => $connection->getTableName($tablePrefix.'catalog_product_entity')], "cpe.entity_id = url_rewrite.entity_id"
                )->where('request_path IN (?)', $urlKey)
                ->where('store_id IN (?)', $storeId)
                ->where('cpe.sku not in (?)', $sku);

        $urlKeyDuplicates = $connection->fetchAssoc($sql);

        if (!empty($urlKeyDuplicates)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkUrlKeyExist($request_path, $i)
    {   
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $table = $connection->getTableName($tablePrefix.'url_rewrite');
        $urlKey = '';
        $urlKey = $request_path;
        if($request_path != ''){
            $request_path .= '.html';
        }
        $query_request_path = addslashes($request_path);
        $coll = array();
        $query = "SELECT * FROM `".$table."` AS maintable WHERE maintable.`request_path` LIKE '%".$query_request_path."%'";
        $coll = $connection->fetchAll($query);
        
        if(sizeof($coll) > 0){
            $delimiter = "-".$i;
            $urlKey = rtrim($urlKey,$delimiter);

            $i += 1;
            $newUrlKey = $urlKey.'-'.$i;
            return $this->checkUrlKeyExist($newUrlKey, $i);
        }else{
            return $urlKey;
        }
    }

    public function setIsUpdated($value,$sellerId){
        if(sizeof($value) > 0){
            if (array_key_exists('barcode', $value)) {
                if(trim($value['barcode']) != ''){
                    $barcode = trim($value['barcode']);
                    $historyFactory = $this->_productHistoryFactory->create()->getCollection()->addFieldToFilter('barcode',$barcode)->addFieldToFilter('seller_id',$sellerId);
                    if($historyFactory->getSize() > 0){
                        $isUpdated = 1;
                        $id = $historyFactory->getFirstItem()->getId();
                        $coll = $this->_productHistoryFactory->create()->load($id);
                        $coll->setIsUpdated($isUpdated);
                        $coll->save();
                        
                        /*$productId = $historyFactory->getFirstItem()->getProductId();
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
                        $product->setStoreId(0);
                        $product->load($productId);
                        $product->setStatus(Status::STATUS_ENABLED);
                        $product->save();*/
                    }
                }  
            }
        }
        return $this;
    }

    public function isValidImageUrl($image_url, $sku) {
        $valid = 0;
        if(trim($image_url) != ''){
            $data = @getimagesize($image_url);
            if(is_array($data)){
                $valid = 1;
            }
        }
        if($valid == 0){
            $message = 'The Product SKU "'.$sku.'" has invalid url '.$image_url;
            $this->logger->info($message);
        }
        return $valid;
    }

    public function getInventoryStock($sellerId, $greenlineapi_product_id){

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $table = $connection->getTableName($tablePrefix.'greenlineapi_inventory_stock');


        $query = "SELECT * FROM `".$table."` WHERE `seller_id` = $sellerId AND `product_id` = '".$greenlineapi_product_id."'";
        $coll = [];
        $coll = $connection->fetchAll($query);

        $qty = 0;
        if(sizeof($coll) > 0){
            $coll = array_shift($coll);
            $qty = $coll['qty'];
        }
        return (int)$qty;
    }

}
