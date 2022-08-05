<?php

namespace Cb\GreenlineApi\Model\ResourceModel;

class GreenlineCredential extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('greenlineapi_seller_credentiial', 'id');   
    }

}
