<?php

namespace Cb\GreenlineApi\Model\ResourceModel;

class OrderExportHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('greenlineapi_export_order_history', 'id');   
    }

}
