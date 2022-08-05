<?php

namespace Cb\GreenlineApi\Model;

use Magento\Framework\Model\AbstractModel;

class GreenlineCredential extends AbstractModel {

    /**
     * Define resource model
     */
    protected function _construct() {
        $this->_init('Cb\GreenlineApi\Model\ResourceModel\GreenlineCredential');
    }

}