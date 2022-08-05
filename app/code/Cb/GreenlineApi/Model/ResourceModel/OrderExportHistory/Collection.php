<?php

namespace Cb\GreenlineApi\Model\ResourceModel\OrderExportHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    /**
     * Define model & resource model
     */
    protected function _construct() {
        $this->_init(
                'Cb\GreenlineApi\Model\OrderExportHistory', 'Cb\GreenlineApi\Model\ResourceModel\OrderExportHistory'
        );
    }

}
