<?php

namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class InventoryImport extends AbstractHelper {

    const GREENLINEAPI_INVENTORY_TABLE = 'greenlineapi_inventory_stock';
    const GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE = 'greenlineapi_seller_history';

    protected $_moduleHelper;
    protected $_resource;

    public function __construct(
    \Magento\Framework\View\Element\Context $context,
    \Cb\GreenlineApi\Helper\Data $moduleHelper,
    \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_moduleHelper = $moduleHelper;
        $this->_resource = $resource;
    }

    public function importInventory($collection, $sellerId) {

        if ($this->_moduleHelper->chkIsModuleEnable()) {
            
            $connection  = $this->_resource->getConnection();
            $tablePrefix = $this->_moduleHelper->getTablePrefix();

            $table = $connection->getTableName($tablePrefix.self::GREENLINEAPI_INVENTORY_TABLE);

            foreach ($collection as $key => $value) {
                $product_id = trim($value['productId']);
                $qty = trim($value['quantity']);
                $coll = [];
                $query = "SELECT * FROM `".$table."` WHERE `seller_id` = '".$sellerId."' AND `product_id` = '".$product_id."'";
                $coll = $connection->fetchAll($query);
                if(sizeof($coll) > 0) {
                    $coll = array_shift($coll);
                    $update_at = date('Y-m-d H:i:s', time());
                    $data = ["qty"=> $qty, "updated_at"=> $update_at];
                    $id = $coll['id'];
                    $where = ['id = ?' => (int)$id];
                    $connection->update($table, $data, $where);
                }else{
                    $tableData = [];
                    $tableData[] = [$sellerId, $product_id, $qty];
                    $connection->insertArray($table ,['seller_id', 'product_id', 'qty'], $tableData);
                }
            }

            $this->setOffset($sellerId);

        }

        return $this;
    }

    public function setOffset($sellerId){

        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->_moduleHelper->getTablePrefix();
        $sellerHistoryTable = $connection->getTableName($tablePrefix.self::GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE);

        $query= "UPDATE `".$sellerHistoryTable."` SET `inventory_status` = '1' WHERE `seller_id` = ".$sellerId;
        $connection->query($query);

        return $this;
    }
}
