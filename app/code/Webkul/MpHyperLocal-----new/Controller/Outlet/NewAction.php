<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Controller\Outlet;

use Magento\Framework\App\RequestInterface;

class NewAction extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_forward("edit");
    }
}
