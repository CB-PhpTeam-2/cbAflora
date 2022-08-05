<?php

namespace Cb\GreenlineApi\Model\ResourceModel;

class ProductHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('greenlineapi_import_product_history', 'id');   
    }

}
