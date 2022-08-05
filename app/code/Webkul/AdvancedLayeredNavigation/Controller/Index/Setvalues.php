<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Controller\Index;

use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Setvalues extends Action
{
    /**
     * @var SessionManager
     */
    protected $_session;

    /**
     * @param Context        $context
     * @param SessionManager $session
     * @param PageFactory    $resultPageFactory
     */
    public function __construct(
        Context $context,
        SessionManager $session
    ) {
        $this->_session = $session;
        parent::__construct($context);
    }

    /**
     * set values in session
     *
     * @return void
     */
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $this->_session->setData($post["attr"], $post["value"]);
    }
}
