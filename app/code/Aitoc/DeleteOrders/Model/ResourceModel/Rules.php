<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Rules
 *
 * @package Aitoc\DeleteOrders\Model\ResourceModel
 */
class Rules extends AbstractDb
{
    const TABLE_NAME = 'aitoc_deleteorders_rules';
    const ID_FIELD_NAME = 'entity_id';

    /**
     *  Class constructor
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }
}
