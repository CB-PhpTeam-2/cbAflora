<?php
namespace Cb\OcsImport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper {

    const OCS_IMPORT_HISTORY_TABLE = 'ocs_import_history';

	protected $scopeConfig;
	protected $_objectManager;
    protected $_storeManager;
    protected $_resource;
	
	public function __construct(
		\Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource
	){
        $this->scopeConfig = $context->getScopeConfig();
		$this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
    }

    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::Cb_GreenlineApi_XML_PATH_EXTENSIONS .$path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getTablePrefix(){
        $deploymentConfig = $this->_objectManager->get('Magento\Framework\App\DeploymentConfig');
        return $deploymentConfig->get('db/table_prefix');
    }

    public function getItemSize() {
        //return $this->getModuleConfig('itemsize');
		return 1000;
    }

    public function getCollection($serviceName){
		
		$connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
		
        $itemSize = (int) trim($this->getItemSize());
        $offset = $this->getOffset($serviceName);
        $lastOffset = $this->getLastOffset($serviceName);

        if($lastOffset != 0 && $offset == $lastOffset){
            return $this;
        }
		
		$lastOffset = $offset + $itemSize + 1;
		$productSchema = $connection->getTableName($tablePrefix.'catalog_product_entity');
		$mpSchema = $connection->getTableName($tablePrefix.'marketplace_product');
		
		$collection = [];
		$query = "SELECT `productSchema`.entity_id, `mpSchema`.seller_id FROM `".$productSchema."` As `productSchema` LEFT JOIN `".$mpSchema."` As `mpSchema` ON `productSchema`.entity_id = `mpSchema`.mageproduct_id WHERE `productSchema`.entity_id > ".$offset." AND `productSchema`.entity_id < ".$lastOffset;      
		
		$collection = $connection->fetchAll($query);
        
        if($offset == 0 && sizeof($collection) > 0){
			$_query = "SELECT entity_id FROM `".$productSchema."` ORDER BY entity_id DESC LIMIT 1";
			$last_offset = (int) $connection->fetchOne($_query);
            //$total = (int) sizeof($collection);
            //$last_offset = $total - 1;

            $connection  = $this->_resource->getConnection();
            $tablePrefix = $this->getTablePrefix();
            $ocsImportSchema = $connection->getTableName($tablePrefix.self::OCS_IMPORT_HISTORY_TABLE);
            $query = "UPDATE `".$ocsImportSchema."` SET `last_offset` = '$last_offset', `updated_at` = CURRENT_TIMESTAMP WHERE `".$ocsImportSchema."`.`service` = '$serviceName'";
            $connection->query($query);
        }

        return $collection;
    }
	
	public function getOffset($serviceName){
        $offset = 0;
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $ocsImportSchema = $connection->getTableName($tablePrefix.self::OCS_IMPORT_HISTORY_TABLE);
        $coll = array();
        $query = "SELECT * FROM `".$ocsImportSchema."` WHERE `service` = '".$serviceName."'";
        $coll = $connection->fetchAll($query);
        if(sizeof($coll) > 0) {
            $coll = array_shift($coll);
            $offset = $coll['offset'];
            $lastOffset = $coll['last_offset'];
            if($offset == $lastOffset){
                $offset = 0;
                $query = "UPDATE `".$ocsImportSchema."` SET `offset` = '$offset', `updated_at` = CURRENT_TIMESTAMP WHERE `".$ocsImportSchema."`.`service` = '$serviceName'";
                $connection->query($query);
            }
        }else{
            $response = array();
            $response['service'] = $serviceName;
            $response['offset'] = $offset;
            $response['last_offset'] = 0;

            $tableColumn = array();
            $tableData = array();
            $tableColumn = array_keys($response);
            $tableData[] = array_values($response);
            $connection->insertArray($ocsImportSchema ,$tableColumn, $tableData);
        }
        return $offset;
    }

    public function getLastOffset($serviceName){
        $lastOffset = '';
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $ocsImportSchema = $connection->getTableName($tablePrefix.self::OCS_IMPORT_HISTORY_TABLE);
        $coll = array();
        $query = "SELECT * FROM `".$ocsImportSchema."` WHERE `service` = '".$serviceName."'";
        $coll = $connection->fetchAll($query);
        if(sizeof($coll) > 0) {
            $coll = array_shift($coll);
            $lastOffset = $coll['last_offset'];
        }
        return $lastOffset;
    }

    public function setOffset($serviceName){

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->getTablePrefix();
        $ocsImportSchema = $connection->getTableName($tablePrefix.self::OCS_IMPORT_HISTORY_TABLE);

        $query = "SELECT * FROM `".$ocsImportSchema."` WHERE `service` = '".$serviceName."'";
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
            $updateQuery = "UPDATE `".$ocsImportSchema."` SET `offset` = '$offset', `updated_at` = CURRENT_TIMESTAMP WHERE `".$ocsImportSchema."`.`service` = '$serviceName'";
            $connection->query($updateQuery);
        }
        
        return $this;
    }
	
}
