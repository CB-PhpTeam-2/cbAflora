<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;

/**
 * Class NewAction
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\Rules
 */
class NewAction extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aitoc_DeleteOrders::rules';

    public function execute()
    {
        $this->_forward('edit');
    }
}
